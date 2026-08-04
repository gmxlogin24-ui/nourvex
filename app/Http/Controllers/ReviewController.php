<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function storeForGig(Request $request, Gig $gig)
    {
        $user = Auth::user();

        abort_unless($gig->status === 'completed' && in_array($user->id, [$gig->user_id, $gig->accepted_by]), 403);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $reviewedUserId = $user->id === $gig->user_id ? $gig->accepted_by : $gig->user_id;

        Review::updateOrCreate(
            ['reviewer_id' => $user->id, 'reviewable_type' => Gig::class, 'reviewable_id' => $gig->id],
            ['reviewed_user_id' => $reviewedUserId, 'rating' => $validated['rating'], 'comment' => $validated['comment'] ?? null]
        );

        return back()->with('status', 'Review submitted!');
    }

    public function storeForListing(Request $request, Listing $listing)
    {
        $user = Auth::user();

        abort_unless($listing->status === 'sold' && $user->id === $listing->buyer_id, 403);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        Review::updateOrCreate(
            ['reviewer_id' => $user->id, 'reviewable_type' => Listing::class, 'reviewable_id' => $listing->id],
            ['reviewed_user_id' => $listing->user_id, 'rating' => $validated['rating'], 'comment' => $validated['comment'] ?? null]
        );

        return back()->with('status', 'Review submitted!');
    }
}
