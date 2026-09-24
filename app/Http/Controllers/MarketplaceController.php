<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(function ($sub) use ($search) {
                $sub->where('listings.title', 'like', '%' . $search . '%')
                    ->orWhere('listings.description', 'like', '%' . $search . '%')
                    ->orWhere('listings.store_name', 'like', '%' . $search . '%');
            });

            if (Auth::check()) {
                \App\Models\UserActivity::create([
                    'user_id' => Auth::id(),
                    'action' => 'search',
                    'search_term' => $search,
                    'category' => $request->query('category'),
                ]);
            }
        }

        if (!Auth::check()) {
            $allListings = $query->get();

            $justArrived = $allListings->take(4);
            $secondFeatured = $allListings->skip(4)->take(4)->count() ? $allListings->skip(4)->take(4) : $allListings->take(4);

            $featuredSource = $allListings->where('is_verified_seller', true)->take(4);
            if ($featuredSource->isEmpty()) {
                $featuredSource = $allListings->take(4);
            }
            $featuredPool = $featuredSource->map(function ($item) {
                return [
                    'badge' => $item->is_verified_seller ? 'VERIFIED' : 'FEATURED',
                    'title' => $item->title,
                    'price' => $item->amount,
                    'oldPrice' => null,
                    'seed' => 'listing' . $item->id,
                ];
            })->values()->all();

            $topCategories = Listing::where('status', 'available')
                ->selectRaw('category, COUNT(*) as cnt, MIN(amount) as min_amount')
                ->groupBy('category')
                ->orderByDesc('cnt')
                ->take(3)
                ->get();

            $categoryIconMap = ['Fashion' => 'shirt', 'Electronics' => 'cpu', 'Books' => 'book', 'Food' => 'utensils', 'Services' => 'wrench', 'Rentals' => 'key', 'Other' => 'grid'];
            $catTiles = $topCategories->map(function ($c) use ($categoryIconMap) {
                return [
                    'label' => $c->category,
                    'from' => $c->min_amount,
                    'icon' => $categoryIconMap[$c->category] ?? 'grid',
                    'seed' => 'cat' . strtolower($c->category),
                ];
            })->values()->all();

            return view('marketplace.guest', [
                'listings' => $allListings,
                'justArrived' => $justArrived,
                'secondFeatured' => $secondFeatured,
                'realFeaturedPool' => $featuredPool,
                'realCatTiles' => $catTiles,
                'activeCategory' => $request->query('category'),
                'activeType' => $request->query('type'),
                'searchQuery' => $request->query('q'),
            ]);
        }

        $recommended = collect();
        if (Auth::check() && !$request->filled('q') && !$request->filled('category')) {
            $topCategories = \App\Models\UserActivity::where('user_id', Auth::id())
                ->whereNotNull('category')
                ->selectRaw('category, COUNT(*) as cnt')
                ->groupBy('category')
                ->orderByDesc('cnt')
                ->take(3)
                ->pluck('category');

            if ($topCategories->isNotEmpty()) {
                $recommended = Listing::where('status', 'available')
                    ->whereIn('category', $topCategories)
                    ->where('user_id', '!=', Auth::id())
                    ->latest()
                    ->take(8)
                    ->get();
            }
        }

        return view('marketplace.index', [
            'listings' => $query->get(),
            'activeCategory' => $request->query('category'),
            'activeType' => $request->query('type'),
            'searchQuery' => $request->query('q'),
            'recommended' => $recommended,
        ]);
    }

    public function show(Listing $listing)
    {
        if (Auth::check() && Auth::id() !== $listing->user_id) {
            \App\Models\UserActivity::create([
                'user_id' => Auth::id(),
                'action' => 'view',
                'category' => $listing->category,
                'listing_id' => $listing->id,
            ]);
        }

        $seller = $listing->user_id ? User::find($listing->user_id) : null;
        $reviews = $listing->reviews()->with('reviewer')->latest()->get();
        $avgRating = $reviews->avg('rating');

        $isOwner = Auth::check() && Auth::id() === $listing->user_id;
        $messages = collect();
        $conversations = collect();

        if (Auth::check() && $listing->user_id) {
            $convs = \App\Models\Conversation::where('type', 'business')
                ->where('subject_type', Listing::class)
                ->where('subject_id', $listing->id)
                ->whereHas('participants', fn($q) => $q->where('user_id', Auth::id()))
                ->with(['messages.sender', 'participants'])
                ->get();

            if ($isOwner) {
                // Group all messages by the other party (each distinct buyer who has messaged).
                foreach ($convs as $conv) {
                    $otherId = $conv->participants->pluck('user_id')->first(fn($id) => $id !== $listing->user_id);
                    if ($otherId) {
                        $conversations[$otherId] = $conv->messages;
                    }
                }
            } else {
                $conv = $convs->first();
                $messages = $conv ? $conv->messages : collect();
            }
        }

        return view('marketplace.show', [
            'listing' => $listing,
            'seller' => $seller,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'messages' => $messages,
            'conversations' => $conversations,
            'isOwner' => $isOwner,
        ]);
    }

    public function toggleFavorite(Listing $listing)
    {
        $userId = Auth::id();
        $exists = $listing->favoritedBy()->where('user_id', $userId)->exists();

        if ($exists) {
            $listing->favoritedBy()->detach($userId);
            return back()->with('status', 'Removed from favorites.');
        }

        $listing->favoritedBy()->attach($userId);
        return back()->with('status', 'Added to favorites.');
    }

    public function favorites()
    {
        $listings = Auth::user()->favoriteListings()->latest('listing_favorites.created_at')->get();
        return view('marketplace.favorites', ['listings' => $listings]);
    }

    // Seller marks a ship-method listing as sent. Buyer sees this on the
    // listing page. Does not release escrow — that still needs Confirm
    // Receipt (or the dispute/auto-release fallback) same as before.
    public function markShipped(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === Auth::id(), 403, 'Only the seller can mark this as shipped.');
        abort_unless($listing->delivery_method === 'ship', 422, 'This listing is not set up for shipping.');
        abort_unless($listing->status === 'sold', 422, 'This listing has no pending sale to ship.');

        $validated = $request->validate(['shipping_note' => 'nullable|string|max:500']);

        $listing->update([
            'shipped_at' => now(),
            'shipping_note' => $validated['shipping_note'] ?? $listing->shipping_note,
        ]);

        if ($listing->buyer_id) {
            $note = $validated['shipping_note'] ?? null;
            \App\Models\ListingMessage::postSystemEvent(
                $listing->id, Auth::id(), $listing->buyer_id,
                '📦 Marked as shipped.' . ($note ? ' ' . $note : '')
            );
            \App\Models\User::find($listing->buyer_id)?->notify(new \App\Notifications\MarketplaceOrderShipped($listing));
        }

        return back()->with('status', 'Marked as shipped.');
    }

    public function myOrders()
    {
        $orders = \App\Models\ListingOrder::where('buyer_id', Auth::id())
            ->with('listing')
            ->latest()
            ->get();
        return view('marketplace.orders', ['orders' => $orders]);
    }

    private function findOrCreateListingConversation(int $listingId, int $userA, int $userB): \App\Models\Conversation
    {
        $ids = collect([$userA, $userB])->unique()->sort()->values();
        $existing = \App\Models\Conversation::where('type', 'business')
            ->where('subject_type', \App\Models\Listing::class)
            ->where('subject_id', $listingId)
            ->whereHas('participants', fn($q) => $q->where('user_id', $ids[0]))
            ->whereHas('participants', fn($q) => $q->where('user_id', $ids->count() > 1 ? $ids[1] : $ids[0]))
            ->get()
            ->first(function ($conv) use ($ids) {
                $convUserIds = $conv->participants()->pluck('user_id')->unique()->sort()->values();
                return $convUserIds->count() === $ids->count() && $convUserIds->all() === $ids->all();
            });
        if ($existing) return $existing;

        $conv = \App\Models\Conversation::create([
            'type' => 'business',
            'subject_type' => \App\Models\Listing::class,
            'subject_id' => $listingId,
        ]);
        foreach ($ids as $uid) {
            \App\Models\ConversationParticipant::firstOrCreate(['conversation_id' => $conv->id, 'user_id' => $uid]);
        }
        return $conv;
    }

    public function sendMessage(Request $request, Listing $listing)
    {
        if (!app(\App\Services\TierService::class)->unlocked(Auth::user(), 'chat.enabled')) {
            return back()->withErrors(['message' => 'Chat is currently unavailable.']);
        }

        $validated = $request->validate([
            'body' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:4096',
            'recipient_id' => 'nullable|exists:users,id',
        ]);
        if (empty($validated['body']) && !$request->hasFile('attachment')) {
            return back()->withErrors(['message' => 'Type a message or attach a photo.']);
        }

        if (!$listing->user_id) {
            return back()->withErrors(['message' => 'This listing has no seller to message.']);
        }

        $userId = Auth::id();

        if ($userId === $listing->user_id) {
            $recipientId = $validated['recipient_id'] ?? null;
            if (!$recipientId) {
                return back()->withErrors(['message' => 'Select a buyer to reply to.']);
            }
        } else {
            $recipientId = $listing->user_id;
        }

        $conversation = $this->findOrCreateListingConversation($listing->id, $userId, $recipientId);

        $messageType = 'text';
        $meta = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
            $messageType = 'image';
            $meta = ['url' => '/storage/' . $path];
        }

        $message = \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
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

    // Posts a structured meetup-proposal card into the thread. Only meaningful
    // for sold, meetup-delivery listings. Either party (buyer or seller) can
    // propose — whoever didn't propose is the one who can accept/decline.
    public function proposeMeetup(Request $request, Listing $listing)
    {
        abort_unless($listing->status === 'sold', 422, 'This listing has no active sale to arrange a meetup for.');
        abort_unless($listing->delivery_method === 'meetup', 422, 'This listing is not set up for meetup delivery.');

        $userId = Auth::id();
        $isOwner = $listing->user_id === $userId;
        abort_unless($isOwner || $listing->buyer_id === $userId, 403, 'Only the buyer or seller can propose a meetup.');

        $validated = $request->validate([
            'when' => 'required|string|max:200',
            'where' => 'required|string|max:200',
            'recipient_id' => 'nullable|exists:users,id',
        ]);

        $recipientId = $isOwner ? ($validated['recipient_id'] ?? $listing->buyer_id) : $listing->user_id;
        abort_unless($recipientId, 422, 'No counterparty to send this proposal to.');

        $conversation = $this->findOrCreateListingConversation($listing->id, $userId, $recipientId);
        $message = \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => '📍 Meetup proposed: ' . $validated['when'] . ' at ' . $validated['where'],
            'message_type' => 'meetup_proposal',
            'meta' => [
                'when' => $validated['when'],
                'where' => $validated['where'],
                'status' => 'pending',
                'proposed_by' => $userId,
            ],
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

    // Buyer or seller (whichever didn't propose) accepts/declines. Posts a
    // system-event confirmation into the same thread on either outcome.
    public function respondMeetup(Request $request, Listing $listing, \App\Models\ConversationMessage $message)
    {
        abort_unless($message->conversation->subject_type === \App\Models\Listing::class && (int) $message->conversation->subject_id === $listing->id, 404);
        abort_unless($message->message_type === 'meetup_proposal', 422, 'This message is not a meetup proposal.');

        $meta = $message->meta ?? [];
        abort_unless(($meta['status'] ?? null) === 'pending', 422, 'This proposal has already been responded to.');

        $userId = Auth::id();
        abort_if((int) ($meta['proposed_by'] ?? 0) === $userId, 403, 'You cannot respond to your own proposal.');
        abort_unless($message->conversation->participants()->where('user_id', $userId)->exists(), 403, 'Not part of this conversation.');

        $validated = $request->validate(['action' => 'required|in:accept,decline']);

        $meta['status'] = $validated['action'] === 'accept' ? 'accepted' : 'declined';
        $message->update(['meta' => $meta]);

        $eventText = $validated['action'] === 'accept'
            ? '✅ Meetup confirmed — ' . ($meta['when'] ?? '') . ' at ' . ($meta['where'] ?? '')
            : '❌ Meetup proposal declined.';
        \App\Models\ConversationMessage::create([
            'conversation_id' => $message->conversation_id,
            'sender_id' => $userId,
            'is_system' => true,
            'message_type' => 'text',
            'body' => $eventText,
        ]);
        $message->conversation->update(['last_message_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['status' => $meta['status']]);
        }

        return back();
    }

    // Polling endpoint — returns messages for a thread as JSON so the chat
    // updates without a full page reload. Buyers pass no other_user_id
    // (thread is always with the seller). Sellers must pass other_user_id
    // since they may have multiple buyer threads on one listing.
    public function messages(Request $request, Listing $listing)
    {
        $userId = Auth::id();
        $isOwner = $listing->user_id === $userId;
        $otherUserId = $isOwner ? $request->query('other_user_id') : $listing->user_id;

        abort_unless($otherUserId, 422, 'other_user_id is required.');

        $conversation = $this->findOrCreateListingConversation($listing->id, $userId, (int) $otherUserId);
        $messages = $conversation->messages()->get()->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'body' => $m->body,
            'is_system' => $m->is_system,
            'message_type' => $m->message_type,
            'meta' => $m->meta,
            'created_at' => $m->created_at->diffForHumans(),
        ]);

        return response()->json($messages);
    }

    // STEP 1: buyer pays, funds sit in escrow. Seller is NOT paid yet.
    // Locked to close a race where two buyers could purchase the same listing simultaneously.
    public function purchase(Listing $listing)
    {
        $buyer = Auth::user();

        return DB::transaction(function () use ($buyer, $listing) {
            $lockedListing = Listing::where('id', $listing->id)->lockForUpdate()->first();
            $lockedBuyer = User::where('id', $buyer->id)->lockForUpdate()->first();

            if ($lockedBuyer->wallet_restricted) {
                return back()->withErrors(['balance' => 'Your wallet is restricted due to an unresolved payment issue.']);
            }

            if ($lockedListing->user_id === $lockedBuyer->id) {
                return back()->withErrors(['balance' => 'You cannot buy your own listing.']);
            }

            if ($lockedListing->status !== 'available') {
                return back()->withErrors(['balance' => 'This listing is no longer available.']);
            }

            if ($lockedBuyer->wallet_balance < $lockedListing->amount) {
                return back()->withErrors(['balance' => 'Insufficient wallet balance. Top up your wallet first.']);
            }

            $lockedBuyer->decrement('wallet_balance', $lockedListing->amount);
            Transaction::create([
                'user_id' => $lockedBuyer->id,
                'type' => 'marketplace_escrow',
                'amount' => -$lockedListing->amount,
                'description' => 'Paid (held in escrow): ' . $lockedListing->title,
            ]);

            if ($lockedListing->stock_quantity !== null) {
                \App\Models\ListingOrder::create([
                    'listing_id' => $lockedListing->id,
                    'buyer_id' => $lockedBuyer->id,
                    'amount' => $lockedListing->amount,
                    'status' => 'paid',
                ]);
                $lockedListing->decrement('stock_quantity');
                if ($lockedListing->fresh()->stock_quantity <= 0) {
                    $lockedListing->update(['status' => 'sold']);
                }
            } else {
                $lockedListing->update([
                    'status' => 'sold',
                    'buyer_id' => $lockedBuyer->id,
                    'sold_at' => now(),
                ]);
            }

            if ($lockedListing->user_id) {
                \App\Models\ListingMessage::postSystemEvent(
                    $lockedListing->id, $lockedBuyer->id, $lockedListing->user_id,
                    '💰 Payment sent — ₦' . number_format($lockedListing->amount) . ' held in escrow until delivery is confirmed.'
                );
                \App\Models\User::find($lockedListing->user_id)?->notify(new \App\Notifications\MarketplaceOrderPlaced($lockedListing, $lockedBuyer));
            }

            return redirect()->route('marketplace.show', $lockedListing->id)->with('status', 'Payment sent — funds are held until you confirm you received the item.');
        });
    }

    // STEP 2: buyer confirms delivery. Only now does escrow release to the seller, minus platform commission.
    // Locked to close a double-payout race (duplicate/double-click requests could have paid the seller twice).
    public function confirmDelivery(Listing $listing)
    {
        $buyer = Auth::user();

        return DB::transaction(function () use ($buyer, $listing) {
            $lockedListing = Listing::where('id', $listing->id)->lockForUpdate()->first();

            abort_unless($lockedListing->buyer_id === $buyer->id, 403, 'Only the buyer can confirm this delivery.');
            if ($lockedListing->status !== 'sold') {
                return back()->withErrors(['balance' => 'This listing has no pending escrow to release.']);
            }

            $seller = $lockedListing->user_id ? User::where('id', $lockedListing->user_id)->lockForUpdate()->first() : null;

            if ($seller) {
                $rate = match ($seller->tier) {
                    'tier2', 'tier3' => 0,
                    'tier1' => 0.03,
                    default => 0.05,
                };
                $commission = round($lockedListing->amount * $rate, 2);
                $payout = $lockedListing->amount - $commission;

                $seller->increment('wallet_balance', $payout);
                Transaction::create([
                    'user_id' => $seller->id,
                    'type' => 'marketplace_sale',
                    'amount' => $payout,
                    'description' => 'Sold: ' . $lockedListing->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : ''),
                ]);
                \App\Models\PlatformRevenue::record('marketplace', $commission, 'Commission: ' . $lockedListing->title, 'listing', $lockedListing->id);
                $seller->notify(new \App\Notifications\MarketplaceSaleCompleted($lockedListing, $payout));
            }

            $lockedListing->update(['status' => 'completed']);

            if ($lockedListing->user_id) {
                \App\Models\ListingMessage::postSystemEvent(
                    $lockedListing->id, $buyer->id, $lockedListing->user_id,
                    '✅ Delivery confirmed — payment released to the seller.'
                );
            }

            return redirect()->route('marketplace.show', $lockedListing->id)->with('status', 'Delivery confirmed — payment released to the seller.');
        });
    }

    public function confirmOrderDelivery(\App\Models\ListingOrder $order)
    {
        $buyer = Auth::user();

        return DB::transaction(function () use ($buyer, $order) {
            $lockedOrder = \App\Models\ListingOrder::where('id', $order->id)->lockForUpdate()->first();

            abort_unless($lockedOrder->buyer_id === $buyer->id, 403, 'Only the buyer can confirm this delivery.');
            if ($lockedOrder->status !== 'paid') {
                return back()->withErrors(['balance' => 'This order has no pending escrow to release.']);
            }

            $listing = Listing::where('id', $lockedOrder->listing_id)->first();
            $seller = $listing->user_id ? User::where('id', $listing->user_id)->lockForUpdate()->first() : null;

            if ($seller) {
                $rate = match ($seller->tier) {
                    'tier2', 'tier3' => 0,
                    'tier1' => 0.03,
                    default => 0.05,
                };
                $commission = round($lockedOrder->amount * $rate, 2);
                $payout = $lockedOrder->amount - $commission;

                $seller->increment('wallet_balance', $payout);
                Transaction::create([
                    'user_id' => $seller->id,
                    'type' => 'marketplace_sale',
                    'amount' => $payout,
                    'description' => 'Sold (unit): ' . $listing->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : ''),
                ]);
                \App\Models\PlatformRevenue::record('marketplace', $commission, 'Commission (unit): ' . $listing->title, 'listing_order', $lockedOrder->id);
                $seller->notify(new \App\Notifications\MarketplaceSaleCompleted($listing, $payout));
            }

            $lockedOrder->update(['status' => 'completed']);

            return redirect()->route('marketplace.show', $listing->id)->with('status', 'Delivery confirmed — payment released to the seller.');
        });
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

    public function edit(Listing $listing)
    {
        abort_unless($listing->user_id === Auth::id(), 403, 'You can only edit your own listings.');
        abort_if($listing->status !== 'available', 403, 'You cannot edit a listing that has already been sold.');

        return view('marketplace.edit', ['listing' => $listing]);
    }

    public function update(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === Auth::id(), 403, 'You can only edit your own listings.');
        abort_if($listing->status !== 'available', 403, 'You cannot edit a listing that has already been sold.');

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
            'images' => 'nullable|array|max:6',
            'images.*' => 'image|max:4096',
            'delivery_method' => 'required|in:meetup,ship',
        ]);

        $uploadedUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $uploadedUrls[] = '/storage/' . $file->store('listings', 'public');
            }
        }

        $listing->update([
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
            'image_url' => $uploadedUrls[0] ?? $listing->image_url,
            'delivery_method' => $validated['delivery_method'],
        ]);

        if ($uploadedUrls) {
            $startOrder = $listing->images()->max('sort_order') + 1;
            foreach ($uploadedUrls as $i => $url) {
                $listing->images()->create(['image_url' => $url, 'sort_order' => $startOrder + $i]);
            }
        }

        return redirect()->route('marketplace.show', $listing->id)->with('status', 'Listing updated.');
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
            'images' => 'nullable|array|max:6',
            'images.*' => 'image|max:4096',
            'stock_quantity' => 'nullable|integer|min:1',
            'delivery_method' => 'required|in:meetup,ship',
        ]);

        $uploadedUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $uploadedUrls[] = '/storage/' . $file->store('listings', 'public');
            }
        }

        $canTrackStock = in_array(Auth::user()->tier, ['tier2', 'tier3'], true);

        $listing = Listing::create([
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
            'image_url' => $uploadedUrls[0] ?? null,
            'stock_quantity' => $canTrackStock ? ($validated['stock_quantity'] ?? null) : null,
            'delivery_method' => $validated['delivery_method'],
        ]);

        foreach ($uploadedUrls as $i => $url) {
            $listing->images()->create(['image_url' => $url, 'sort_order' => $i]);
        }

        return redirect()->route('marketplace.index');
    }
}
