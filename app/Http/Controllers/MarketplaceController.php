<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::leftJoin('users', 'listings.user_id', '=', 'users.id')
            ->select('listings.*')
            ->orderByRaw("CASE WHEN users.tier = 'tier2' THEN 0 ELSE 1 END")
            ->latest('listings.created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('type')) {
            $query->where('listing_type', $request->query('type'));
        }

        return view(\Illuminate\Support\Facades\Auth::check() ? 'marketplace.index' : 'marketplace.guest', [
            'listings' => $query->get(),
            'activeCategory' => $request->query('category'),
            'activeType' => $request->query('type'),
        ]);
    }

    public function show(Listing $listing)
    {
        $seller = $listing->user_id ? \App\Models\User::find($listing->user_id) : null;
        $reviews = $listing->reviews()->with('reviewer')->latest()->get();
        $avgRating = $reviews->avg('rating');
        return view('marketplace.show', [
            'listing' => $listing,
            'seller' => $seller,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
        ]);
    }

    // STEP 1: buyer pays, funds sit in escrow. Seller is NOT paid yet.
    public function purchase(Listing $listing)
    {
        $buyer = Auth::user();

        if ($listing->user_id === $buyer->id) {
            return back()->withErrors(['balance' => 'You cannot buy your own listing.']);
        }

        if ($listing->status !== 'available') {
            return back()->withErrors(['balance' => 'This listing is no longer available.']);
        }

        if ($buyer->wallet_balance < $listing->amount) {
            return back()->withErrors(['balance' => 'Insufficient wallet balance. Top up your wallet first.']);
        }

        $buyer->decrement('wallet_balance', $listing->amount);
        Transaction::create([
            'user_id' => $buyer->id,
            'type' => 'marketplace_escrow',
            'amount' => -$listing->amount,
            'description' => 'Paid (held in escrow): ' . $listing->title,
        ]);

        $listing->update([
            'status' => 'sold',
            'buyer_id' => $buyer->id,
        ]);

        return redirect()->route('marketplace.show', $listing->id)->with('status', 'Payment sent — funds are held until you confirm you received the item.');
    }

    // STEP 2: buyer confirms delivery. Only now does escrow release to the seller, minus platform commission.
    public function confirmDelivery(Listing $listing)
    {
        $buyer = Auth::user();

        abort_unless($listing->buyer_id === $buyer->id, 403, 'Only the buyer can confirm this delivery.');
        if ($listing->status !== 'sold') {
            return back()->withErrors(['balance' => 'This listing has no pending escrow to release.']);
        }

        $seller = $listing->user_id ? \App\Models\User::find($listing->user_id) : null;

        if ($seller) {
            $rate = match ($seller->tier) {
                'tier2', 'tier3' => 0,
                'tier1' => 0.03,
                default => 0.05,
            };
            $commission = round($listing->amount * $rate, 2);
            $payout = $listing->amount - $commission;

            $seller->increment('wallet_balance', $payout);
            Transaction::create([
                'user_id' => $seller->id,
                'type' => 'marketplace_sale',
                'amount' => $payout,
                'description' => 'Sold: ' . $listing->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : ''),
            ]);
        }

        $listing->update(['status' => 'completed']);

        return redirect()->route('marketplace.show', $listing->id)->with('status', 'Delivery confirmed — payment released to the seller.');
    }

    // Weekly listing caps by tier: free=5, tier1=15, tier2/tier3=unlimited.
    private function listingWeeklyCap(string $tier): ?int
    {
        return match ($tier) {
            'tier2', 'tier3' => null, // null = unlimited
            'tier1' => 15,
            default => 5,
        };
    }

    public function create()
    {
        $user = Auth::user();
        $cap = $this->listingWeeklyCap($user->tier);
        $recentCount = Listing::where('user_id', $user->id)->where('created_at', '>=', now()->subDays(7))->count();
        $remaining = $cap === null ? null : max(0, $cap - $recentCount);
        $canPost = $cap === null || $remaining > 0;

        if (!$canPost) {
            return redirect()->route('subscriptions.index')->withErrors([
                'tier' => 'You\'ve reached your weekly listing limit (' . $cap . '). Upgrade for a higher limit.'
            ]);
        }

        return view('marketplace.create', ['remaining' => $remaining]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $cap = $this->listingWeeklyCap($user->tier);
        $recentCount = Listing::where('user_id', $user->id)->where('created_at', '>=', now()->subDays(7))->count();
        $canPost = $cap === null || $recentCount < $cap;

        if (!$canPost) {
            return redirect()->route('subscriptions.index')->withErrors([
                'tier' => 'You\'ve reached your weekly listing limit (' . $cap . '). Upgrade for a higher limit.'
            ]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'listing_type' => 'required|in:item,service,rent,store',
            'condition' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'store_name' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:4096',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('listings', 'public');
        }

        Listing::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'listing_type' => $validated['listing_type'],
            'condition' => $validated['condition'],
            'location' => $validated['location'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? '',
            'store_name' => $validated['store_name'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'seller_name' => Auth::user()->name,
            'user_id' => Auth::id(),
            'is_verified_seller' => Auth::user()->is_verified_seller,
            'image_url' => $imagePath ? '/storage/' . $imagePath : null,
        ]);

        return redirect()->route('marketplace.index');
    }
}
