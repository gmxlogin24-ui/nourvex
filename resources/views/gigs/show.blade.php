@extends('layouts.world-shell')
@section('world', 'gigs')
@section('content')
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}"><link rel="stylesheet" href="{{ asset('css/world-page.css') }}?v={{ @filemtime(public_path('css/world-page.css')) }}">
<main class="wp-page wp-gigs">
    <a href="{{ route('gigs.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Gigs
    </a>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
    <p class="text-red-500 text-sm mb-4">{{ $errors->first() }}</p>
    @endif

    @php
  $gxPk = is_array($gig->packages) ? array_values($gig->packages) : [];
  $gxSt = ['open' => 'Open', 'in_progress' => 'In progress', 'delivered' => 'Delivered', 'awaiting_worker_signup' => 'Payment held', 'completed' => 'Completed'];
  $gxIcons = ['Typing & Printing' => 'printer', 'Hair & Beauty' => 'scissors', 'Repairs' => 'wrench', 'Errands & Delivery' => 'truck', 'Tutoring' => 'graduation-cap', 'Photography' => 'camera', 'Laundry' => 'shirt'];
  $gxRev = collect();
  try { $gxRev = $gig->reviews()->with('reviewer')->latest()->get(); } catch (\Throwable $e) { $gxRev = collect(); }
  $gxAvg = $gxRev->count() ? round($gxRev->avg('rating'), 1) : null;
  $gxLvl = data_get($posterLevel ?? null, 'label');
  $gxOwner = auth()->check() && $gig->user_id === auth()->id();
  $gxFrom = count($gxPk) ? min(array_map(fn ($x) => (float) ($x['price'] ?? 0), $gxPk)) : (float) $gig->amount;
  $gxTier = ['Basic', 'Standard', 'Premium'];
