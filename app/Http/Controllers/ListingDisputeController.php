<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingDispute;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ListingDisputeFiled;
use App\Notifications\ListingDisputeResolved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ListingDisputeController extends Controller
{
    // Buyer files a complaint. Allowed any time escrow is still held —
    // before confirming (item never arrived/wrong item) or after shipping
    // was marked but nothing showed up. Not for the seller to file (they
    // already got what they wanted the moment the buyer paid).
    public function store(Request $request, Listing $listing)
    {
        $user = Auth::user();
        abort_unless($listing->buyer_id === $user->id, 403, 'Only the buyer can file a complaint on this listing.');
        abort_unless($listing->status === 'sold', 422, 'This listing has no pending escrow to dispute.');

        $existing = ListingDispute::where('listing_id', $listing->id)->first();
        if ($existing && $existing->status === 'open') {
            return back()->withErrors(['dispute' => 'A complaint is already open for this listing.']);
        }

        $validated = $request->validate(['description' => 'required|string|max:2000']);

        $dispute = ListingDispute::updateOrCreate(
            ['listing_id' => $listing->id],
            [
                'raised_by' => $user->id,
                'description' => $validated['description'],
                'status' => 'open',
                'resolved_by' => null,
                'resolved_at' => null,
            ]
        );

        Notification::send(User::where('is_admin', true)->get(), new ListingDisputeFiled($dispute));

        if ($listing->user_id) {
            \App\Models\ListingMessage::postSystemEvent(
                $listing->id, $user->id, $listing->user_id,
                '⚠️ A complaint was filed on this listing — admin is reviewing.'
            );
        }

        return back()->with('status', 'Complaint submitted — admin will review it.');
    }

    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $disputes = ListingDispute::where('status', 'open')->with(['listing', 'raisedBy', 'assignedTo'])->latest()->get();
        $ccAgents = User::where('role', 'customer_care')->orderBy('name')->get();
        return view('admin.marketplace-disputes.index', ['disputes' => $disputes, 'ccAgents' => $ccAgents]);
    }

    // Admin sides with the buyer: escrowed funds already sitting in nobody's
    // wallet get released to the buyer's wallet (refund). No commission taken.
    public function resolveRefunded(ListingDispute $dispute)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($dispute->status === 'open', 422, 'This complaint has already been resolved.');

        DB::transaction(function () use ($dispute, $admin) {
            $listing = Listing::where('id', $dispute->listing_id)->lockForUpdate()->first();
            abort_unless($listing->status === 'sold', 422, 'This listing is no longer awaiting payment resolution.');

            $buyer = User::where('id', $listing->buyer_id)->lockForUpdate()->first();

            $buyer->increment('wallet_balance', $listing->amount);
            Transaction::create([
                'user_id' => $buyer->id,
                'type' => 'marketplace_refund',
                'amount' => $listing->amount,
                'description' => 'Admin refund (dispute): ' . $listing->title,
            ]);

            $listing->update(['status' => 'available', 'buyer_id' => null]);

            $dispute->update(['status' => 'resolved_refunded', 'resolved_by' => $admin->id, 'resolved_at' => now()]);
        });

        $this->notifyResolution($dispute->fresh(), 'refunded');

        return back()->with('status', 'Dispute resolved — buyer has been refunded.');
    }

    // Admin sides with the seller: escrow releases normally, same commission
    // logic as a normal confirmed delivery.
    public function resolvePaid(ListingDispute $dispute)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($dispute->status === 'open', 422, 'This complaint has already been resolved.');

        DB::transaction(function () use ($dispute, $admin) {
            $listing = Listing::where('id', $dispute->listing_id)->lockForUpdate()->first();
            abort_unless($listing->status === 'sold', 422, 'This listing is no longer awaiting payment resolution.');

            $seller = $listing->user_id ? User::where('id', $listing->user_id)->lockForUpdate()->first() : null;

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
                    'description' => 'Sold (dispute resolved): ' . $listing->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : ''),
                ]);
                \App\Models\PlatformRevenue::record('marketplace', $commission, 'Commission (dispute resolved): ' . $listing->title, 'listing', $listing->id);
            }

            $listing->update(['status' => 'completed']);

            $dispute->update(['status' => 'resolved_paid', 'resolved_by' => $admin->id, 'resolved_at' => now()]);
        });

        $this->notifyResolution($dispute->fresh(), 'paid');

        return back()->with('status', 'Dispute resolved — seller has been paid.');
    }

    public function dismiss(ListingDispute $dispute)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($dispute->status === 'open', 422, 'This complaint has already been resolved.');

        $dispute->update(['status' => 'dismissed', 'resolved_by' => $admin->id, 'resolved_at' => now()]);

        $this->notifyResolution($dispute->fresh(), 'dismissed');

        return back()->with('status', 'Dispute dismissed.');
    }

    private function notifyResolution(ListingDispute $dispute, string $outcome): void
    {
        $listing = $dispute->listing;
        if ($listing->buyer_id) {
            User::find($listing->buyer_id)?->notify(new ListingDisputeResolved($dispute, $outcome, 'buyer'));
        }
        if ($listing->user_id) {
            User::find($listing->user_id)?->notify(new ListingDisputeResolved($dispute, $outcome, 'seller'));
        }
    }
}
