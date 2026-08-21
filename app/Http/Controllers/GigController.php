<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\GigView;
use App\Models\Review;
use App\Services\GigPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\User;
use App\Models\GigMessage;
use Illuminate\Support\Str;

class GigController extends Controller
{
    public function __construct(private GigPayoutService $payoutService)
    {
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Gig::leftJoin('gig_views', function ($join) use ($userId) {
                $join->on('gigs.id', '=', 'gig_views.gig_id')
                     ->where('gig_views.user_id', '=', $userId);
            })
            ->leftJoin('users', 'gigs.user_id', '=', 'users.id')
            ->select('gigs.*', 'gig_views.views_count')
            ->orderByRaw("CASE WHEN gigs.status = 'open' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN users.tier IN ('tier2', 'tier3') THEN 0 ELSE 1 END")
            ->orderByDesc('gig_views.views_count')
            ->orderByDesc('gigs.created_at')
            ->with(['poster', 'worker', 'reviews']);

        if ($request->filled('search')) {
            $query->where('gigs.title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('gigs.category', $request->category);
        }
        if ($request->sort === 'newest') {
            $query->reorder('gigs.created_at', 'desc');
        } elseif ($request->sort === 'highest') {
            $query->reorder('gigs.amount', 'desc');
        }

        $gigs = $query->get()->map(function ($gig) {
            $poster = $gig->poster;
            $acceptedBy = $gig->worker;
            $reviews = $gig->reviews;

            $gig->poster_name = $poster->name ?? 'Unknown';
            $gig->poster_verified = $poster->is_verified_seller ?? false;
            $gig->poster_level = $poster ? $poster->workerLevel() : null;
            $gig->poster_badge_tier = $poster->badge_tier ?? null;
            $gig->poster_completed_count = $poster ? $poster->completedGigsCount() : 0;
            $gig->avg_rating = $reviews->count() ? round($reviews->avg('rating'), 1) : null;
            $gig->review_count = $reviews->count();
            $gig->accepted_by_name = $acceptedBy->name ?? null;
            return $gig;
        });

        $categories = Gig::select('category')->distinct()->pluck('category')->filter()->values();

        return view(Auth::check() ? 'gigs.index' : 'gigs.guest', ['gigs' => $gigs, 'categories' => $categories]);
    }

    public function show(Request $request, Gig $gig)
    {
        GigView::updateOrCreate(
            ['user_id' => Auth::id(), 'gig_id' => $gig->id],
            []
        )->increment('views_count');

        $poster = $gig->poster;
        $acceptedByUser = $gig->worker;
        $posterCompletedCount = $poster ? $poster->completedGigsCount() : 0;
        $workerCompletedCount = $acceptedByUser ? $acceptedByUser->completedGigsCount() : 0;

        $guestToken = $request->cookie('qelvo_guest_token');
        $isGuestWorker = !Auth::check() && $gig->guest_token && $gig->guest_token === $guestToken;
        $guestGigsUsed = (!Auth::check() && $guestToken) ? Gig::where('guest_token', $guestToken)->count() : 0;

        $messages = collect();
        if (Auth::check() && $poster) {
            $otherUserId = Auth::id() === $poster->id ? $gig->accepted_by : $poster->id;
            if ($otherUserId) {
                $messages = GigMessage::where('gig_id', $gig->id)
                    ->where(function ($q) use ($otherUserId) {
                        $q->where(fn($q2) => $q2->where('sender_id', Auth::id())->where('recipient_id', $otherUserId))
                          ->orWhere(fn($q2) => $q2->where('sender_id', $otherUserId)->where('recipient_id', Auth::id()));
                    })
                    ->with('sender')
                    ->oldest()
                    ->get();
            }
        }

        return view('gigs.show', [
            'gig' => $gig,
            'poster' => $poster,
            'acceptedByUser' => $acceptedByUser,
            'posterCompletedCount' => $posterCompletedCount,
            'workerCompletedCount' => $workerCompletedCount,
            'posterLevel' => $poster ? $poster->workerLevel() : null,
            'posterBadgeTier' => $poster->badge_tier ?? null,
            'workerBadgeTier' => $acceptedByUser->badge_tier ?? null,
            'posterRating' => $poster ? $poster->averageRatingReceived() : null,
            'posterReviewCount' => $poster ? $poster->reviewsReceivedCount() : 0,
            'workerLevel' => $acceptedByUser ? $acceptedByUser->workerLevel() : null,
            'workerRating' => $acceptedByUser ? $acceptedByUser->averageRatingReceived() : null,
            'isGuestWorker' => $isGuestWorker,
            'guestGigsUsed' => $guestGigsUsed,
            'messages' => $messages,
            'dispute' => $gig->dispute,
        ]);
    }

    public function sendMessage(Request $request, Gig $gig)
    {
        $validated = $request->validate(['body' => 'required|string|max:1000']);

        $poster = $gig->poster;
        if (!$poster) {
            return back()->withErrors(['message' => 'This gig has no poster to message.']);
        }

        $recipientId = Auth::id() === $poster->id ? $gig->accepted_by : $poster->id;
        if (!$recipientId) {
            return back()->withErrors(['message' => 'No one to message yet — wait until someone claims this gig.']);
        }

        GigMessage::create([
            'gig_id' => $gig->id,
            'sender_id' => Auth::id(),
            'recipient_id' => $recipientId,
            'body' => $validated['body'],
        ]);

        return back();
    }

    // Open-gig caps by tier: free=4, tier1=10, tier2/tier3=unlimited.
    private function gigPostCap(string $tier): ?int
    {
        return match ($tier) {
            'tier2', 'tier3' => null,
            'tier1' => 10,
            default => 4,
        };
    }

    public function create()
    {
        $user = Auth::user();

        $cap = $this->gigPostCap($user->tier);
        $openCount = Gig::where('user_id', $user->id)->where('status', 'open')->count();
        $remaining = $cap === null ? null : max(0, $cap - $openCount);
        $canPost = $cap === null || $remaining > 0;
        if (!$canPost) {
            return redirect()->route('subscriptions.index')->withErrors([
                'tier' => 'You\'ve reached your open gig limit (' . $cap . '). Upgrade for a higher limit.'
            ]);
        }

        return view('gigs.create', ['freePostsRemaining' => $remaining]);
    }

    public function edit(Gig $gig)
    {
        abort_unless($gig->user_id === Auth::id(), 403, 'You can only edit your own gigs.');
        abort_if($gig->status !== 'open', 403, 'You can only edit a gig while it is still open.');
        return view('gigs.edit', ['gig' => $gig]);
    }

    // Editing only happens while status === 'open', i.e. before a deposit was
    // ever split from a full escrow — so the deposit ratio (50%) is kept in
    // sync with the new price here, not the raw price difference.
    public function update(Request $request, Gig $gig)
    {
        abort_unless($gig->user_id === Auth::id(), 403, 'You can only edit your own gigs.');
        abort_if($gig->status !== 'open', 403, 'You can only edit a gig while it is still open.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'whatsapp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        $posterId = Auth::id();

        $result = DB::transaction(function () use ($gig, $validated, $posterId) {
            $lockedGig = Gig::where('id', $gig->id)->lockForUpdate()->first();

            if ($lockedGig->user_id !== $posterId || $lockedGig->status !== 'open') {
                return ['ok' => false];
            }

            $newDeposit = round($validated['amount'] * 0.5, 2);
            $diff = $newDeposit - $lockedGig->escrowed_amount;

            $poster = User::where('id', $posterId)->lockForUpdate()->first();

            if ($diff > 0) {
                if ($poster->wallet_balance < $diff) {
                    return ['ok' => false, 'insufficient' => true, 'diff' => $diff];
                }
                $poster->decrement('wallet_balance', $diff);
                Transaction::create(['user_id' => $poster->id, 'type' => 'gig_escrow', 'amount' => -$diff, 'description' => 'Increased deposit for gig: ' . $gig->title]);
            } elseif ($diff < 0) {
                $poster->increment('wallet_balance', abs($diff));
                Transaction::create(['user_id' => $poster->id, 'type' => 'gig_escrow_refund', 'amount' => abs($diff), 'description' => 'Reduced deposit for gig: ' . $gig->title]);
            }

            $lockedGig->update([...$validated, 'escrowed_amount' => $newDeposit]);
            return ['ok' => true];
        });

        if (!$result['ok']) {
            if (!empty($result['insufficient'])) {
                return back()->withErrors(['amount' => 'Insufficient wallet balance to raise the deposit by ₦' . number_format($result['diff']) . '.'])->withInput();
            }
            return back()->withErrors(['gig' => 'This gig can no longer be edited.']);
        }

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Gig updated.');
    }

    // Posting now only charges a 50% deposit, not the full amount — the rest
    // is charged from the poster at complete() time, once they confirm the
    // work. escrowed_amount tracks exactly how much has been taken so far.
    public function store(Request $request)
    {
        $poster = Auth::user();

        $cap = $this->gigPostCap($poster->tier);
        $openCount = Gig::where('user_id', $poster->id)->where('status', 'open')->count();
        $canPost = $cap === null || $openCount < $cap;

        if (!$canPost) {
            return redirect()->route('subscriptions.index')->withErrors([
                'tier' => 'You\'ve reached your open gig limit (' . $cap . '). Upgrade for a higher limit.'
            ]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'whatsapp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        $posterId = $poster->id;
        $deposit = round($validated['amount'] * 0.5, 2);

        $gig = DB::transaction(function () use ($posterId, $validated, $deposit) {
            $lockedPoster = User::where('id', $posterId)->lockForUpdate()->first();

            if ($lockedPoster->wallet_restricted) {
                return null;
            }
            if ($lockedPoster->wallet_balance < $deposit) {
                return null;
            }

            $lockedPoster->decrement('wallet_balance', $deposit);
            Transaction::create([
                'user_id' => $lockedPoster->id,
                'type' => 'gig_escrow',
                'amount' => -$deposit,
                'description' => '50% deposit for gig: ' . $validated['title'],
            ]);

            return Gig::create([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? '',
                'amount' => $validated['amount'],
                'escrowed_amount' => $deposit,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'user_id' => $lockedPoster->id,
                'status' => 'open',
            ]);
        });

        if (!$gig) {
            $poster->refresh();
            if ($poster->wallet_restricted) {
                return back()->withErrors(['amount' => 'Your wallet is restricted due to an unresolved payment issue. Clear your balance to post new gigs.'])->withInput();
            }
            return back()->withErrors(['amount' => 'Insufficient wallet balance to fund the deposit. Top up first.'])->withInput();
        }

        return redirect()->route('gigs.index')->with('status', 'Gig posted!');
    }

    public function accept(Request $request, Gig $gig)
    {
        $guestName = null;
        $token = null;

        if (!Auth::check()) {
            $validated = $request->validate(['guest_name' => 'required|string|max:255']);
            $guestName = $validated['guest_name'];
            $token = $request->cookie('qelvo_guest_token') ?: Str::random(40);

            $usedCount = Gig::where('guest_token', $token)->count();
            if ($usedCount >= 2) {
                return redirect()->route('register')
                    ->withErrors(['gig' => 'You\'ve used your 2 free gigs. Create an account to keep taking gigs.'])
                    ->cookie('qelvo_guest_token', $token, 60 * 24 * 365 * 2);
            }
        }

        $userId = Auth::id();

        $result = DB::transaction(function () use ($gig, $token, $guestName, $userId) {
            $locked = Gig::where('id', $gig->id)->lockForUpdate()->first();

            if ($locked->status !== 'open') {
                return ['ok' => false, 'reason' => 'unavailable'];
            }

            if ($userId) {
                if ($locked->user_id === $userId) {
                    return ['ok' => false, 'reason' => 'own_gig'];
                }
                $locked->update(['status' => 'in_progress', 'accepted_by' => $userId, 'guest_name' => null, 'guest_token' => null]);
            } else {
                $locked->update([
                    'status' => 'in_progress',
                    'accepted_by' => null,
                    'guest_name' => $guestName,
                    'guest_token' => $token,
                ]);
            }

            return ['ok' => true];
        });

        if (!$result['ok']) {
            $message = $result['reason'] === 'own_gig'
                ? 'You cannot claim your own gig.'
                : 'This gig is no longer available.';
            return back()->withErrors(['gig' => $message]);
        }

        if (!$userId) {
            $usedCount = Gig::where('guest_token', $token)->count();
            return redirect()->route('gigs.show', $gig->id)
                ->with('status', 'Gig claimed as ' . $guestName . ' — free gig ' . $usedCount . ' of 2. Message the poster, then mark it delivered. You\'ll need to create an account to receive payment.')
                ->cookie('qelvo_guest_token', $token, 60 * 24 * 365 * 2);
        }

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Gig claimed — message the poster, then mark it delivered once you\'re done.');
    }

    public function deliver(Request $request, Gig $gig)
    {
        $isOwnerWorker = Auth::check() && $gig->accepted_by === Auth::id();
        $isGuestWorker = !Auth::check() && $gig->guest_token && $gig->guest_token === $request->cookie('qelvo_guest_token');

        abort_unless($isOwnerWorker || $isGuestWorker, 403, 'Only the person who claimed this gig can mark it delivered.');
        if ($gig->status !== 'in_progress') {
            return back()->withErrors(['gig' => 'This gig cannot be marked delivered right now.']);
        }

        $validated = $request->validate(['delivery_note' => 'nullable|string|max:1000']);

        $gig->update([
            'status' => 'delivered',
            'delivery_note' => $validated['delivery_note'] ?? null,
            'delivered_at' => now(),
            'payment_reminder_sent_at' => null,
        ]);

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Marked as delivered — waiting for the poster to confirm and release payment.');
    }

    // STEP 3: poster confirms the delivered work is good.
    // The remaining balance (amount - escrowed_amount, i.e. the second 50%)
    // is charged from the poster right here, in the same locked transaction,
    // before anything is paid out. If the poster can't cover it, the gig
    // simply stays 'delivered' so they can retry after topping up — the
    // worker can also file a complaint via GigDisputeController in the
    // meantime.
    // If a guest (no account) did the work, the gig is held as
    // 'awaiting_worker_signup' instead of paid out, but the poster's money
    // (now the full amount) has already left their wallet at this point —
    // settleGuestGigs() below only pays the worker, it doesn't charge the
    // poster again.
    public function complete(Gig $gig)
    {
        $userId = Auth::id();

        $result = DB::transaction(function () use ($gig, $userId) {
            $locked = Gig::where('id', $gig->id)->lockForUpdate()->first();

            if ($locked->user_id !== $userId) {
                return ['ok' => false, 'reason' => 'forbidden'];
            }
            if ($locked->status !== 'delivered') {
                return ['ok' => false, 'reason' => 'not_delivered'];
            }
            if (!$locked->accepted_by && !$locked->guest_token) {
                return ['ok' => false, 'reason' => 'no_worker'];
            }

            $remaining = round($locked->amount - $locked->escrowed_amount, 2);
            $poster = User::where('id', $userId)->lockForUpdate()->first();

            if ($remaining > 0) {
                if ($poster->wallet_balance < $remaining) {
                    return ['ok' => false, 'reason' => 'insufficient_funds', 'remaining' => $remaining];
                }
                $poster->decrement('wallet_balance', $remaining);
                Transaction::create([
                    'user_id' => $poster->id,
                    'type' => 'gig_escrow',
                    'amount' => -$remaining,
                    'description' => 'Final payment for gig: ' . $locked->title,
                ]);
            }

            $locked->update(['escrowed_amount' => $locked->amount]);

            if ($locked->accepted_by) {
                $worker = User::where('id', $locked->accepted_by)->lockForUpdate()->first();
                $this->payoutService->payout($locked, $worker);
                $locked->update(['status' => 'completed']);
                return ['ok' => true, 'mode' => 'paid', 'worker_name' => $worker->name];
            }

            $locked->update(['status' => 'awaiting_worker_signup']);
            return ['ok' => true, 'mode' => 'held', 'guest_name' => $locked->guest_name];
        });

        if (!$result['ok']) {
            if ($result['reason'] === 'forbidden') {
                abort(403, 'Only the poster can confirm completion.');
            }
            if ($result['reason'] === 'insufficient_funds') {
                return back()->withErrors(['gig' => 'Insufficient wallet balance to release the remaining ₦' . number_format($result['remaining']) . '. Top up and try again.']);
            }
            $messages = [
                'not_delivered' => 'This gig has not been marked delivered yet.',
                'no_worker' => 'This gig has no worker to pay.',
            ];
            return back()->withErrors(['gig' => $messages[$result['reason']]]);
        }

        if ($result['mode'] === 'paid') {
            return redirect()->route('gigs.show', $gig->id)->with('status', 'Payment released to ' . $result['worker_name'] . '.');
        }

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Marked complete — payment is held until ' . $result['guest_name'] . ' creates an account to receive it.');
    }

    public function rejectDelivery(Request $request, Gig $gig)
    {
        $user = Auth::user();
        abort_unless($gig->user_id === $user->id, 403, 'Only the poster can request changes.');
        if ($gig->status !== 'delivered') {
            return back()->withErrors(['gig' => 'This gig is not awaiting confirmation.']);
        }

        $validated = $request->validate(['rejection_note' => 'required|string|max:1000']);

        $gig->update([
            'status' => 'in_progress',
            'delivery_note' => null,
            'delivered_at' => null,
            'rejection_note' => $validated['rejection_note'],
            'payment_reminder_sent_at' => null,
        ]);

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Sent back for revisions.');
    }

    // Called from FlutterwaveController once a former guest pays the signup fee.
    // The poster already paid the full amount at complete() time, so this only
    // pays the worker — it never touches the poster's wallet.
    public function settleGuestGigs(User $user, string $guestToken): int
    {
        return DB::transaction(function () use ($user, $guestToken) {
            $gigs = Gig::where('guest_token', $guestToken)
                ->where('status', 'awaiting_worker_signup')
                ->lockForUpdate()
                ->get();

            if ($gigs->isEmpty()) {
                return 0;
            }

            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            foreach ($gigs as $gig) {
                $gig->update(['accepted_by' => $lockedUser->id]);
                $this->payoutService->payout($gig, $lockedUser);
                $gig->update(['status' => 'completed']);
                $lockedUser->refresh();
            }

            return $gigs->count();
        });
    }
}
