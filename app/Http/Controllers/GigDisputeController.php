<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\GigDispute;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GigDisputeFiled;
use App\Notifications\GigDisputeResolved;
use App\Services\GigPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class GigDisputeController extends Controller
{
    public function __construct(private GigPayoutService $payoutService)
    {
    }

    // Worker files a complaint when a delivered gig sits unconfirmed/unpaid.
    // Guests without an account can't file — they can't be paid without one
    // anyway, so this only applies to registered workers.
    //
    // A poster can also file, but only once they've hit the revision cap
    // (GigController::MAX_REVISIONS) — before that, they're expected to use
    // "Request Changes" instead of escalating straight to a complaint.
    // NOTE: admin resolution (resolvePaid/dismiss) currently only pays the
    // worker — poster-raised disputes are filed and notified here, but the
    // admin-side outcome for a poster-raised complaint hasn't been wired up
    // yet pending review of resolvePaid()/dismiss().
    public function store(Request $request, Gig $gig)
    {
        $user = Auth::user();
        $isWorker = $gig->accepted_by === $user->id;
        $isPoster = $gig->user_id === $user->id;

        abort_unless($isWorker || $isPoster, 403, 'Only the poster or the worker on this gig can file a complaint.');
        abort_unless($gig->status === 'delivered', 422, 'This gig is not awaiting payment confirmation.');

        if ($isPoster && !$isWorker && $gig->revision_count < \App\Http\Controllers\GigController::MAX_REVISIONS) {
            return back()->withErrors(['dispute' => 'Try "Request Changes" first — you can file a complaint after you\'ve requested changes ' . \App\Http\Controllers\GigController::MAX_REVISIONS . ' times.']);
        }

        $existing = GigDispute::where('gig_id', $gig->id)->first();
        if ($existing && $existing->status === 'open') {
            return back()->withErrors(['dispute' => 'A complaint is already open for this gig.']);
        }

        $validated = $request->validate(['description' => 'required|string|max:2000']);

        $dispute = GigDispute::updateOrCreate(
            ['gig_id' => $gig->id],
            [
                'raised_by' => $user->id,
                'description' => $validated['description'],
                'status' => 'open',
                'resolved_by' => null,
                'resolved_at' => null,
            ]
        );

        Notification::send(User::where('is_admin', true)->get(), new GigDisputeFiled($dispute));

        return back()->with('status', 'Complaint submitted — admin will review it.');
    }

    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $disputes = GigDispute::where('status', 'open')->with(['gig', 'raisedBy', 'assignedTo'])->latest()->get();
        $ccAgents = User::where('role', 'customer_care')->orderBy('name')->get();
        return view('admin.disputes.index', ['disputes' => $disputes, 'ccAgents' => $ccAgents]);
    }

    // Admin sides with the worker: force-charges the poster for whatever
    // remains unescrowed (allowed to go negative), restricts the poster's
    // wallet if it does, and pays the worker immediately.
    public function resolvePaid(GigDispute $dispute)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($dispute->status === 'open', 422, 'This complaint has already been resolved.');

        DB::transaction(function () use ($dispute, $admin) {
            $gig = Gig::where('id', $dispute->gig_id)->lockForUpdate()->first();
            abort_unless($gig->status === 'delivered', 422, 'This gig is no longer awaiting payment.');

            $poster = User::where('id', $gig->user_id)->lockForUpdate()->first();
            $worker = User::where('id', $gig->accepted_by)->lockForUpdate()->first();

            $remaining = round($gig->amount - $gig->escrowed_amount, 2);

            if ($remaining > 0) {
                $poster->decrement('wallet_balance', $remaining);
                Transaction::create([
                    'user_id' => $poster->id,
                    'type' => 'gig_escrow',
                    'amount' => -$remaining,
                    'description' => 'Admin-enforced final payment for disputed gig: ' . $gig->title,
                ]);

                $poster->refresh();
                if ($poster->wallet_balance < 0 && !$poster->wallet_restricted) {
                    $poster->update(['wallet_restricted' => true]);
                }
            }

            $gig->update(['escrowed_amount' => $gig->amount]);
            $this->payoutService->payout($gig, $worker);
            $gig->update(['status' => 'completed']);

            $dispute->update(['status' => 'resolved_paid', 'resolved_by' => $admin->id, 'resolved_at' => now()]);
        });

        $this->notifyResolution($dispute->fresh(), 'paid');

        return back()->with('status', 'Dispute resolved — worker has been paid.');
    }

    // Admin splits the outcome: worker is paid for a percentage of the job
    // (their judgment of how much was actually delivered), poster is
    // charged or refunded to match, platform still takes its normal
    // commission on whatever the worker is paid. Used for poster-raised
    // disputes where the work was partially done or partially acceptable.
    public function resolvePartial(Request $request, GigDispute $dispute)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($dispute->status === 'open', 422, 'This complaint has already been resolved.');

        $validated = $request->validate([
            'worker_percent' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($dispute, $admin, $validated) {
            $gig = Gig::where('id', $dispute->gig_id)->lockForUpdate()->first();
            abort_unless($gig->status === 'delivered', 422, 'This gig is no longer awaiting payment.');
            abort_unless($gig->accepted_by, 422, 'This gig has no registered worker to pay - use dismiss or resolve-paid instead for guest workers.');

            $poster = User::where('id', $gig->user_id)->lockForUpdate()->first();
            $worker = User::where('id', $gig->accepted_by)->lockForUpdate()->first();

            $workerGrossAmount = round($gig->amount * ($validated['worker_percent'] / 100), 2);
            $diff = $workerGrossAmount - $gig->escrowed_amount;

            if ($diff > 0) {
                // Worker's share is more than what's escrowed so far - charge the poster the rest.
                $poster->decrement('wallet_balance', $diff);
                Transaction::create([
                    'user_id' => $poster->id,
                    'type' => 'gig_escrow',
                    'amount' => -$diff,
                    'description' => 'Admin-enforced partial payment (' . $validated['worker_percent'] . '%) for disputed gig: ' . $gig->title,
                ]);
                $poster->refresh();
                if ($poster->wallet_balance < 0 && !$poster->wallet_restricted) {
                    $poster->update(['wallet_restricted' => true]);
                }
            } elseif ($diff < 0) {
                // Worker's share is less than what's already escrowed - refund the poster the difference.
                $poster->increment('wallet_balance', abs($diff));
                Transaction::create([
                    'user_id' => $poster->id,
                    'type' => 'gig_refund',
                    'amount' => abs($diff),
                    'description' => 'Partial refund (dispute resolved ' . $validated['worker_percent'] . '% to worker) for gig: ' . $gig->title,
                ]);
            }

            $gig->update(['escrowed_amount' => $workerGrossAmount]);
            $this->payoutService->payout($gig, $worker, $workerGrossAmount);
            $gig->update(['status' => 'completed']);

            $dispute->update(['status' => 'resolved_partial', 'resolved_by' => $admin->id, 'resolved_at' => now()]);
        });

        $this->notifyResolution($dispute->fresh(), 'partial');

        return back()->with('status', 'Dispute resolved — worker paid ' . $validated['worker_percent'] . '% of the gig amount.');
    }

    public function dismiss(GigDispute $dispute)
    {
        $admin = Auth::user();
        abort_unless($admin->is_admin, 403);
        abort_unless($dispute->status === 'open', 422, 'This complaint has already been resolved.');

        $dispute->update(['status' => 'dismissed', 'resolved_by' => $admin->id, 'resolved_at' => now()]);

        $this->notifyResolution($dispute->fresh(), 'dismissed');

        return back()->with('status', 'Dispute dismissed.');
    }

    private function notifyResolution(GigDispute $dispute, string $outcome): void
    {
        $gig = $dispute->gig;
        if ($gig->worker) {
            $gig->worker->notify(new GigDisputeResolved($dispute, $outcome, 'worker'));
        }
        if ($gig->poster) {
            $gig->poster->notify(new GigDisputeResolved($dispute, $outcome, 'poster'));
        }
    }
}