@endphp
<link rel="stylesheet" href="{{ asset('css/gig-show2.css') }}?v={{ time() }}">
<div class="gv">
  <section class="gv-s gv-hero" id="gvHero">
    <div class="gv-top">
      <span class="gv-ic"><i data-lucide="{{ $gxIcons[$gig->category] ?? 'briefcase' }}"></i></span>
      <span class="gv-chip {{ $gig->status === 'open' ? 'ok' : '' }}">{{ $gxSt[$gig->status] ?? $gig->status }}</span>
      @auth
        @if($gxOwner && $gig->status === 'open')
          <a href="{{ route('gigs.edit', $gig->id) }}" class="gv-edit"><i data-lucide="pencil"></i>Edit</a>
        @endif
      @endauth
    </div>
    <h1 class="gv-title">{{ $gig->title }}</h1>
    <div class="gv-meta">
      <span>{{ $gig->category }}</span>
      <span>Posted {{ $gig->created_at->diffForHumans() }}</span>
      @if($gxAvg)<span class="gv-star"><i data-lucide="star"></i>{{ $gxAvg }} ({{ $gxRev->count() }})</span>@endif
      @if($gig->deadline)<span><i data-lucide="calendar"></i>Due {{ $gig->deadline->format('M j') }}</span>@endif
    </div>
  </section>

  <section class="gv-s">
    @if(count($gxPk))
      <div class="gv-tabs" id="gvTabs">
        @foreach($gxPk as $gi => $gp)
          <button type="button" class="gv-tab {{ $gi === 0 ? 'on' : '' }}" data-i="{{ $gi }}">{{ $gp['name'] ?? ($gxTier[$gi] ?? 'Package') }}</button>
        @endforeach
      </div>
      @foreach($gxPk as $gi => $gp)
        <div class="gv-pk" data-i="{{ $gi }}" {{ $gi === 0 ? '' : 'hidden' }}>
          <div class="gv-pp">₦{{ number_format((float) ($gp['price'] ?? 0)) }}</div>
          @if(!empty($gp['description']))<p class="gv-pd">{{ $gp['description'] }}</p>@endif
          @if($gig->chosen_package === ($gp['name'] ?? null))<span class="gv-chip ok">Chosen</span>@endif
        </div>
      @endforeach
      <p class="gv-note"><i data-lucide="shield-check"></i>Pick your package in the claim form below. Payment stays in escrow until the work is approved.</p>
    @else
      <div class="gv-pp">₦{{ number_format((float) $gig->amount) }}</div>
      <p class="gv-note"><i data-lucide="shield-check"></i>Payment stays in escrow until the work is approved.</p>
    @endif
  </section>

  <section class="gv-s">
    <h2 class="gv-h">About this hustle</h2>
    @if(trim((string) $gig->description) !== '')
      <p class="gv-desc">{!! nl2br(e($gig->description)) !!}</p>
    @else
      <p class="gv-muted">The poster has not added a description. Message them to ask what is needed.</p>
    @endif
  </section>

  @if($gig->deadline || $gig->location_type || $gig->location || $gig->phone || $gig->attachment_path || !empty($gig->details) || $gig->has_item)
    <section class="gv-s">
      <h2 class="gv-h">Job details</h2>
      <dl class="gv-dl">
        @if($gig->deadline)<div><dt>Deadline</dt><dd>{{ $gig->deadline->format('M j, Y') }}</dd></div>@endif
        @if($gig->location_type)<div><dt>Where</dt><dd>{{ ucfirst($gig->location_type) }}</dd></div>@endif
        @if($gig->location)<div><dt>Location</dt><dd>{{ $gig->location }}</dd></div>@endif
        @if($gig->phone)<div><dt>Call</dt><dd>{{ $gig->phone }}</dd></div>@endif
        @if(!empty($gig->details))
          @foreach($gig->details as $gk => $gv)
            @if($gv)<div><dt>{{ ucfirst(str_replace('_', ' ', $gk)) }}</dt><dd>{{ $gv }}</dd></div>@endif
          @endforeach
        @endif
        @if($gig->has_item)
          <div><dt>Item</dt><dd>{{ $gig->item_name }}{{ $gig->item_qty ? ' x ' . $gig->item_qty : '' }}</dd></div>
          @if($gig->item_cost)<div><dt>Item cost</dt><dd>₦{{ number_format($gig->item_cost) }} (not in escrow, arrange directly)</dd></div>@endif
          @if($gig->delivery_address)<div><dt>Deliver to</dt><dd>{{ $gig->delivery_address }}</dd></div>@endif
          @if($gig->item_delivery_method)<div><dt>Delivery</dt><dd>{{ $gig->item_delivery_method === 'ship' ? 'Ship / send' : 'Meet on campus' }}</dd></div>@endif
        @endif
      </dl>
      @if($gig->attachment_path)<a href="/storage/{{ $gig->attachment_path }}" target="_blank" class="gv-link"><i data-lucide="paperclip"></i>View attached brief</a>@endif
      @if($gig->delivery_attachment_path)<a href="/storage/{{ $gig->delivery_attachment_path }}" target="_blank" class="gv-link"><i data-lucide="package-check"></i>View delivered file</a>@endif
    </section>
  @endif

  @if($poster)
    <section class="gv-s">
      <h2 class="gv-h">About the poster</h2>
      <div class="gv-sel">
        <span class="gv-av">@if($poster->avatar_url)<img src="{{ $poster->avatar_url }}" alt="">@else{{ strtoupper(substr($poster->name, 0, 1)) }}@endif</span>
        <div class="gv-si">
          <b>{{ $poster->username ? '@'.$poster->username : $poster->name }}@if($poster->is_verified_seller)<i data-lucide="badge-check"></i>@endif</b>
          @if($poster->skills)<small>{{ $poster->skills }}</small>@endif
          <small>Member since {{ $poster->created_at->format('M Y') }}</small>
        </div>
        @if($gig->whatsappLink())<a href="{{ $gig->whatsappLink() }}" target="_blank" class="gv-wa" aria-label="WhatsApp"><i data-lucide="message-circle"></i></a>@endif
      </div>
      <div class="gv-st">
        <div><b>{{ $posterCompletedCount }}</b>Completed</div>
        <div><b>{{ !empty($posterRating) ? $posterRating . ' ★' : '—' }}</b>Rating</div>
        <div><b>{{ $gxLvl ?: 'New' }}</b>Level</div>
      </div>
    </section>
  @endif

  <section class="gv-s">
    <h2 class="gv-h">Reviews</h2>
    @forelse($gxRev->take(5) as $gr)
      <div class="gv-rv">
        <span class="gv-ra">{{ strtoupper(substr(optional($gr->reviewer)->name ?? 'U', 0, 1)) }}</span>
        <div>
          <div class="gv-rh"><b>{{ optional($gr->reviewer)->name ?? 'User' }}</b><span>{{ str_repeat('★', (int) $gr->rating) }}{{ str_repeat('☆', 5 - (int) $gr->rating) }}</span></div>
          <small>{{ $gr->created_at->format('M j, Y') }}</small>
          @if($gr->comment)<p>{{ $gr->comment }}</p>@endif
        </div>
      </div>
    @empty
      <p class="gv-muted">No reviews yet. Reviews appear after the work is completed.</p>
    @endforelse
  </section>
</div>
<script>
(function () {
  var t = document.getElementById('gvTabs'); if (!t) return;
  t.addEventListener('click', function (e) {
    var b = e.target.closest('.gv-tab'); if (!b) return;
    var i = b.getAttribute('data-i');
    [].slice.call(t.querySelectorAll('.gv-tab')).forEach(function (x) { x.classList.toggle('on', x === b); });
    [].slice.call(document.querySelectorAll('.gv-pk')).forEach(function (x) { x.hidden = x.getAttribute('data-i') !== i; });
  });
  if (window.lucide) lucide.createIcons();
})();
</script>

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
