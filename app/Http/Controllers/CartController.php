<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $ids = Session::get('cart', []);
        $listings = Listing::whereIn('id', $ids)->where('status', 'available')->get();

        // Drop any stale IDs (sold/removed since being added) from the session cart.
        Session::put('cart', $listings->pluck('id')->values()->all());

        return view('cart.index', [
            'listings' => $listings,
            'total' => $listings->sum('amount'),
        ]);
    }

    public function add(Listing $listing)
    {
        if ($listing->user_id === Auth::id()) {
            return back()->withErrors(['cart' => 'You cannot add your own listing to cart.']);
        }
        if ($listing->status !== 'available') {
            return back()->withErrors(['cart' => 'This listing is no longer available.']);
        }

        $cart = Session::get('cart', []);
        if (!in_array($listing->id, $cart, true)) {
            $cart[] = $listing->id;
            Session::put('cart', $cart);
        }

        return back()->with('status', 'Added to cart.');
    }

    public function remove(Listing $listing)
    {
        $cart = Session::get('cart', []);
        Session::put('cart', array_values(array_diff($cart, [$listing->id])));

        return back()->with('status', 'Removed from cart.');
    }

    // Buys every item in the cart via escrow, same as a single Buy Now — just batched.
    public function checkout()
    {
        $buyer = Auth::user();
        $ids = Session::get('cart', []);
        $listings = Listing::whereIn('id', $ids)->where('status', 'available')->get();

        if ($listings->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $total = $listings->sum('amount');
        if ($buyer->wallet_balance < $total) {
            return back()->withErrors(['cart' => 'Insufficient wallet balance for all items in your cart. Top up first, or remove some items.']);
        }

        foreach ($listings as $listing) {
            if ($listing->user_id === $buyer->id) {
                continue; // skip own listing defensively
            }

            $buyer->decrement('wallet_balance', $listing->amount);
            Transaction::create([
                'user_id' => $buyer->id,
                'type' => 'marketplace_escrow',
                'amount' => -$listing->amount,
                'description' => 'Paid (held in escrow): ' . $listing->title,
            ]);

            $listing->update(['status' => 'sold', 'buyer_id' => $buyer->id]);
        }

        Session::forget('cart');

        return redirect()->route('marketplace.index')->with('status', 'Checkout complete — funds held in escrow per item until you confirm delivery.');
    }
}
