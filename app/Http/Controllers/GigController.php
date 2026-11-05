<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\GigView;
use App\Models\Review;
use App\Services\GigPayoutService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('gigs.title', 'like', '%' . $search . '%')
                  ->orWhere('gigs.description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('category')) {
            $query->where('gigs.category', $request->category);
        }
        if ($request->filled('min_price')) {
            $query->where('gigs.amount', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('gigs.amount', '<=', $request->max_price);
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

        // $gigs stays the FULL filtered collection - index.blade.php and
        // guest.blade.php both compute stats (open count, category counts,
        // average reward) from it directly, so it's left untouched. Only
        // the grid display gets a paginated slice, as its own variable.
        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $gigsPage = new LengthAwarePaginator(
            $gigs->slice(($page - 1) * $perPage, $perPage)->values(),
            $gigs->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $categories = Gig::select('category')->distinct()->pluck('category')->filter()->values();

        return view('gigs.index', [
            'gigs' => $gigs,
            'gigsPage' => $gigsPage,
            'categories' => $categories,
        ]);
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
                $conversation = \App\Models\Conversation::where('type', 'business')
                    ->where('subject_type', Gig::class)
                    ->where('subject_id', $gig->id)
                    ->first();
                if ($conversation) {
                    $messages = $conversation->messages()->with('sender')->get();
                }
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
        if (!app(\App\Services\TierService::class)->unlocked(Auth::user(), 'chat.enabled')) {
            return back()->withErrors(['message' => 'Chat is currently unavailable.']);
        }
        $validated = $request->validate([
            'body' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:4096',
        ]);
        if (empty($validated['body']) && !$request->hasFile('attachment')) {
            return back()->withErrors(['message' => 'Type a message or attach a photo.']);
        }

        $poster = $gig->poster;
        if (!$poster) {
            return back()->withErrors(['message' => 'This gig has no poster to message.']);
        }

        $recipientId = Auth::id() === $poster->id ? $gig->accepted_by : $poster->id;
        if (!$recipientId) {
            return back()->withErrors(['message' => 'No one to message yet — wait until someone claims this gig.']);
        }

        $conversation = \App\Models\Conversation::where('type', 'business')
            ->where('subject_type', Gig::class)
            ->where('subject_id', $gig->id)
            ->first();
        if (!$conversation) {
            $conversation = \App\Models\Conversation::create([
                'type' => 'business',
                'subject_type' => Gig::class,
                'subject_id' => $gig->id,
            ]);
        }
        foreach ([Auth::id(), $recipientId] as $uid) {
            \App\Models\ConversationParticipant::firstOrCreate(['conversation_id' => $conversation->id, 'user_id' => $uid]);
        }

        $messageType = 'text';
        $meta = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
            $messageType = 'image';
            $meta = ['url' => '/storage/' . $path];
        }

        $message = \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message_type' => $messageType,
            'body' => $validated['body'] ?? null,
            'meta' => $meta,
        ]);
        $conversation->update(['last_message_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'message_type' => $message->message_type,
                'meta' => $message->meta,
                'created_at' => $message->created_at->diffForHumans(),
            ]);
        }

        return back();
    }

    public function messages(Gig $gig)
    {
        $userId = Auth::id();
        $poster = $gig->poster;
        if (!$poster) {
            return response()->json([]);
        }
        $otherUserId = $userId === $poster->id ? $gig->accepted_by : $poster->id;
        if (!$otherUserId) {
            return response()->json([]);
        }
        $conversation = \App\Models\Conversation::where('type', 'business')
            ->where('subject_type', Gig::class)
            ->where('subject_id', $gig->id)
            ->first();
        $messages = $conversation ? $conversation->messages()->get() : collect();
        return response()->json($messages->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'body' => $m->body,
            'is_system' => $m->is_system,
            'message_type' => $m->message_type,
            'meta' => $m->meta,
            'created_at' => $m->created_at->diffForHumans(),
        ]));
    }

    // Open-gig caps by tier: free=4, tier1=10, tier2/tier3=unlimited.
    // Caps how many gigs a user can have claimed (in_progress or delivered)
    // at once - not how many they post. Posting is uncapped for gigs.
    private function claimCap(string $tier): ?int
    {
        return config('plans.' . $tier . '.gigs_claim_cap', 2);
    }

    public function create()
    {
        return view('gigs.create', [
            'freePostsRemaining' => null,
            'categories' => array_keys(config('gig_categories')),
        ]);
    }

    public function edit(Gig $gig)
    {
        abort_unless($gig->user_id === Auth::id(), 403, 'You can only edit your own gigs.');
        abort_if($gig->status !== 'open', 403, 'You can only edit a gig while it is still open.');
        return view('gigs.edit', [
            'gig' => $gig,
            'categories' => array_keys(config('gig_categories')),
        ]);
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
            'amount' => 'required_without:packages|nullable|numeric|min:1',
            'whatsapp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'deadline' => 'nullable|date|after_or_equal:today',
            'location_type' => 'nullable|in:remote,onsite,pickup',
            'location' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:2048',
            'details' => 'nullable|array',
            'details.*' => 'nullable|string|max:500',
            'has_item' => 'nullable|boolean',
            'item_name' => 'nullable|required_if:has_item,1|string|max:255',
            'item_qty' => 'nullable|integer|min:1',
            'item_cost' => 'nullable|numeric|min:0',
            'delivery_address' => 'nullable|string|max:255',
            'item_delivery_method' => 'nullable|in:meetup,ship',
            'packages' => 'nullable|array|max:3',
            'packages.*.name' => 'required_with:packages|string|max:100',
            'packages.*.price' => 'required_with:packages|numeric|min:1',
            'packages.*.description' => 'nullable|string|max:500',
        ]);

        $packages = !empty($validated['packages'])
            ? array_values(array_filter($validated['packages'], fn ($p) => !empty($p['name']) && !empty($p['price'])))
            : null;
        $computedAmount = $packages ? min(array_column($packages, 'price')) : $validated['amount'];

        $posterId = Auth::id();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('gig-attachments', 'public');
        }

        $result = DB::transaction(function () use ($gig, $validated, $posterId, $attachmentPath, $packages, $computedAmount) {
            $lockedGig = Gig::where('id', $gig->id)->lockForUpdate()->first();

            if ($lockedGig->user_id !== $posterId || $lockedGig->status !== 'open') {
                return ['ok' => false];
            }

            $newDeposit = round($computedAmount * 0.5, 2);
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

            $updateData = $validated;
            unset($updateData['attachment']);
            $updateData['amount'] = $computedAmount;
            $updateData['packages'] = $packages;
            $updateData['details'] = !empty($validated['details']) ? array_filter($validated['details']) : null;
            $updateData['has_item'] = !empty($validated['has_item']);
            $updateData['escrowed_amount'] = $newDeposit;
            if ($attachmentPath) {
                $updateData['attachment_path'] = $attachmentPath;
            }

            $lockedGig->update($updateData);
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

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'amount' => 'required_without:packages|nullable|numeric|min:1',
            'whatsapp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'deadline' => 'nullable|date|after_or_equal:today',
            'location_type' => 'nullable|in:remote,onsite,pickup',
            'location' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:2048',
            'details' => 'nullable|array',
            'details.*' => 'nullable|string|max:500',
            'has_item' => 'nullable|boolean',
            'item_name' => 'nullable|required_if:has_item,1|string|max:255',
            'item_qty' => 'nullable|integer|min:1',
            'item_cost' => 'nullable|numeric|min:0',
            'delivery_address' => 'nullable|string|max:255',
            'item_delivery_method' => 'nullable|in:meetup,ship',
            'packages' => 'nullable|array|max:3',
            'packages.*.name' => 'required_with:packages|string|max:100',
            'packages.*.price' => 'required_with:packages|numeric|min:1',
            'packages.*.description' => 'nullable|string|max:500',
        ]);

        // Packages, if given, are the source of truth for price - the poster's
        // deposit is based on the LOWEST tier. Never trust a client-submitted
        // amount when packages are present; always recompute it here.
        $packages = !empty($validated['packages'])
            ? array_values(array_filter($validated['packages'], fn ($p) => !empty($p['name']) && !empty($p['price'])))
            : null;
        $computedAmount = $packages ? min(array_column($packages, 'price')) : $validated['amount'];

        $posterId = $poster->id;
        $deposit = round($computedAmount * 0.5, 2);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('gig-attachments', 'public');
        }

        if ($reason = \App\Services\SpendLimit::blockedReason(\App\Models\User::find($posterId), (float) $deposit)) {
            return back()->withErrors(['amount' => $reason])->withInput();
        }

        $gig = DB::transaction(function () use ($posterId, $validated, $deposit, $attachmentPath, $packages, $computedAmount) {
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
                'amount' => $computedAmount,
                'escrowed_amount' => $deposit,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'deadline' => $validated['deadline'] ?? null,
                'location_type' => $validated['location_type'] ?? null,
                'location' => $validated['location'] ?? null,
                'details' => !empty($validated['details']) ? array_filter($validated['details']) : null,
                'attachment_path' => $attachmentPath,
                'has_item' => !empty($validated['has_item']),
                'item_name' => $validated['item_name'] ?? null,
                'item_qty' => $validated['item_qty'] ?? null,
                'item_cost' => $validated['item_cost'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'item_delivery_method' => $validated['item_delivery_method'] ?? null,
                'packages' => $packages,
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

        if ($userId) {
            $claimer = Auth::user();
            $cap = $this->claimCap($claimer->tier);
            if ($cap !== null) {
                $activeClaims = Gig::where('accepted_by', $userId)
                    ->whereIn('status', ['in_progress', 'delivered'])
                    ->count();
                if ($activeClaims >= $cap) {
                    return back()->withErrors([
                        'gig' => 'You already have ' . $cap . ' gig(s) claimed and not yet paid out. Finish one or get paid before claiming another - upgrade your tier for a higher limit.'
                    ]);
                }
            }
        }

        $packageIndex = $request->input('package_index');

        $result = DB::transaction(function () use ($gig, $token, $guestName, $userId, $packageIndex) {
            $locked = Gig::where('id', $gig->id)->lockForUpdate()->first();

            if ($locked->status !== 'open') {
                return ['ok' => false, 'reason' => 'unavailable'];
            }

            if ($userId && $locked->user_id === $userId) {
                return ['ok' => false, 'reason' => 'own_gig'];
            }

            // A gig with packages requires the claimer to pick one before
            // claiming - it decides the final price. The poster's deposit
            // was taken at posting based on the lowest tier, so if the
            // claimer picks a pricier tier, the poster is charged the extra
            // deposit needed right here, in the same locked transaction.
            if (!empty($locked->packages)) {
                if ($packageIndex === null || !isset($locked->packages[$packageIndex])) {
                    return ['ok' => false, 'reason' => 'invalid_package'];
                }
                $chosen = $locked->packages[$packageIndex];
                $chosenPrice = (float) $chosen['price'];
                $newDeposit = round($chosenPrice * 0.5, 2);
                $diff = $newDeposit - $locked->escrowed_amount;

                if ($diff > 0) {
                    $poster = $locked->user_id ? User::where('id', $locked->user_id)->lockForUpdate()->first() : null;
                    if (!$poster || $poster->wallet_restricted || $poster->wallet_balance < $diff) {
                        return ['ok' => false, 'reason' => 'poster_insufficient'];
                    }
                    $poster->decrement('wallet_balance', $diff);
                    Transaction::create(['user_id' => $poster->id, 'type' => 'gig_escrow', 'amount' => -$diff, 'description' => 'Increased deposit for gig (package: ' . $chosen['name'] . '): ' . $locked->title]);
                } elseif ($diff < 0 && $locked->user_id) {
                    $poster = User::where('id', $locked->user_id)->lockForUpdate()->first();
                    if ($poster) {
                        $poster->increment('wallet_balance', abs($diff));
                        Transaction::create(['user_id' => $poster->id, 'type' => 'gig_escrow_refund', 'amount' => abs($diff), 'description' => 'Reduced deposit for gig (package: ' . $chosen['name'] . '): ' . $locked->title]);
                    }
                }

                $locked->update([
                    'amount' => $chosenPrice,
                    'escrowed_amount' => $newDeposit,
                    'chosen_package' => $chosen['name'],
                ]);
            }

            if ($userId) {
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
            $messages = [
                'own_gig' => 'You cannot claim your own gig.',
                'invalid_package' => 'Please select a package before claiming this gig.',
                'poster_insufficient' => 'This gig\'s poster cannot currently cover the deposit for that package. Try again later or choose a lower tier if available.',
            ];
            $message = $messages[$result['reason']] ?? 'This gig is no longer available.';
            return back()->withErrors(['gig' => $message]);
        }

        if ($userId && $gig->user_id) {
            \App\Models\User::find($gig->user_id)?->notify(new \App\Notifications\GigAccepted($gig));
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

        $validated = $request->validate([
            'delivery_note' => 'nullable|string|max:1000',
            'delivery_attachment' => 'nullable|file|max:2048',
        ]);

        $deliveryAttachmentPath = $gig->delivery_attachment_path;
        if ($request->hasFile('delivery_attachment')) {
            $deliveryAttachmentPath = $request->file('delivery_attachment')->store('gig-deliveries', 'public');
        }

        $gig->update([
            'status' => 'delivered',
            'delivery_note' => $validated['delivery_note'] ?? null,
            'delivery_attachment_path' => $deliveryAttachmentPath,
            'delivered_at' => now(),
            'payment_reminder_sent_at' => null,
        ]);

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Marked as delivered — waiting for the poster to confirm and release payment.');
    }

    // Lets the claimer (logged-in or guest) back out of a gig they can't
    // finish, while it's still in_progress. Poster's 50% deposit is left
    // untouched - it's released or refunded through the normal complete()
    // or deleteGig() paths, not here.
    public function withdraw(Request $request, Gig $gig)
    {
        $isOwnerWorker = Auth::check() && $gig->accepted_by === Auth::id();
        $isGuestWorker = !Auth::check() && $gig->guest_token && $gig->guest_token === $request->cookie('qelvo_guest_token');

        abort_unless($isOwnerWorker || $isGuestWorker, 403, 'Only the person who claimed this gig can withdraw from it.');
        if ($gig->status !== 'in_progress') {
            return back()->withErrors(['gig' => 'This gig can only be withdrawn from while it is in progress.']);
        }

        DB::transaction(function () use ($gig) {
            $locked = Gig::where('id', $gig->id)->lockForUpdate()->first();
            if ($locked->status !== 'in_progress') {
                return;
            }
            $locked->update([
                'status' => 'open',
                'accepted_by' => null,
                'guest_name' => null,
                'guest_token' => null,
            ]);
        });

        return redirect()->route('gigs.show', $gig->id)->with('status', 'You\'ve withdrawn from this gig — it\'s open again for others to claim.');
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
                app(\App\Services\LevelBadgeService::class)->refresh($worker);
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

    public const MAX_REVISIONS = 3;

    public function rejectDelivery(Request $request, Gig $gig)
    {
        $user = Auth::user();
        abort_unless($gig->user_id === $user->id, 403, 'Only the poster can request changes.');
        if ($gig->status !== 'delivered') {
            return back()->withErrors(['gig' => 'This gig is not awaiting confirmation.']);
        }
        if ($gig->revision_count >= self::MAX_REVISIONS) {
            return back()->withErrors([
                'gig' => 'You\'ve already requested changes ' . self::MAX_REVISIONS . ' times. Confirm and release payment, or contact customer care if the work still isn\'t right.'
            ]);
        }

        $validated = $request->validate(['rejection_note' => 'required|string|max:1000']);

        $gig->update([
            'status' => 'in_progress',
            'delivery_note' => null,
            'delivered_at' => null,
            'rejection_note' => $validated['rejection_note'],
            'payment_reminder_sent_at' => null,
            'revision_count' => $gig->revision_count + 1,
        ]);

        return redirect()->route('gigs.show', $gig->id)->with('status', 'Sent back for revisions.');
    }

    // Called from PaystackController once a former guest pays the signup fee.
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
