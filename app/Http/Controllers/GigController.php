<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\GigView;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\User;
use App\Models\GigMessage;
use Illuminate\Support\Str;

class GigController extends Controller
{
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
            ->orderByDesc('gigs.created_at');

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
            $poster = $gig->user_id ? User::find($gig->user_id) : null;
            $acceptedBy = $gig->accepted_by ? User::find($gig->accepted_by) : null;
            $reviews = Review::where('reviewable_type', Gig::class)
                ->where('reviewable_id', $gig->id)
                ->get();
            $gig->poster_name = $poster->name ?? 'Unknown';
            $gig->poster_verified = $poster->is_verified_seller ?? false;
            $gig->poster_level = $poster ? $poster->workerLevel() : null;
            $gig->poster_badge_tier = $poster->badge_tier ?? null;
            $gig->poster_completed_count = $poster ? Gig::where('user_id', $poster->id)->where('status', 'completed')->count() : 0;
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

        $poster = $gig->user_id ? User::find($gig->user_id) : null;
        $acceptedByUser = $gig->accepted_by ? User::find($gig->accepted_by) : null;
        $posterCompletedCount = $poster ? Gig::where('user_id', $poster->id)->where('status', 'completed')->count() : 0;
        $workerCompletedCount = $acceptedByUser ? Gig::where('accepted_by', $acceptedByUser->id)->where('status', 'completed')->count() : 0;

