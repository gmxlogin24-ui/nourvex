@extends('layouts.app-authenticated')
@section('content')
<main class="max-w-2xl mx-auto p-6">
    <a href="{{ route('gigs.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Gigs
    </a>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
    <p class="text-red-500 text-sm mb-4">{{ $errors->first() }}</p>
    @endif

    <div class="glass-card p-8 reveal-on-scroll">
        <div class="flex items-center justify-between mb-4">
            <div class="icon-badge"><i data-lucide="briefcase" class="w-6 h-6"></i></div>
            <span class="tx-status {{ $gig->status === 'open' ? 'done' : 'pending' }}">
                {{ ['open' => 'Open', 'in_progress' => 'In Progress', 'delivered' => 'Delivered', 'awaiting_worker_signup' => 'Payment Held', 'completed' => 'Completed'][$gig->status] ?? $gig->status }}
            </span>
        </div>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ $gig->title }}</h1>
                <p class="text-muted text-sm mt-1">{{ $gig->category }} · Posted {{ $gig->created_at->diffForHumans() }}</p>
            </div>
            @auth
            @if($gig->user_id === auth()->id() && $gig->status === 'open')
            <a href="{{ route('gigs.edit', $gig->id) }}" class="btn-secondary text-sm" style="white-space:nowrap;"><i data-lucide="pencil" class="w-4 h-4"></i> Edit</a>
            @endif
            @endauth
        </div>
        <p class="text-3xl font-bold text-accent mt-4">₦{{ number_format($gig->amount) }}</p>
        <p class="mt-6 text-sm">{{ $gig->description }}</p>
    </div>

    @if($gig->deadline || $gig->location_type || $gig->location || $gig->phone || $gig->attachment_path || !empty($gig->details) || $gig->has_item || !empty($gig->packages))
    <div class="glass-card p-5 mt-6 reveal-on-scroll">
        <h2 class="font-semibold mb-3">Job Details</h2>
        <div class="space-y-2 text-sm">
            @if(!empty($gig->packages))
            <div>
                <span class="text-muted">Packages:</span>
                <ul class="mt-1 space-y-1">
                    @foreach($gig->packages as $pkg)
                    <li>
                        {{ $pkg['name'] }} — ₦{{ number_format($pkg['price']) }}
                        @if($gig->chosen_package === $pkg['name'])<span class="text-accent">(chosen)</span>@endif
                        @if(!empty($pkg['description']))<span class="text-muted"> — {{ $pkg['description'] }}</span>@endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if($gig->has_item)
            <p><span class="text-muted">Item:</span> {{ $gig->item_name }}{{ $gig->item_qty ? ' × ' . $gig->item_qty : '' }}</p>
            @if($gig->item_cost)
            <p><span class="text-muted">Item cost:</span> ₦{{ number_format($gig->item_cost) }} (not included in gig escrow — arrange directly)</p>
            @endif
            @if($gig->delivery_address)
            <p><span class="text-muted">Delivery address:</span> {{ $gig->delivery_address }}</p>
            @endif
            @if($gig->item_delivery_method)
            <p><span class="text-muted">Delivery method:</span> {{ $gig->item_delivery_method === 'ship' ? 'Ship / send' : 'Meet on campus' }}</p>
            @endif
            @endif
            @if($gig->deadline)
            <p><span class="text-muted">Deadline:</span> {{ $gig->deadline->format('M j, Y') }}</p>
            @endif
            @if($gig->location_type)
            <p><span class="text-muted">Location type:</span> {{ ucfirst($gig->location_type) }}</p>
            @endif
            @if($gig->location)
            <p><span class="text-muted">Location:</span> {{ $gig->location }}</p>
            @endif
            @if($gig->phone)
            <p><span class="text-muted">Call:</span> {{ $gig->phone }}</p>
            @endif
            @if(!empty($gig->details))
            @foreach($gig->details as $key => $value)
            @if($value)
            <p><span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</p>
            @endif
            @endforeach
            @endif
            @if($gig->attachment_path)
            <p><a href="/storage/{{ $gig->attachment_path }}" target="_blank" class="text-accent">View attached brief</a></p>
            @endif
            @if($gig->delivery_attachment_path)
            <p><a href="/storage/{{ $gig->delivery_attachment_path }}" target="_blank" class="text-accent">View delivered file</a></p>
            @endif
        </div>
    </div>
    @endif

    @if($poster)
    <div class="glass-card p-5 mt-6 flex items-center gap-3 reveal-on-scroll">
        <div class="w-12 h-12 rounded-full bg-card flex items-center justify-center font-semibold overflow-hidden">
            @if($poster->avatar_url)<img src="{{ $poster->avatar_url }}" class="w-full h-full object-cover">@else{{ strtoupper(substr($poster->name, 0, 1)) }}@endif
        </div>
        <div class="flex-1">
            <p class="font-medium text-sm">Posted by {{ $poster->name }}</p>
            @if($poster->skills)<p class="text-xs text-accent mt-0.5">{{ $poster->skills }}</p>@endif
            @if($poster->is_verified_seller)
            <p class="text-xs text-accent flex items-center gap-1"><i data-lucide="badge-check" class="w-3 h-3"></i> Verified</p>
            @else
            <p class="text-xs text-muted">Student</p>
            @endif
            <p class="text-xs text-muted mt-0.5">{{ $posterCompletedCount }} gig{{ $posterCompletedCount === 1 ? '' : 's' }} completed · Member since {{ $poster->created_at->format('M Y') }}</p>
        </div>
        @if($gig->whatsappLink())
        <a href="{{ $gig->whatsappLink() }}" target="_blank" class="theme-toggle"><i data-lucide="message-circle" class="w-4 h-4"></i></a>
        @endif
    </div>
    @endif

    @if(in_array($gig->status, ['in_progress', 'delivered', 'awaiting_worker_signup', 'completed']) && ($acceptedByUser || $gig->guest_name))
    <div class="glass-card p-5 mt-4 flex items-center gap-3 reveal-on-scroll">
        <div class="w-12 h-12 rounded-full bg-card flex items-center justify-center font-semibold overflow-hidden">
            @if($acceptedByUser && $acceptedByUser->avatar_url)<img src="{{ $acceptedByUser->avatar_url }}" class="w-full h-full object-cover">@else{{ strtoupper(substr($acceptedByUser->name ?? $gig->guest_name, 0, 1)) }}@endif
        </div>
        <div class="flex-1">
            <p class="font-medium text-sm">Claimed by {{ $acceptedByUser->name ?? $gig->guest_name }}
                @if(!$acceptedByUser)<span class="text-xs text-muted">(no account yet)</span>@endif
            </p>
            @if($acceptedByUser)
            <p class="text-xs text-muted">{{ $workerCompletedCount }} gig{{ $workerCompletedCount === 1 ? '' : 's' }} completed</p>
            @else
            <p class="text-xs text-muted">Not verified — hasn't created an account</p>
            @endif
        </div>
    </div>
    @endif

    @if($gig->status === 'delivered' && $gig->delivery_note)
    <div class="glass-card p-5 mt-4 reveal-on-scroll">
        <h2 class="font-semibold mb-2 flex items-center gap-2"><i data-lucide="package-check" class="w-4 h-4"></i> Delivery Note</h2>
        <p class="text-sm text-muted">{{ $gig->delivery_note }}</p>
    </div>
    @endif

    @if($gig->status === 'in_progress' && $gig->rejection_note)
    <div class="glass-card p-5 mt-4 reveal-on-scroll" style="border:1px solid #f59e0b;">
        <h2 class="font-semibold mb-2 flex items-center gap-2"><i data-lucide="rotate-ccw" class="w-4 h-4" style="color:#f59e0b;"></i> Changes Requested</h2>
        <p class="text-sm text-muted">{{ $gig->rejection_note }}</p>
    </div>
    @endif

    {{-- OPEN --}}
    @if($gig->status === 'open')
        @auth
            @if($gig->user_id === auth()->id())
            <p class="text-muted text-sm mt-6 text-center">Your posted gig — waiting for someone to claim it.</p>
            @else
            <form method="POST" action="{{ route('gigs.accept', $gig->id) }}" onsubmit="return confirm('Claim this gig? You won\'t get paid until the poster confirms the delivered work.');">
                @csrf
                @if(!empty($gig->packages))
                <div class="space-y-2 mb-3">
                    <p class="text-sm text-muted">Choose a package:</p>
                    @foreach($gig->packages as $i => $pkg)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="package_index" value="{{ $i }}" required>
                        {{ $pkg['name'] }} — ₦{{ number_format($pkg['price']) }}
                        @if(!empty($pkg['description']))<span class="text-muted">({{ $pkg['description'] }})</span>@endif
                    </label>
                    @endforeach
                </div>
                @endif
                <button type="submit" class="btn-primary mt-6 w-full justify-center">Claim This Gig</button>
            </form>
            @endif
        @else
            @if($guestGigsUsed >= 2)
            <div class="glass-card p-5 mt-6 text-center reveal-on-scroll">
                <p class="text-sm mb-3">You've used your 2 free gigs. Create an account to keep taking gigs.</p>
                <a href="{{ route('register') }}" class="btn-primary w-full justify-center">Create Account</a>
            </div>
            @else
            <div class="glass-card p-5 mt-6 reveal-on-scroll">
                <p class="text-xs text-muted mb-3">Free gig {{ $guestGigsUsed + 1 }} of 2 — no account needed yet. You'll need to create one before payment is released.</p>
                <form method="POST" action="{{ route('gigs.accept', $gig->id) }}">
                    @csrf
                    <input type="text" name="guest_name" required maxlength="255" placeholder="Your name" class="input-field !mt-0">
                    @if(!empty($gig->packages))
                    <div class="space-y-2 my-3">
                        <p class="text-sm text-muted">Choose a package:</p>
                        @foreach($gig->packages as $i => $pkg)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="package_index" value="{{ $i }}" required>
                            {{ $pkg['name'] }} — ₦{{ number_format($pkg['price']) }}
                            @if(!empty($pkg['description']))<span class="text-muted">({{ $pkg['description'] }})</span>@endif
                        </label>
                        @endforeach
                    </div>
                    @endif
                    <button type="submit" class="btn-primary mt-3 w-full justify-center">Claim This Gig</button>
                </form>
            </div>
            @endif
        @endauth
    @endif

    {{-- IN PROGRESS --}}
    @if($gig->status === 'in_progress')
        @auth
            @if($gig->accepted_by === auth()->id())
            <div class="glass-card p-5 mt-6 reveal-on-scroll">
                <h2 class="font-semibold mb-3">Mark as Delivered</h2>
                <form method="POST" action="{{ route('gigs.deliver', $gig->id) }}" enctype="multipart/form-data">
                    @csrf
                    <textarea name="delivery_note" placeholder="Optional note about what you delivered..." class="input-field !mt-0" rows="2"></textarea>
                    <input type="file" name="delivery_attachment" class="input-field !mt-2">
                    <button type="submit" class="btn-primary mt-3 w-full justify-center">Mark as Delivered</button>
                </form>
                <form method="POST" action="{{ route('gigs.withdraw', $gig->id) }}" onsubmit="return confirm('Withdraw from this gig? It will go back to open for someone else to claim.');" class="mt-2">
                    @csrf
                    <button type="submit" class="btn-secondary w-full justify-center text-sm">Withdraw From This Gig</button>
                </form>
            </div>
            @elseif($gig->user_id === auth()->id())
            <p class="text-muted text-sm mt-6 text-center">Work is in progress — payment releases once the worker marks it delivered and you confirm.</p>
            @else
            <p class="text-muted text-sm mt-6 text-center">This gig is already being worked on by someone else.</p>
            @endif
        @else
            @if($isGuestWorker)
            <div class="glass-card p-5 mt-6 reveal-on-scroll">
                <h2 class="font-semibold mb-3">Mark as Delivered</h2>
                <form method="POST" action="{{ route('gigs.deliver', $gig->id) }}" enctype="multipart/form-data">
                    @csrf
                    <textarea name="delivery_note" placeholder="Optional note about what you delivered..." class="input-field !mt-0" rows="2"></textarea>
                    <input type="file" name="delivery_attachment" class="input-field !mt-2">
                    <button type="submit" class="btn-primary mt-3 w-full justify-center">Mark as Delivered</button>
                </form>
                <form method="POST" action="{{ route('gigs.withdraw', $gig->id) }}" onsubmit="return confirm('Withdraw from this gig? It will go back to open for someone else to claim.');" class="mt-2">
                    @csrf
                    <button type="submit" class="btn-secondary w-full justify-center text-sm">Withdraw From This Gig</button>
                </form>
            </div>
            @else
            <p class="text-muted text-sm mt-6 text-center">This gig is already being worked on by someone else.</p>
            @endif
        @endauth
    @endif

    {{-- DELIVERED --}}
    @if($gig->status === 'delivered')
        @auth
            @if($gig->user_id === auth()->id())
            <form method="POST" action="{{ route('gigs.complete', $gig->id) }}" onsubmit="return confirm('Confirm the delivered work is good? This releases ₦{{ number_format($gig->amount) }} to {{ $acceptedByUser->name ?? $gig->guest_name ?? 'the worker' }} immediately and cannot be undone.');">
                @csrf
                <button type="submit" class="btn-primary mt-6 w-full justify-center">Confirm & Release Payment</button>
            </form>
            <button type="button" onclick="document.getElementById('rejectBox').classList.toggle('hidden')" class="btn-secondary mt-2 w-full justify-center text-sm">Request Changes Instead</button>
            <div id="rejectBox" class="hidden glass-card p-5 mt-2 reveal-on-scroll">
                <form method="POST" action="{{ route('gigs.reject', $gig->id) }}">
                    @csrf
                    <textarea name="rejection_note" required maxlength="1000" placeholder="What needs to change before you can accept this?" class="input-field !mt-0" rows="3"></textarea>
                    <button type="submit" class="btn-secondary mt-3 w-full justify-center">Send Back for Revision</button>
                </form>
            </div>
            @elseif($gig->accepted_by === auth()->id())
            @if($dispute && $dispute->status === 'open')
            <div class="glass-card p-5 mt-6 text-center reveal-on-scroll" style="border:1px solid #f59e0b;">
                <p class="text-sm">Complaint submitted — admin is reviewing it.</p>
            </div>
            @else
            <p class="text-muted text-sm mt-6 text-center">Delivered — waiting for the poster to confirm and release payment.</p>
            <button type="button" onclick="document.getElementById('disputeBox').classList.toggle('hidden')" class="btn-secondary mt-2 w-full justify-center text-sm">Poster Not Responding? File a Complaint</button>
            <div id="disputeBox" class="hidden glass-card p-5 mt-2 reveal-on-scroll">
                <form method="POST" action="{{ route('gigs.dispute.store', $gig->id) }}">
                    @csrf
                    <button type="submit" class="btn-secondary mt-3 w-full justify-center">Submit Complaint</button>
                </form>
            </div>
            @endif
            @endif
        @else
            @if($isGuestWorker)
            <p class="text-muted text-sm mt-6 text-center">Delivered — waiting for the poster to confirm and release payment.</p>
            @endif
        @endauth
    @endif

    {{-- AWAITING WORKER SIGNUP (guest completed, payment held) --}}
    @if($gig->status === 'awaiting_worker_signup')
        @auth
            @if($gig->user_id === auth()->id())
            <p class="text-muted text-sm mt-6 text-center">Marked complete — payment is held until {{ $gig->guest_name }} creates an account to receive it.</p>
            @endif
        @else
            @if($isGuestWorker)
            <div class="glass-card p-5 mt-6 text-center reveal-on-scroll" style="border:1px solid var(--color-accent);">
                <p class="font-semibold mb-1">₦{{ number_format($gig->amount) }} is waiting for you</p>
                <p class="text-sm text-muted mb-3">Create an account to receive this payment into your wallet.</p>
                <a href="{{ route('register') }}" class="btn-primary w-full justify-center">Create Account & Get Paid</a>
            </div>
            @endif
        @endauth
    @endif

    {{-- COMPLETED --}}
    @if($gig->status === 'completed')
    <p class="text-muted text-sm mt-6 text-center">This gig is complete and paid out.</p>
    @endif

    @auth
        @if($poster && ($gig->accepted_by || $gig->user_id === auth()->id()))
        <div class="glass-card p-5 mt-6 reveal-on-scroll">
            <h2 class="font-semibold mb-3 flex items-center gap-2"><i data-lucide="message-square" class="w-4 h-4"></i> Chat</h2>
            <div id="gigChatMessages" class="space-y-3 mb-4" style="max-height:300px;overflow-y:auto;">
                @forelse($messages as $msg)
                <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}" data-msg-id="{{ $msg->id }}">
                    <div class="{{ $msg->sender_id === auth()->id() ? 'btn-primary' : 'bg-card' }}" style="max-width:75%;padding:0.6rem 0.9rem;border-radius:1rem;">
                        @if($msg->message_type === 'image' && ($msg->meta['url'] ?? null))
                        <img src="{{ $msg->meta['url'] }}" style="max-width:100%;border-radius:0.5rem;margin-bottom:{{ $msg->body ? '0.4rem' : '0' }};">
                        @endif
                        @if($msg->body)<p class="text-sm">{{ $msg->body }}</p>@endif
                        <p class="text-xs mt-1" style="opacity:0.65;">{{ $msg->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-muted text-sm" id="gigChatEmptyState">No messages yet — say hello.</p>
                @endforelse
            </div>
            @if($gig->accepted_by || $gig->user_id === auth()->id())
            <form id="gigChatForm" method="POST" action="{{ route('gigs.message', $gig->id) }}" enctype="multipart/form-data" class="flex gap-2" data-gig-id="{{ $gig->id }}" data-my-id="{{ auth()->id() }}">
                @csrf
                <label class="btn-secondary" style="padding:0.6rem 0.75rem;cursor:pointer;">
                    <i data-lucide="paperclip" class="w-4 h-4"></i>
                    <input type="file" name="attachment" accept="image/*" style="display:none;" id="gigChatAttachment">
                </label>
                <input type="text" name="body" placeholder="Type a message..." class="input-field !mt-0 flex-1">
                <button type="submit" class="btn-primary" style="padding:0.6rem 1rem;"><i data-lucide="send" class="w-4 h-4"></i></button>
            </form>
            <script>
            (function() {
                const form = document.getElementById('gigChatForm');
                if (!form) return;
                const gigId = form.dataset.gigId;
                const myId = form.dataset.myId;
                const container = document.getElementById('gigChatMessages');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                function renderBubble(msg) {
                    const mine = String(msg.sender_id) === String(myId);
                    const wrap = document.createElement('div');
                    wrap.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
                    wrap.dataset.msgId = msg.id;
                    const bubble = document.createElement('div');
                    bubble.className = mine ? 'btn-primary' : 'bg-card';
                    bubble.style.cssText = 'max-width:75%;padding:0.6rem 0.9rem;border-radius:1rem;';
                    if (msg.message_type === 'image' && msg.meta && msg.meta.url) {
                        const img = document.createElement('img');
                        img.src = msg.meta.url;
                        img.style.cssText = 'max-width:100%;border-radius:0.5rem;margin-bottom:' + (msg.body ? '0.4rem' : '0');
                        bubble.appendChild(img);
                    }
                    if (msg.body) {
                        const p = document.createElement('p');
                        p.className = 'text-sm';
                        p.textContent = msg.body;
                        bubble.appendChild(p);
                    }
                    const time = document.createElement('p');
                    time.className = 'text-xs mt-1';
                    time.style.opacity = '0.65';
                    time.textContent = msg.created_at;
                    bubble.appendChild(time);
                    wrap.appendChild(bubble);
                    container.appendChild(wrap);
                }

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    fetch(`/gigs/${gigId}/message`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    })
                    .then(r => r.json())
                    .then(msg => {
                        const empty = document.getElementById('gigChatEmptyState');
                        if (empty) empty.remove();
                        renderBubble(msg);
                        container.scrollTop = container.scrollHeight;
                        form.reset();
                    })
                    .catch(() => {});
                });

                function poll() {
                    const seenIds = new Set(Array.from(container.querySelectorAll('[data-msg-id]')).map(el => el.dataset.msgId));
                    fetch(`/gigs/${gigId}/messages`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.ok ? r.json() : [])
                        .then(messages => {
                            let appended = false;
                            messages.forEach(msg => {
                                if (!seenIds.has(String(msg.id))) {
                                    renderBubble(msg);
                                    seenIds.add(String(msg.id));
                                    appended = true;
                                }
                            });
                            if (appended) container.scrollTop = container.scrollHeight;
                        })
                        .catch(() => {});
                }
                setInterval(poll, 4000);
            })();
            </script>
            @endif
        </div>
        @elseif($gig->guest_name && ($gig->user_id === auth()->id()))
        <p class="text-muted text-xs mt-4 text-center">Chat opens once {{ $gig->guest_name }} creates an account.</p>
        @endif

        @if($gig->status === 'completed' && ($gig->user_id === auth()->id() || $gig->accepted_by === auth()->id()))
            @php
                $otherPartyId = auth()->id() === $gig->user_id ? $gig->accepted_by : $gig->user_id;
                $alreadyReviewed = $gig->reviews()->where('reviewer_id', auth()->id())->exists();
            @endphp
            @if($otherPartyId && !$alreadyReviewed)
            <div class="glass-card p-5 mt-6 reveal-on-scroll">
                <h2 class="font-semibold mb-3">Leave a Review</h2>
                <form method="POST" action="{{ route('gigs.review', $gig->id) }}">
                    @csrf
                    <div class="flex gap-1 mb-3" id="starPicker">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-btn" data-val="{{ $i }}" style="font-size:1.4rem;color:var(--color-muted);background:none;">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="">
                    <textarea name="comment" placeholder="Optional comment..." class="input-field !mt-0" rows="2"></textarea>
                    <button type="submit" class="btn-primary mt-3 w-full justify-center">Submit Review</button>
                </form>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const stars = document.querySelectorAll('#starPicker .star-btn');
                const input = document.getElementById('ratingInput');
                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        const val = parseInt(star.dataset.val);
                        input.value = val;
                        stars.forEach(s => s.style.color = parseInt(s.dataset.val) <= val ? '#fbbf24' : 'var(--color-muted)');
                    });
                });
            });
            </script>
            @elseif($alreadyReviewed)
            <p class="text-muted text-xs mt-4 text-center">You've already reviewed this gig.</p>
            @endif
        @endif
    @endauth
</main>
@endsection
