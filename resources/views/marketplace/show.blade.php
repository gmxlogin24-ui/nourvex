@extends('layouts.app-authenticated')
@section('content')
<link rel="stylesheet" href="{{ asset('css/marketplace-neu.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/marketplace-detail.css') }}?v={{ time() }}">

<main class="max-w-4xl mx-auto p-6">
<div class="mpd-page">

    <div class="mpd-topbar">
        <a href="{{ route('marketplace.index') }}" class="mpd-icon-btn">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div class="mpd-topbar-actions">
            @auth
                @if(!$isOwner)
                <form method="POST" action="{{ route('marketplace.favorite', $listing->id) }}">
                    @csrf
                    @php $isFavorited = $listing->favoritedBy()->where('user_id', auth()->id())->exists(); @endphp
                    <button type="submit" class="mpd-icon-btn" style="{{ $isFavorited ? 'color:#ff4757;' : '' }}">
                        <i data-lucide="heart" class="w-4 h-4" style="{{ $isFavorited ? 'fill:#ff4757;' : '' }}"></i>
                    </button>
                </form>
                @endif
            @endauth
            <a href="{{ Route::has('cart.index') ? route('cart.index') : '#' }}" class="mpd-icon-btn">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <div class="mpd-image-wrap reveal-on-scroll">
        @if($listing->images->count() > 0)
            <img id="mainListingImage" src="{{ $listing->images->first()->image_url }}" alt="{{ $listing->title }}" class="mpd-image">
        @elseif($listing->image_url)
            <img id="mainListingImage" src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="mpd-image">
        @else
            <div class="mpd-image mpd-image-placeholder"><i data-lucide="image" class="w-12 h-12"></i></div>
        @endif
        <span class="type-badge type-{{ $listing->listing_type }} mpd-type-badge">{{ ucfirst($listing->listing_type) }}</span>
    </div>
    @if($listing->images->count() > 1)
        <div class="mpd-thumb-row">
            @foreach($listing->images as $img)
                <img src="{{ $img->image_url }}" onclick="document.getElementById('mainListingImage').src = this.src;" class="mpd-thumb">
            @endforeach
        </div>
    @endif

    <div class="mpd-info">
        @if($listing->store_name)
            <p class="mpd-store">{{ $listing->store_name }}</p>
        @endif
        <div class="mpd-title-row">
            <h1 class="mpd-title">{{ $listing->title }}</h1>
            @if($avgRating)
                <span class="mpd-rating"><i data-lucide="star" class="w-3 h-3"></i> {{ number_format($avgRating, 1) }}</span>
            @endif
        </div>
        <div class="mpd-meta-row">
            <span class="mpd-meta-pill"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $listing->location }}</span>
            <span class="mpd-meta-pill"><i data-lucide="tag" class="w-3 h-3"></i> {{ $listing->condition }}</span>
        </div>

        <p class="mpd-price">₦{{ number_format($listing->amount) }}</p>

        <a href="{{ $seller ? route('profile.index') : '#' }}" class="mpd-seller-card">
            <div class="mpd-seller-avatar">
                {{ $seller ? strtoupper(substr($seller->business_name ?? $seller->name, 0, 1)) : '?' }}
            </div>
            <div class="mpd-seller-body">
                <p class="mpd-seller-name">{{ $listing->seller_name }}</p>
                @if($listing->is_verified_seller)
                    <p class="mpd-seller-verified"><i data-lucide="badge-check" class="w-3 h-3"></i> Verified seller</p>
                @else
                    <p class="mpd-seller-sub">Student seller</p>
                @endif
                @if($seller)
                    <p class="mpd-seller-sub">Member since {{ $seller->created_at->format('M Y') }}</p>
                @endif
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 mpd-seller-chevron"></i>
        </a>

        <p class="mpd-description">{{ $listing->description }}</p>

        @if ($errors->any())
            <p class="text-red-500 text-sm mt-4">{{ $errors->first() }}</p>
        @endif

        @auth
            @if($listing->status === 'sold' && $listing->buyer_id === auth()->id())
                @if($listing->delivery_method === 'ship')
                <div class="glass-card p-3 mt-6" style="border:1px solid var(--border-color);">
                    @if($listing->shipped_at)
                    <p class="text-sm font-medium flex items-center gap-1"><i data-lucide="truck" class="w-4 h-4"></i> Shipped {{ $listing->shipped_at->diffForHumans() }}</p>
                    @if($listing->shipping_note)
                        <p class="text-muted text-xs mt-1">{{ $listing->shipping_note }}</p>
                    @endif
                    @else
                    <p class="text-sm text-muted flex items-center gap-1"><i data-lucide="clock" class="w-4 h-4"></i> Seller hasn't marked this as shipped yet.</p>
                    @endif
                </div>
                @endif
                <form method="POST" action="{{ route('marketplace.confirm', $listing->id) }}">
                    @csrf
                    <button type="submit" class="mpd-btn-solid mpd-btn-full mt-4">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Receipt — Release Payment
                    </button>
                </form>
                <p class="text-muted text-xs mt-2 text-center">Funds are held in escrow until you confirm you received this.</p>
                @if(!$listing->dispute || $listing->dispute->status !== 'open')
                <button type="button" onclick="document.getElementById('disputeFormBox').style.display='block';this.style.display='none';" class="text-red-500 text-xs mt-2 w-full text-center" style="background:none;border:none;">
                    Something wrong? File a complaint
                </button>
                <div id="disputeFormBox" style="display:none;">
                    <form method="POST" action="{{ route('marketplace.dispute', $listing->id) }}" class="mt-2 space-y-2">
                        @csrf
                        <textarea name="description" required rows="2" placeholder="What went wrong?" class="input-field !mt-0 text-sm"></textarea>
                        <button type="submit" class="btn-secondary text-sm w-full justify-center">Submit Complaint</button>
                    </form>
                </div>
                @else
                <p class="text-amber-500 text-xs mt-2 text-center">Complaint submitted — admin is reviewing.</p>
                @endif
            @elseif($listing->status === 'sold' && $listing->user_id === auth()->id())
                @if($listing->delivery_method === 'ship')
                <div class="glass-card p-4 mt-6">
                    <p class="text-sm font-medium mb-2">This item sold — arrange shipping</p>
                    @if($listing->shipped_at)
                    <p class="text-accent text-sm flex items-center gap-1"><i data-lucide="truck" class="w-4 h-4"></i> Marked shipped {{ $listing->shipped_at->diffForHumans() }}</p>
                    @else
                    <form method="POST" action="{{ route('marketplace.ship', $listing->id) }}" class="space-y-2">
                        @csrf
                        <input type="text" name="shipping_note" placeholder="Courier / tracking info (optional)" class="input-field !mt-0 text-sm">
                        <button type="submit" class="btn-primary text-sm w-full justify-center">Mark as Shipped</button>
                    </form>
                    @endif
                </div>
                @else
                <p class="text-muted text-sm mt-6 text-center">Sold — awaiting buyer confirmation.</p>
                @endif
            @elseif($listing->status === 'completed')
                <p class="text-accent text-sm mt-6 text-center font-medium">✓ Purchase completed</p>
            @elseif($listing->status !== 'available')
                <p class="text-muted text-sm mt-6 text-center">This listing is no longer available.</p>
            @else
                @if($listing->user_id === auth()->id())
                    <a href="{{ route('marketplace.edit', $listing->id) }}" class="mpd-btn-ghost mpd-btn-full">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit Listing
                    </a>
                @else
                    <div class="mpd-action-row">
                        <form method="POST" action="{{ route('cart.add', $listing->id) }}" style="flex:1;">
                            @csrf
                            <button type="submit" class="mpd-btn-ghost mpd-btn-full">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
                            </button>
                        </form>
                        <form method="POST" action="{{ route('marketplace.purchase', $listing->id) }}" style="flex:1;" onsubmit="return confirm('Buy this item for ₦{{ number_format($listing->amount) }}? The amount will be held in escrow until you confirm delivery.');">
                            @csrf
                            <button type="submit" class="mpd-btn-solid mpd-btn-full">
                                Buy Now
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        @else
            <a href="{{ route('register') }}" class="mpd-btn-solid mpd-btn-full mt-6">
                <i data-lucide="lock" class="w-4 h-4"></i> Sign up to Buy — ₦{{ number_format($listing->amount) }}
            </a>
        @endauth
    </div>