        $guestToken = $request->cookie('campulsy_guest_token');
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
        ]);
    }

    public function sendMessage(Request $request, Gig $gig)
    {
        $validated = $request->validate(['body' => 'required|string|max:1000']);

        $poster = $gig->user_id ? User::find($gig->user_id) : null;
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
            'tier2', 'tier3' => null, // null = unlimited
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

        $poster = Auth::user();
        $diff = $validated['amount'] - $gig->amount;

        if ($diff > 0) {
            if ($poster->wallet_balance < $diff) {
                return back()->withErrors(['amount' => 'Insufficient wallet balance to raise the price by ₦' . number_format($diff) . '.'])->withInput();
            }
            $poster->decrement('wallet_balance', $diff);
            Transaction::create(['user_id' => $poster->id, 'type' => 'gig_escrow', 'amount' => -$diff, 'description' => 'Increased escrow for gig: ' . $gig->title]);
        } elseif ($diff < 0) {
            $poster->increment('wallet_balance', abs($diff));
            Transaction::create(['user_id' => $poster->id, 'type' => 'gig_escrow_refund', 'amount' => abs($diff), 'description' => 'Reduced escrow for gig: ' . $gig->title]);
        }

        $gig->update($validated);
        return redirect()->route('gigs.show', $gig->id)->with('status', 'Gig updated.');
    }

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

        if ($poster->wallet_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance to fund this gig. Top up first.'])->withInput();
        }

        $poster->decrement('wallet_balance', $validated['amount']);
        Transaction::create([
            'user_id' => $poster->id,
            'type' => 'gig_escrow',
            'amount' => -$validated['amount'],
            'description' => 'Posted gig: ' . $validated['title'],
        ]);

        Gig::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? '',
            'amount' => $validated['amount'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'user_id' => $poster->id,
            'status' => 'open',
        ]);

        return redirect()->route('gigs.index')->with('status', 'Gig posted!');
    }

    // STEP 1: worker claims the gig. No money moves yet.
    // Logged-in users claim normally. A visitor with no account can claim up to 2 gigs
    // lifetime by dropping their name - tracked via a long-lived guest_token cookie.
    public function accept(Request $request, Gig $gig)
    {
        if ($gig->status !== 'open') {
            return back()->withErrors(['gig' => 'This gig is no longer available.']);
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($gig->user_id === $user->id) {
                return back()->withErrors(['gig' => 'You cannot claim your own gig.']);
            }
            $gig->update(['status' => 'in_progress', 'accepted_by' => $user->id, 'guest_name' => null, 'guest_token' => null]);
            return redirect()->route('gigs.show', $gig->id)->with('status', 'Gig claimed — message the poster, then mark it delivered once you\'re done.');
        }

        $validated = $request->validate(['guest_name' => 'required|string|max:255']);

        $token = $request->cookie('campulsy_guest_token') ?: Str::random(40);
        $usedCount = Gig::where('guest_token', $token)->count();

        if ($usedCount >= 2) {
            return redirect()->route('register')
                ->withErrors(['gig' => 'You\'ve used your 2 free gigs. Create an account to keep taking gigs.'])
                ->cookie('campulsy_guest_token', $token, 60 * 24 * 365 * 2);
        }

        $gig->update([
            'status' => 'in_progress',
            'accepted_by' => null,
            'guest_name' => $validated['guest_name'],
            'guest_token' => $token,
        ]);

        return redirect()->route('gigs.show', $gig->id)
            ->with('status', 'Gig claimed as ' . $validated['guest_name'] . ' — free gig ' . ($usedCount + 1) . ' of 2. Message the poster, then mark it delivered. You\'ll need to create an account to receive payment.')
            ->cookie('campulsy_guest_token', $token, 60 * 24 * 365 * 2);
    }

    // STEP 2: worker marks the work as delivered. Still no money moves — poster must confirm.
    public function deliver(Request $request, Gig $gig)
    {
        $isOwnerWorker = Auth::check() && $gig->accepted_by === Auth::id();
        $isGuestWorker = !Auth::check() && $gig->guest_token && $gig->guest_token === $request->cookie('campulsy_guest_token');

        abort_unless($isOwnerWorker || $isGuestWorker, 403, 'Only the person who claimed this gig can mark it delivered.');
        if ($gig->status !== 'in_progress') {
            return back()->withErrors(['gig' => 'This gig cannot be marked delivered right now.']);
        }

        $validated = $request->validate(['delivery_note' => 'nullable|string|max:1000']);

        $gig->update([
            'status' => 'delivered',
            'delivery_note' => $validated['delivery_note'] ?? null,
            'delivered_at' => now(),
        ]);

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Marked as delivered — waiting for the poster to confirm and release payment.');
    }

    // STEP 3: poster confirms the delivered work is good. Only now does escrow release, minus platform commission.
    // If a guest (no account) did the work, the gig is held as 'awaiting_worker_signup' instead of paid out -
    // settleGuestGigs() below releases it once they register + pay the signup fee.
    public function complete(Gig $gig)
    {
        $user = Auth::user();

        abort_unless($gig->user_id === $user->id, 403, 'Only the poster can confirm completion.');
        if ($gig->status !== 'delivered') {
            return back()->withErrors(['gig' => 'This gig has not been marked delivered yet.']);
        }

        if ($gig->accepted_by) {
            $worker = User::find($gig->accepted_by);
            $this->payoutGig($gig, $worker);
            $gig->update(['status' => 'completed']);
            return redirect()->route('gigs.show', $gig->id)->with('status', 'Payment released to ' . $worker->name . '.');
        }

        if ($gig->guest_token) {
            $gig->update(['status' => 'awaiting_worker_signup']);
            return redirect()->route('gigs.show', $gig->id)->with('status', 'Marked complete — payment is held until ' . $gig->guest_name . ' creates an account to receive it.');
        }

        return back()->withErrors(['gig' => 'This gig has no worker to pay.']);
    }

    private function payoutGig(Gig $gig, User $worker)
    {
        $rate = match ($worker->tier) {
            'tier2', 'tier3' => 0,
            'tier1' => 0.03,
            default => 0.05,
        };
        $commission = round($gig->amount * $rate, 2);
        $payout = $gig->amount - $commission;

        $worker->increment('wallet_balance', $payout);
        Transaction::create([
            'user_id' => $worker->id,
            'type' => 'gig_earning',
            'amount' => $payout,
            'description' => 'Completed gig: ' . $gig->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : ''),
        ]);

        if ($worker->referred_by) {
            $referrer = User::find($worker->referred_by);
            if ($referrer) {
                $pct = in_array($referrer->tier, ['tier2', 'tier3'], true) ? 0.15 : 0;
                $bonus = $payout * $pct;
                if ($bonus > 0) {
                    $referrer->increment('wallet_balance', $bonus);
                    Transaction::create([
                        'user_id' => $referrer->id,
                        'type' => 'referral_bonus',
                        'amount' => $bonus,
                        'description' => 'Referral bonus from ' . $worker->name . ' completing a gig',
                    ]);
                }
            }
        }
    }

    // Poster declines delivered work and sends it back for revision instead of paying out.
    // No money moves. Works whether the worker is a real account or a guest.
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
        ]);

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Sent back for revisions.');
    }
    // Called from FlutterwaveController once a former guest pays the ₦1,000 signup fee -
    // releases every gig they completed as a guest that was waiting on their account to exist.
    public function settleGuestGigs(User $user, string $guestToken): int
    {
        $gigs = Gig::where('guest_token', $guestToken)
            ->where('status', 'awaiting_worker_signup')
            ->get();

        foreach ($gigs as $gig) {
            $gig->update(['accepted_by' => $user->id]);
            $this->payoutGig($gig, $user->fresh());
            $gig->update(['status' => 'completed']);
        }

        return $gigs->count();
    }
}
