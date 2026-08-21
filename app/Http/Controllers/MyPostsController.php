<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Deletion policy: once work has been delivered (or already
        // completed), the poster can no longer just cancel and walk away
        // with a refund - the worker already did the work. They must
        // confirm delivery (releasing payment) or open a dispute instead.
        // Deletion is only allowed while nothing has been delivered yet:
        // 'open' (no worker), 'in_progress', or 'awaiting_worker_signup'
        // (worker accepted but hasn't submitted anything).
        if (in_array($gig->status, ['delivered', 'completed'], true)) {
            abort(422, 'This gig has already been delivered and can\'t be cancelled. Please confirm delivery or open a dispute instead.');
        }

        // Lock the gig row for the refund-check + delete so a duplicate/
        // double-click request can't read status 'open' twice and refund
        // the escrow twice before either delete commits.
        DB::transaction(function () use ($gig) {
            $lockedGig = Gig::where('id', $gig->id)->lockForUpdate()->first();

            if (!$lockedGig) {
                return;
            }

            // Re-check inside the lock too - status could have changed
            // (e.g. worker delivered) between the check above and now.
            if (in_array($lockedGig->status, ['delivered', 'completed'], true)) {
                abort(422, 'This gig has already been delivered and can\'t be cancelled. Please confirm delivery or open a dispute instead.');
            }

            $hadWorker = $lockedGig->accepted_by !== null;

            if ($lockedGig->escrowed_amount > 0) {
                $lockedGig->poster->increment('wallet_balance', $lockedGig->escrowed_amount);
                Transaction::create([
                    'user_id' => $lockedGig->user_id,
                    'type' => 'gig_refund',
                    'amount' => $lockedGig->escrowed_amount,
                    'description' => 'Refund for cancelled gig: ' . $lockedGig->title,
                ]);
            }

            if ($hadWorker) {
                $lockedGig->worker->notify(new \App\Notifications\GigCancelledByPoster($lockedGig));
            }

            $lockedGig->delete();
        });

        return back()->with('status', 'Gig removed.');
    }

    public function deleteListing(Listing $listing)
    {
        abort_unless($listing->user_id === Auth::id(), 403);
        $listing->delete();

        return back()->with('status', 'Listing removed.');
    }
}
