<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class MyPostsController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        return view('posts.index', [
            'gigs' => Gig::where('user_id', $userId)->latest()->get(),
            'listings' => Listing::where('user_id', $userId)->latest()->get(),
        ]);
    }

    public function deleteGig(Gig $gig)
    {
        abort_unless($gig->user_id === Auth::id(), 403);

        if ($gig->status === 'open') {
            // Refund escrow back to poster since no one completed it
            $gig->user->increment('wallet_balance', $gig->amount);
            \App\Models\Transaction::create([
                'user_id' => $gig->user_id,
                'type' => 'gig_refund',
                'amount' => $gig->amount,
                'description' => 'Refund for cancelled gig: ' . $gig->title,
            ]);
        }

        $gig->delete();

        return back()->with('status', 'Gig removed.');
    }

    public function deleteListing(Listing $listing)
    {
        abort_unless($listing->user_id === Auth::id(), 403);
        $listing->delete();

        return back()->with('status', 'Listing removed.');
    }
}