</div>

@auth
    @if($seller && !$isOwner)
    <div class="glass-card p-5 mt-6 reveal-on-scroll">
        <h2 class="font-semibold mb-3 flex items-center gap-2"><i data-lucide="message-square" class="w-4 h-4"></i> Chat with Seller</h2>
        <div id="chatMessages" class="space-y-3 mb-4" style="max-height:300px;overflow-y:auto;">
            @forelse($messages as $msg)
            @if($msg->is_system)
            <div class="text-center" data-msg-id="{{ $msg->id }}">
                <span class="text-muted text-xs" style="display:inline-block;padding:0.35rem 0.75rem;border-radius:999px;background:var(--color-card);">{{ $msg->body }}</span>
            </div>
            @elseif($msg->message_type === 'meetup_proposal')
            <div class="meetup-card" data-msg-id="{{ $msg->id }}" style="background:var(--color-card);border:1px solid var(--border-color);border-radius:1rem;padding:0.85rem;max-width:85%;{{ $msg->sender_id === auth()->id() ? 'margin-left:auto;' : '' }}">
                <p class="text-sm font-medium flex items-center gap-1"><i data-lucide="map-pin" class="w-4 h-4"></i> Meetup proposed</p>
                <p class="text-xs text-muted mt-1">{{ $msg->meta['when'] ?? '' }} — {{ $msg->meta['where'] ?? '' }}</p>
                @if(($msg->meta['status'] ?? 'pending') === 'pending')
                    @if((int)($msg->meta['proposed_by'] ?? 0) !== auth()->id())
                    <div class="flex gap-2 mt-2">
                        <button type="button" class="meetup-respond-btn btn-primary text-xs" data-msg-id="{{ $msg->id }}" data-action="accept" style="padding:0.35rem 0.75rem;">Accept</button>
                        <button type="button" class="meetup-respond-btn btn-secondary text-xs" data-msg-id="{{ $msg->id }}" data-action="decline" style="padding:0.35rem 0.75rem;">Decline</button>
                    </div>
                    @else
                    <p class="text-xs text-muted mt-2">Waiting for a response...</p>
                    @endif
                @elseif($msg->meta['status'] === 'accepted')
                <p class="text-accent text-xs mt-2 font-medium">✅ Accepted</p>
                @else
                <p class="text-xs mt-2" style="color:#ef4444;">❌ Declined</p>
                @endif
            </div>
            @else
            <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}" data-msg-id="{{ $msg->id }}">
                <div class="{{ $msg->sender_id === auth()->id() ? 'btn-primary' : 'bg-card' }}" style="max-width:75%;padding:0.6rem 0.9rem;border-radius:1rem;">
                    @if($msg->message_type === 'image' && ($msg->meta['url'] ?? null))
                    <img src="{{ $msg->meta['url'] }}" style="max-width:100%;border-radius:0.5rem;margin-bottom:{{ $msg->body ? '0.4rem' : '0' }};">
                    @endif
                    @if($msg->body)<p class="text-sm">{{ $msg->body }}</p>@endif
                    <p class="text-xs mt-1" style="opacity:0.65;">{{ $msg->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endif
            @empty
            <p class="text-muted text-sm" id="chatEmptyState">No messages yet — ask the seller a question.</p>
            @endforelse
        </div>
        <div class="chat-chips flex gap-2 mb-2" style="flex-wrap:wrap;">
            @if($listing->status === 'available')
            <button type="button" class="chat-chip" data-chip-text="Is this still available?">Is this still available?</button>
            <button type="button" class="chat-chip" data-chip-text="Can you do a lower price?">Can you negotiate?</button>
            @elseif($listing->status === 'sold' && $listing->buyer_id === auth()->id())
                @if($listing->delivery_method === 'meetup')
            <button type="button" class="chat-chip" id="proposeMeetupBtn" data-listing-id="{{ $listing->id }}">📍 Propose meetup</button>
                @else
            <button type="button" class="chat-chip" data-chip-text="Any updates on shipping?">Any shipping updates?</button>
                @endif
            @endif
        </div>
        <div id="meetupProposeForm" style="display:none;" class="mb-2 flex gap-2 flex-wrap">
            <input type="text" id="meetupWhen" placeholder="When? e.g. Tomorrow 3pm" class="input-field !mt-0" style="flex:1;min-width:140px;">
            <input type="text" id="meetupWhere" placeholder="Where? e.g. Main gate" class="input-field !mt-0" style="flex:1;min-width:140px;">
            <button type="button" id="meetupSubmitBtn" class="btn-primary text-sm" style="padding:0.5rem 1rem;">Send</button>
        </div>
        <form class="chat-form flex gap-2" data-listing-id="{{ $listing->id }}" data-my-id="{{ auth()->id() }}" enctype="multipart/form-data">
            @csrf
            <label class="btn-secondary" style="padding:0.6rem 0.75rem;cursor:pointer;">
                <i data-lucide="paperclip" class="w-4 h-4"></i>
                <input type="file" name="attachment" accept="image/*" style="display:none;" class="chat-attachment-input">
            </label>
            <input type="text" name="body" placeholder="Type a message..." class="input-field !mt-0 flex-1">
            <button type="submit" class="btn-primary" style="padding:0.6rem 1rem;"><i data-lucide="send" class="w-4 h-4"></i></button>
        </form>
    </div>
    @elseif($isOwner && $conversations->count() > 0)
    <div class="glass-card p-5 mt-6 reveal-on-scroll">
        <h2 class="font-semibold mb-3 flex items-center gap-2"><i data-lucide="message-square" class="w-4 h-4"></i> Buyer Messages</h2>
        @foreach($conversations as $otherUserId => $thread)
        @php $otherUser = $thread->first()->sender_id == $otherUserId ? $thread->first()->sender : $thread->first()->recipient; @endphp
        <div class="mb-4" style="border-bottom:1px solid var(--border-color);padding-bottom:1rem;">
            <p class="text-sm font-medium mb-2">{{ $otherUser->name ?? 'User' }}</p>
            <div id="chatMessages-{{ $otherUserId }}" class="space-y-2 mb-3" style="max-height:200px;overflow-y:auto;">
                @foreach($thread as $msg)
                @if($msg->is_system)
                <div class="text-center" data-msg-id="{{ $msg->id }}">
                    <span class="text-muted text-xs" style="display:inline-block;padding:0.3rem 0.65rem;border-radius:999px;background:var(--color-bg);">{{ $msg->body }}</span>
                </div>
                @elseif($msg->message_type === 'meetup_proposal')
                <div class="meetup-card" data-msg-id="{{ $msg->id }}" style="background:var(--color-bg);border:1px solid var(--border-color);border-radius:0.85rem;padding:0.7rem;max-width:85%;{{ $msg->sender_id === auth()->id() ? 'margin-left:auto;' : '' }}">
                    <p class="text-xs font-medium flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> Meetup proposed</p>
                    <p class="text-xs text-muted mt-1">{{ $msg->meta['when'] ?? '' }} — {{ $msg->meta['where'] ?? '' }}</p>
                    @if(($msg->meta['status'] ?? 'pending') === 'pending')
                        @if((int)($msg->meta['proposed_by'] ?? 0) !== auth()->id())
                        <div class="flex gap-2 mt-2">
                            <button type="button" class="meetup-respond-btn btn-primary text-xs" data-msg-id="{{ $msg->id }}" data-action="accept" style="padding:0.3rem 0.6rem;">Accept</button>
                            <button type="button" class="meetup-respond-btn btn-secondary text-xs" data-msg-id="{{ $msg->id }}" data-action="decline" style="padding:0.3rem 0.6rem;">Decline</button>
                        </div>
                        @else
                        <p class="text-xs text-muted mt-1">Waiting for a response...</p>
                        @endif
                    @elseif($msg->meta['status'] === 'accepted')
                    <p class="text-accent text-xs mt-1 font-medium">✅ Accepted</p>
                    @else
                    <p class="text-xs mt-1" style="color:#ef4444;">❌ Declined</p>
                    @endif
                </div>
                @else
                <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}" data-msg-id="{{ $msg->id }}">
                    <div class="{{ $msg->sender_id === auth()->id() ? 'btn-primary' : 'bg-card' }}" style="max-width:75%;padding:0.5rem 0.8rem;border-radius:1rem;">
                        <p class="text-sm">{{ $msg->body }}</p>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @if($listing->status === 'sold' && $listing->buyer_id === (int) $otherUserId)
            <div class="chat-chips flex gap-2 mb-2" style="flex-wrap:wrap;">
                @if($listing->delivery_method === 'meetup')
            <button type="button" class="chat-chip seller-propose-meetup-btn" data-listing-id="{{ $listing->id }}" data-other-user-id="{{ $otherUserId }}">📍 Propose meetup</button>
                @elseif(!$listing->shipped_at)
            <button type="button" class="chat-chip" data-chip-text="I'll ship this today.">I'll ship this today</button>
                @endif
            </div>
            @endif
            <form class="chat-form flex gap-2" data-listing-id="{{ $listing->id }}" data-my-id="{{ auth()->id() }}" data-other-user-id="{{ $otherUserId }}">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $otherUserId }}">
                <input type="text" name="body" required placeholder="Reply..." class="input-field !mt-0 flex-1">
                <button type="submit" class="btn-primary" style="padding:0.6rem 1rem;"><i data-lucide="send" class="w-4 h-4"></i></button>
            </form>
        </div>
        @endforeach
    </div>
    @endif
    @endauth

    @auth
    @if(($seller && !$isOwner) || ($isOwner && $conversations->count() > 0))
    <script>
    (function() {
        const listingId = {{ $listing->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;

        function meetupCardHtml(msg, myId) {
            const mine = String(msg.sender_id) === String(myId);
            const meta = msg.meta || {};
            const status = meta.status || 'pending';
            const proposedByMe = String(meta.proposed_by) === String(myId);
            let actionsHtml = '';
            if (status === 'pending') {
                actionsHtml = proposedByMe
                    ? '<p class="text-xs text-muted mt-2">Waiting for a response...</p>'
                    : `<div class="flex gap-2 mt-2">
                        <button type="button" class="meetup-respond-btn btn-primary text-xs" data-msg-id="${msg.id}" data-action="accept" style="padding:0.35rem 0.75rem;">Accept</button>
                        <button type="button" class="meetup-respond-btn btn-secondary text-xs" data-msg-id="${msg.id}" data-action="decline" style="padding:0.35rem 0.75rem;">Decline</button>
                       </div>`;
            } else if (status === 'accepted') {
                actionsHtml = '<p class="text-accent text-xs mt-2 font-medium">✅ Accepted</p>';
            } else {
                actionsHtml = '<p class="text-xs mt-2" style="color:#ef4444;">❌ Declined</p>';
            }
            const div = document.createElement('div');
            div.className = 'meetup-card';
            div.dataset.msgId = msg.id;
            div.style.cssText = `background:var(--color-card);border:1px solid var(--border-color);border-radius:1rem;padding:0.85rem;max-width:85%;${mine ? 'margin-left:auto;' : ''}`;
            const title = document.createElement('p');
            title.className = 'text-sm font-medium flex items-center gap-1';
            title.innerHTML = '<i data-lucide="map-pin" class="w-4 h-4"></i> Meetup proposed';
            const detail = document.createElement('p');
            detail.className = 'text-xs text-muted mt-1';
            detail.textContent = (meta.when || '') + ' — ' + (meta.where || ''); // textContent — avoids XSS
            div.appendChild(title);
            div.appendChild(detail);
            const actionsWrap = document.createElement('div');
            actionsWrap.innerHTML = actionsHtml; // static strings only, no user input here
            while (actionsWrap.firstChild) div.appendChild(actionsWrap.firstChild);
            if (window.lucide) lucide.createIcons();
            return div;
        }

        function bubbleHtml(msg, myId) {
            if (msg.is_system) {
                return `<div class="text-center" data-msg-id="${msg.id}">
                    <span class="text-muted text-xs" style="display:inline-block;padding:0.35rem 0.75rem;border-radius:999px;background:var(--color-card);"></span>
                </div>`;
            }
            const mine = String(msg.sender_id) === String(myId);
            return `<div class="flex ${mine ? 'justify-end' : 'justify-start'}" data-msg-id="${msg.id}">
                <div class="${mine ? 'btn-primary' : 'bg-card'}" style="max-width:75%;padding:0.6rem 0.9rem;border-radius:1rem;">
                    <p class="text-sm"></p>
                    <p class="text-xs mt-1" style="opacity:0.65;">${msg.created_at}</p>
                </div>
            </div>`;
        }

        function renderBubble(msg, myId, container) {
            if (msg.message_type === 'meetup_proposal') {
                container.appendChild(meetupCardHtml(msg, myId));
                return;
            }
            const wrapper = document.createElement('div');
            wrapper.innerHTML = bubbleHtml(msg, myId);
            const node = wrapper.firstElementChild;
            const bubbleDiv = msg.is_system ? null : node.querySelector('div > div');
            if (!msg.is_system && msg.message_type === 'image' && msg.meta && msg.meta.url && bubbleDiv) {
                const img = document.createElement('img');
                img.src = msg.meta.url;
                img.style.cssText = 'max-width:100%;border-radius:0.5rem;margin-bottom:' + (msg.body ? '0.4rem' : '0');
                bubbleDiv.insertBefore(img, bubbleDiv.firstChild);
            }
            const textEl = msg.is_system ? node.querySelector('span') : node.querySelector('p.text-sm');
            if (textEl) textEl.textContent = msg.body || ''; // textContent — avoids XSS from message body
            container.appendChild(node);
        }

        function pollThread(container, otherUserId, myId) {
            const seenIds = new Set(
                Array.from(container.querySelectorAll('[data-msg-id]')).map(el => el.dataset.msgId)
            );
            let url = `/marketplace/${listingId}/messages`;
            if (otherUserId) url += `?other_user_id=${otherUserId}`;

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : [])
                .then(messages => {
                    let appended = false;
                    messages.forEach(msg => {
                        if (!seenIds.has(String(msg.id))) {
                            renderBubble(msg, myId, container);
                            seenIds.add(String(msg.id));
                            appended = true;
                        }
                    });
                    if (appended) {
                        const emptyState = document.getElementById('chatEmptyState');
                        if (emptyState) emptyState.remove();
                        container.scrollTop = container.scrollHeight;
                    }
                })
                .catch(() => {}); // silent — polling failures shouldn't interrupt the page
        }

        document.querySelectorAll('.chat-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                const form = this.closest('div').nextElementSibling;
                if (!form || !form.classList.contains('chat-form')) return;
                const input = form.querySelector('input[name="body"]');
                input.value = this.dataset.chipText;
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        });

        document.querySelectorAll('.chat-form').forEach(form => {
            const myId = form.dataset.myId;
            const otherUserId = form.dataset.otherUserId || null;
            const container = otherUserId
                ? document.getElementById('chatMessages-' + otherUserId)
                : document.getElementById('chatMessages');

            if (!container) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const input = form.querySelector('input[name="body"]');
                const attachmentInput = form.querySelector('.chat-attachment-input');
                const body = input.value.trim();
                const hasFile = attachmentInput && attachmentInput.files.length > 0;
                if (!body && !hasFile) return;

                const formData = new FormData();
                if (body) formData.append('body', body);
                if (hasFile) formData.append('attachment', attachmentInput.files[0]);
                if (otherUserId) formData.append('recipient_id', otherUserId);

                fetch(`/marketplace/${listingId}/message`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(msg => {
                    const emptyState = document.getElementById('chatEmptyState');
                    if (emptyState) emptyState.remove();
                    renderBubble(msg, myId, container);
                    container.scrollTop = container.scrollHeight;
                    input.value = '';
                    if (attachmentInput) attachmentInput.value = '';
                })
                .catch(() => { input.value = body; }); // keep text if send failed
            });

            setInterval(() => pollThread(container, otherUserId, myId), 4000);
        });

        // ===== Meetup proposal wiring =====
        function sendMeetupProposal(listingId, when, where, recipientId, onDone) {
            const formData = new FormData();
            formData.append('when', when);
            formData.append('where', where);
            if (recipientId) formData.append('recipient_id', recipientId);
            fetch(`/marketplace/${listingId}/meetup-propose`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            })
            .then(r => r.json())
            .then(msg => { if (onDone) onDone(msg); })
            .catch(() => {});
        }

        const proposeBtn = document.getElementById('proposeMeetupBtn');
        const proposeForm = document.getElementById('meetupProposeForm');
        if (proposeBtn && proposeForm) {
            proposeBtn.addEventListener('click', () => {
                proposeForm.style.display = proposeForm.style.display === 'none' ? 'flex' : 'none';
            });
            document.getElementById('meetupSubmitBtn')?.addEventListener('click', () => {
                const when = document.getElementById('meetupWhen').value.trim();
                const where = document.getElementById('meetupWhere').value.trim();
                if (!when || !where) return;
                const container = document.getElementById('chatMessages');
                sendMeetupProposal(listingId, when, where, null, (msg) => {
                    const emptyState = document.getElementById('chatEmptyState');
                    if (emptyState) emptyState.remove();
                    if (container) { renderBubble(msg, {{ auth()->id() ?? 'null' }}, container); container.scrollTop = container.scrollHeight; }
                    proposeForm.style.display = 'none';
                    document.getElementById('meetupWhen').value = '';
                    document.getElementById('meetupWhere').value = '';
                });
            });
        }

        document.querySelectorAll('.seller-propose-meetup-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const when = prompt('When? (e.g. Tomorrow 3pm)');
                if (!when) return;
                const where = prompt('Where? (e.g. Main gate)');
                if (!where) return;
                const otherUserId = btn.dataset.otherUserId;
                const container = document.getElementById('chatMessages-' + otherUserId);
                sendMeetupProposal(listingId, when, where, otherUserId, (msg) => {
                    if (container) { renderBubble(msg, {{ auth()->id() ?? 'null' }}, container); container.scrollTop = container.scrollHeight; }
                });
            });
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.meetup-respond-btn');
            if (!btn) return;
            const msgId = btn.dataset.msgId;
            const action = btn.dataset.action;
            fetch(`/marketplace/${listingId}/meetup-respond/${msgId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ action })
            })
            .then(r => r.json())
            .then(() => {
                const card = document.querySelector(`.meetup-card[data-msg-id="${msgId}"]`);
                if (!card) return;
                const actionsDiv = card.querySelector('.flex.gap-2.mt-2, .text-xs.mt-2, .text-xs.mt-1');
                if (actionsDiv) {
                    if (action === 'accept') {
                        card.innerHTML = card.querySelector('p.font-medium').outerHTML + card.querySelector('p.text-muted').outerHTML + '<p class="text-accent text-xs mt-2 font-medium">✅ Accepted</p>';
                    } else {
                        card.innerHTML = card.querySelector('p.font-medium').outerHTML + card.querySelector('p.text-muted').outerHTML + '<p class="text-xs mt-2" style="color:#ef4444;">❌ Declined</p>';
                    }
                }
            })
            .catch(() => {});
        });
    })();
    </script>
    @endif
    @endauth

    <!-- Reviews -->
    <div class="glass-card p-6 mt-8 reveal-on-scroll">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold">Reviews {{ $reviews->count() ? '(' . $reviews->count() . ')' : '' }}</h2>
            @if($avgRating)
            <span class="flex items-center gap-1 text-sm"><i data-lucide="star" class="w-4 h-4 text-amber-500"></i> {{ number_format($avgRating, 1) }} average</span>
            @endif
        </div>

        @auth
        <form method="POST" action="{{ route('marketplace.review', $listing->id) }}" class="mb-6 space-y-2">
            @csrf
            <select name="rating" required class="input-field !mt-0">
                <option value="">Rate this listing</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Okay</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Bad, report an issue</option>
            </select>
            <textarea name="comment" rows="2" placeholder="Share your experience or report a problem with this listing..." class="input-field !mt-0"></textarea>
            <button type="submit" class="btn-secondary text-sm">Submit Review</button>
        </form>
        @else
        <p class="text-muted text-sm mb-6">
            <a href="{{ route('login') }}" class="text-accent">Log in</a> to leave a review.
        </p>
        @endauth

        @forelse($reviews as $review)
        <div class="tx-table-row" style="display:block;">
            <div class="flex justify-between items-start">
                <p class="font-medium text-sm">{{ $review->reviewer->name ?? 'User' }}</p>
                <span class="text-xs text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
            </div>
            @if($review->comment)
            <p class="text-muted text-sm mt-1">{{ $review->comment }}</p>
            @endif
            <p class="text-muted text-xs mt-1">{{ $review->created_at->diffForHumans() }}</p>
        </div>
        @empty
        <p class="text-muted text-sm">No reviews yet — be the first to share your experience.</p>
        @endforelse
    </div>
</main>
@endsection
