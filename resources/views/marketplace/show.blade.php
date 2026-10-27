@extends('layouts.world-shell')
@section('world', 'market')
@section('content')
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}"><link rel="stylesheet" href="{{ asset('css/world-page.css') }}?v={{ @filemtime(public_path('css/world-page.css')) }}">
<link rel="stylesheet" href="{{ asset('css/marketplace-neu.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/marketplace-detail.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/world-product.css') }}?v={{ @filemtime(public_path('css/world-product.css')) }}">
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/mk-pd.css') }}?v={{ time() }}">

<main class="wp-page wp-market wp-product">
@php
  $pdGallery = $listing->images->pluck('image_url')->filter()->values()->all();
  if (!$pdGallery && $listing->image_url) { $pdGallery = [$listing->image_url]; }
  $pdVerified = (bool) ($seller && $seller->is_verified_seller);
  $pdDesc = trim((string) $listing->description);
  $pdSellerName = $seller ? ($seller->username ? '@'.$seller->username : ($seller->business_name ?? $seller->name)) : $listing->seller_name;
  $pdStock = $listing->stock_quantity;
  $pdTypes = ['item' => 'Item', 'service' => 'Service', 'rent' => 'For rent', 'store' => 'Store'];
  $pdLines = fn ($v) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $v))));
  $pdHigh = $pdLines($listing->highlights);
  $pdBox = $pdLines($listing->whats_in_box);
  $pdAttrs = $listing->getAttributes();
  $pdNeg = array_key_exists('negotiable', $pdAttrs) ? (bool) $pdAttrs['negotiable'] : null;
  $pdWord = fn ($v) => $v === null ? 'New seller' : ($v >= 4.5 ? 'Excellent' : ($v >= 3.5 ? 'Good' : 'Average'));
  $pdDisp = (int) ($sellerDisputes ?? 0);
  $pdSold = (int) $sellerStats['sold'];
  $pdCompl = $pdSold === 0 ? 'New seller' : ($pdDisp === 0 ? 'Excellent' : (($pdDisp / $pdSold) <= 0.1 ? 'Good' : 'Average'));
  $pdShip = $listing->delivery_method === 'ship';
  $pdFew = $pdStock !== null && $pdStock > 0 && $pdStock <= 5;
@endphp
<div class="pd pdw">
  <section class="pd-s pd-s0">
    <div class="pd-stage">
      <a href="{{ route('marketplace.index') }}" class="pd-bk" aria-label="Back"><i data-lucide="arrow-left"></i></a>
      @auth
        <button type="button" class="pd-heart {{ $isLoved ? 'on' : '' }}" data-url="{{ route('marketplace.love', $listing->id) }}" data-token="{{ csrf_token() }}" aria-label="Save and add to cart"><i data-lucide="heart"></i></button>
      @endauth
      @if(count($pdGallery))
        <div class="pd-slides" id="pdSlides">@foreach($pdGallery as $pdG)<div class="pd-slide"><img src="{{ $pdG }}" alt="{{ $listing->title }}"></div>@endforeach</div>
      @else
        <div class="pd-ph"><i data-lucide="image"></i></div>
      @endif
      @if($pdVerified)<span class="pd-ver" title="Verified seller" aria-label="Verified seller"><i data-lucide="badge-check"></i></span>@endif
    </div>
    @if(count($pdGallery) > 1)
      <div class="pd-dots" id="pdDots">@foreach($pdGallery as $pdI => $pdG)<i class="{{ $pdI === 0 ? 'on' : '' }}"></i>@endforeach</div>
      <div class="pd-thumbs">@foreach($pdGallery as $pdI => $pdG)<button type="button" data-i="{{ $pdI }}" class="pd-th {{ $pdI === 0 ? 'on' : '' }}"><img src="{{ $pdG }}" alt=""></button>@endforeach</div>
    @endif
  </section>

  <section class="pd-s">
    <div class="pd-tags">
      <span class="pd-tag">{{ $pdShip ? 'Seller ships' : 'Meetup' }}</span>
      @if($pdVerified)<span class="pd-tag">Verified seller</span>@endif
      @if($pdNeg)<span class="pd-tag">Negotiable</span>@endif
    </div>
    @if($listing->store_name)<p class="pd-store">{{ $listing->store_name }}</p>@endif
    <h1 class="pd-title">{{ $listing->title }}</h1>
    <div class="pd-pr"><b>₦{{ number_format($listing->amount) }}</b></div>
    @if($pdStock !== null && $pdStock <= 0)<p class="pd-few">Out of stock</p>@elseif($pdFew)<p class="pd-few">Few units left ({{ $pdStock }})</p>@elseif($pdStock !== null)<p class="pd-okt">{{ $pdStock }} in stock</p>@endif
    <p class="pd-ship"><i data-lucide="map-pin"></i>{{ $listing->location }} · {{ $pdShip ? 'The seller delivers' : 'Meet the seller' }}</p>
    <div class="pd-meta">
      <a href="#pdReviews" class="pd-rl">
        @if($reviews->count())
          <span class="pd-st5">{{ str_repeat('★', (int) round($avgRating)) }}{{ str_repeat('☆', 5 - (int) round($avgRating)) }}</span> <span>({{ $reviews->count() }} {{ $reviews->count() === 1 ? 'rating' : 'ratings' }})</span>
        @else
          <span>No ratings yet</span>
        @endif
      </a>
      @if($soldCount > 0)<span class="pd-dotsep">{{ $soldCount }} sold</span>@endif
      <button type="button" class="pd-share" id="pdShare" data-title="{{ $listing->title }}" aria-label="Share"><i data-lucide="share-2"></i></button>
    </div>
    <div class="pd-chips">
      @if($listing->brand)<span><i data-lucide="award"></i>{{ $listing->brand }}</span>@endif
      @if($listing->condition)<span><i data-lucide="tag"></i>{{ $listing->condition }}</span>@endif
      <span><i data-lucide="layers"></i>{{ $pdTypes[$listing->listing_type] ?? ucfirst($listing->listing_type) }}</span>
      <span><i data-lucide="folder"></i>{{ $listing->category }}</span>
    </div>
    @if ($errors->any())<p class="pd-note err">{{ $errors->first() }}</p>@endif
    @if(session('status'))<p class="pd-note">{{ session('status') }}</p>@endif
  </section>

  @auth
    @if($listing->status === 'sold' && $listing->buyer_id === auth()->id())
      <section class="pd-s pd-state">
        @if($pdShip)
          <div class="pd-dr">
            <i data-lucide="{{ $listing->shipped_at ? 'truck' : 'clock' }}"></i>
            <div>
              @if($listing->shipped_at)
                <b>Shipped {{ $listing->shipped_at->diffForHumans() }}</b>
                @if($listing->shipping_note)<p>{{ $listing->shipping_note }}</p>@endif
              @else
                <b>Waiting for the seller</b><p>The seller hasn't marked this as shipped yet.</p>
              @endif
            </div>
          </div>
        @endif
        <form method="POST" action="{{ route('marketplace.confirm', $listing->id) }}">
          @csrf
          <button type="submit" class="btn-primary pd-buy"><i data-lucide="check-circle"></i> Confirm Receipt — Release Payment</button>
        </form>
        <p class="pd-note">Funds are held in escrow until you confirm you received this.</p>
        @if(!$listing->dispute || $listing->dispute->status !== 'open')
          <details class="pd-dispute">
            <summary>Something wrong? File a complaint</summary>
            <form method="POST" action="{{ route('marketplace.dispute', $listing->id) }}">
              @csrf
              <textarea name="description" required rows="3" placeholder="What went wrong?"></textarea>
              <button type="submit" class="btn-secondary pd-buy">Submit Complaint</button>
            </form>
          </details>
        @else
          <p class="pd-note">Complaint submitted — admin is reviewing.</p>
        @endif
      </section>
    @elseif($listing->status === 'sold' && $listing->user_id === auth()->id())
      <section class="pd-s pd-state">
        @if($pdShip)
          <p class="pd-sn">This item sold — arrange shipping</p>
          @if($listing->shipped_at)
            <p class="pd-ss">Marked shipped {{ $listing->shipped_at->diffForHumans() }}</p>
          @else
            <form method="POST" action="{{ route('marketplace.ship', $listing->id) }}">
              @csrf
              <textarea name="shipping_note" rows="2" placeholder="Courier / tracking info (optional)" style="width:100%;margin:8px 0;padding:10px 12px;border-radius:14px;background:rgba(128,128,128,.12);border:1px solid rgba(128,128,128,.24);color:var(--ink,#0A0A0A);"></textarea>
              <button type="submit" class="btn-primary pd-buy">Mark as Shipped</button>
            </form>
          @endif
        @else
          <p class="pd-note">Sold — awaiting buyer confirmation.</p>
        @endif
      </section>
    @elseif($listing->status === 'completed')
      <section class="pd-s"><p class="pd-note">✓ Purchase completed</p></section>
    @elseif($listing->status !== 'available')
      <section class="pd-s"><p class="pd-note">This listing is no longer available.</p></section>
    @elseif($listing->user_id === auth()->id())
      <div class="pd-bar">
        <button type="button" class="btn-secondary pd-cart" disabled aria-label="Add to cart"><i data-lucide="shopping-cart"></i></button>
        <button type="button" class="btn-primary pd-buy" disabled>Buy Now</button>
      </div>
      <section class="pd-s pd-state"><a href="{{ route('marketplace.edit', $listing->id) }}" class="btn-secondary pd-buy"><i data-lucide="pencil"></i> Edit Listing</a><p class="pd-note">This is your own listing, so it can't be added to your cart or bought. Favoriting it only saves it. Open it from another account to see the buyer view.</p></section>
    @elseif(auth()->user()->planAllows('market_buy'))
      <div class="pd-bar">
        <a href="{{ route('cart.index') }}" class="btn-secondary pd-cartin" id="pdCartIn" {{ $inCart ? '' : 'hidden' }}><i data-lucide="check"></i> In cart</a>
        <form method="POST" action="{{ route('cart.add', $listing->id) }}" id="pdCartForm" {{ $inCart ? 'hidden' : '' }}>
          @csrf
          <button type="submit" class="btn-secondary pd-cart" aria-label="Add to cart"><i data-lucide="shopping-cart"></i></button>
        </form>
        <form method="POST" action="{{ route('marketplace.purchase', $listing->id) }}" style="display:contents;" onsubmit="return confirm('Buy this item for ₦{{ number_format($listing->amount) }}? The money is held in escrow until you confirm delivery.');">
          @csrf
          <button type="submit" class="btn-primary pd-buy">Buy Now</button>
        </form>
      </div>
    @else
      <section class="pd-s pd-state"><a href="{{ route('subscriptions.index') }}" class="btn-primary pd-buy"><i data-lucide="lock"></i> Upgrade to Silver to buy</a></section>
    @endif
  @else
    <section class="pd-s pd-state"><a href="{{ route('register') }}" class="btn-primary pd-buy"><i data-lucide="lock"></i> Sign up to Buy — ₦{{ number_format($listing->amount) }}</a></section>
  @endauth

  @include('marketplace.partials.pd-row', ['title' => 'Similar items', 'items' => $similar])

  <section class="pd-s">
    <h2 class="pd-sec">Buyer protection</h2>
    <div class="pd-dr"><i data-lucide="shield-check"></i><div><b>Pay safely</b><p>Your payment is held in escrow and only released to the seller after you confirm you received the item.</p></div></div>
    <div class="pd-dr"><i data-lucide="life-buoy"></i><div><b>Complaints are reviewed</b><p>If something is wrong, file a complaint after buying and an admin reviews it before the seller is paid.</p></div></div>
    <div class="pd-dr"><i data-lucide="lock"></i><div><b>Stay on Naurvex</b><p>Pay only through Naurvex. Meet in a public place and check the item before you confirm.</p></div></div>
  </section>

  <section class="pd-s">
    <h2 class="pd-sec">Delivery &amp; Returns</h2>
    <div class="pd-dr">
      <i data-lucide="{{ $pdShip ? 'truck' : 'handshake' }}"></i>
      <div>
        <b>{{ $pdShip ? 'Delivered by the seller' : 'Meet up with the seller' }}</b>
        <p>{{ $pdShip ? ($listing->shipping_note ?: 'The seller ships it to you after you pay and adds tracking info when it is on its way.') : 'Chat with the seller to agree a place and time. Confirm receipt only after you have checked the item.' }}</p>
      </div>
    </div>
    <div class="pd-dr"><i data-lucide="map-pin"></i><div><b>Item location</b><p>{{ $listing->location }}</p></div></div>
    <div class="pd-dr"><i data-lucide="undo-2"></i><div><b>Return policy</b><p>{{ $listing->return_policy ?: 'No return policy was set by the seller. If the item is not as described, file a complaint after buying.' }}</p></div></div>
    @if($listing->warranty)<div class="pd-dr"><i data-lucide="award"></i><div><b>Warranty</b><p>{{ $listing->warranty }}</p></div></div>@endif
  </section>

  <section class="pd-s">
    <h2 class="pd-sec">Description</h2>
    @if($pdDesc !== '')
      <p class="pd-desc">{!! nl2br(e($pdDesc)) !!}</p>
    @else
      <p class="pd-muted">The seller hasn't added a description yet. Use the chat to ask them anything about this item.</p>
    @endif
    @if(count($pdHigh))
      <h3 class="pd-h">Highlights</h3>
      <ul class="pd-list">@foreach($pdHigh as $pdL)<li>{{ $pdL }}</li>@endforeach</ul>
    @endif
    @if(count($pdBox))
      <h3 class="pd-h">What's in the box</h3>
      <ul class="pd-list">@foreach($pdBox as $pdL)<li>{{ $pdL }}</li>@endforeach</ul>
    @endif
  </section>

  <section class="pd-s">
    <h2 class="pd-sec">Specifications</h2>
    <dl class="pd-info">
      @if($listing->brand)<div><dt>Brand</dt><dd>{{ $listing->brand }}</dd></div>@endif
      <div><dt>Category</dt><dd>{{ $listing->category }}</dd></div>
      <div><dt>Condition</dt><dd>{{ $listing->condition }}</dd></div>
      <div><dt>Location</dt><dd>{{ $listing->location }}</dd></div>
      <div><dt>Type</dt><dd>{{ $pdTypes[$listing->listing_type] ?? ucfirst($listing->listing_type) }}</dd></div>
      <div><dt>Availability</dt><dd>{{ $pdStock === null ? ucfirst($listing->status) : ($pdStock > 0 ? $pdStock . ' in stock' : 'Out of stock') }}</dd></div>
      <div><dt>Delivery</dt><dd>{{ $pdShip ? 'Seller ships' : 'Meetup' }}</dd></div>
      @if($listing->warranty)<div><dt>Warranty</dt><dd>{{ $listing->warranty }}</dd></div>@endif
      @if($pdNeg !== null)<div><dt>Price</dt><dd>{{ $pdNeg ? 'Negotiable' : 'Fixed' }}</dd></div>@endif
      <div><dt>Listed</dt><dd>{{ $listing->created_at->diffForHumans() }}</dd></div>
      <div><dt>Item ID</dt><dd>#{{ $listing->id }}</dd></div>
    </dl>
  </section>

  @include('marketplace.partials.pd-row', ['title' => 'Customers also viewed', 'items' => $alsoLike])

  <section class="pd-s" id="pdReviews">
    <h2 class="pd-sec">Product Rating &amp; Reviews</h2>
    @if($reviews->count())
      <div class="pd-rs">
        <div class="pd-rs-big"><b>{{ number_format($avgRating, 1) }}</b><span>out of 5</span><small>{{ $reviews->count() }} {{ $reviews->count() === 1 ? 'rating' : 'ratings' }}</small></div>
        <div class="pd-rs-bars">
          @foreach($breakdown as $pdStar => $pdN)
            <div><em>{{ $pdStar }}★</em><i><u style="width:{{ round($pdN / $reviews->count() * 100) }}%"></u></i><small>{{ $pdN }}</small></div>
          @endforeach
        </div>
      </div>
    @endif
    @forelse($reviews as $pdRv)
      <div class="pd-rv">
        <span class="pd-rav">{{ strtoupper(substr(optional($pdRv->reviewer)->name ?? 'B', 0, 1)) }}</span>
        <div>
          <div class="pd-rh"><b>{{ optional($pdRv->reviewer)->username ? '@'.optional($pdRv->reviewer)->username : (optional($pdRv->reviewer)->name ?? 'Buyer') }}</b><span>{{ str_repeat('★', (int) $pdRv->rating) }}{{ str_repeat('☆', 5 - (int) $pdRv->rating) }}</span></div>
          <small>{{ $pdRv->created_at->format('M j, Y') }}@if($listing->buyer_id && $listing->buyer_id === $pdRv->reviewer_id) · Verified purchase @endif</small>
          @if($pdRv->comment)<p>{{ $pdRv->comment }}</p>@endif
        </div>
      </div>
    @empty
      <p class="pd-muted">No reviews yet. Be the first to share your experience.</p>
    @endforelse
    @auth
      @if(!$isOwner)
        <form method="POST" action="{{ route('marketplace.review', $listing->id) }}" class="pd-rf">
          @csrf
          <b>Rate this item</b>
          <div class="pd-stars">
            @for($pdI = 5; $pdI >= 1; $pdI--)<input type="radio" name="rating" id="pdr{{ $pdI }}" value="{{ $pdI }}" required><label for="pdr{{ $pdI }}" aria-label="{{ $pdI }} stars">★</label>@endfor
          </div>
          <textarea name="comment" rows="2" placeholder="Share your experience or report a problem" class="input-field !mt-0"></textarea>
          <button type="submit" class="btn-primary pd-rsub">Submit review</button>
        </form>
      @endif
    @else
      <p class="pd-muted"><a href="{{ route('login') }}">Log in</a> to leave a review.</p>
    @endauth
  </section>

  <section class="pd-s">
    <h2 class="pd-sec">Seller information</h2>
    <div class="pd-sh">
      <span class="pd-sav">@if($seller && $seller->avatar_url)<img src="{{ $seller->avatar_url }}" alt="">@else{{ strtoupper(substr($pdSellerName ?? '?', 0, 1)) }}@endif</span>
      <div>
        <p class="pd-sn">{{ $pdSellerName }}@if($pdVerified)<i data-lucide="badge-check" title="Verified seller"></i>@endif</p>
        @if($seller)<p class="pd-ss">Member since {{ $seller->created_at->format('M Y') }}</p>@endif
      </div>
    </div>
    <div class="pd-st">
      <div><b>{{ $sellerStats['listings'] }}</b>Listings</div>
      <div><b>{{ $pdSold }}</b>Sold</div>
      <div><b>{{ $sellerStats['rating'] ? $sellerStats['rating'] . ' ★' : '—' }}</b>Rating</div>
    </div>
    @if($seller)
    <div class="pd-sbtns">
      <a href="{{ route('profile.public', $seller->id) }}" class="btn-secondary pd-sbtn"><i data-lucide="user"></i> View profile</a>
      @auth @unless($isOwner)<button type="button" class="btn-primary pd-sbtn pd-msg-seller"><i data-lucide="message-circle"></i> Message seller</button>@endunless @endauth
    </div>
    <script>(function(){var b=document.querySelector('.pd-msg-seller');if(!b)return;b.addEventListener('click',function(){var a=document.getElementById('pdAsk');if(a){a.click();return;}var c=document.getElementById('chatMessages');if(c){c.scrollIntoView({behavior:'smooth',block:'center'});var i=document.querySelector('.chat-form input[name="body"]');if(i)i.focus({preventScroll:true});}});})();</script>
    @endif
    <h3 class="pd-h">Seller performance</h3>
    <ul class="pd-perf">
      <li><i data-lucide="check-circle-2"></i><span>Quality score:</span> <b>{{ $pdWord($sellerStats['rating']) }}</b></li>
      <li><i data-lucide="check-circle-2"></i><span>Customer rating:</span> <b>{{ $sellerStats['rating'] ? $sellerStats['rating'] . ' out of 5' : 'No ratings yet' }}</b></li>
      <li><i data-lucide="check-circle-2"></i><span>Order completion:</span> <b>{{ $pdCompl }}</b></li>
      <li><i data-lucide="check-circle-2"></i><span>Items sold:</span> <b>{{ $pdSold }}</b></li>
    </ul>
  </section>

  @include('marketplace.partials.pd-row', ['title' => 'More from this seller', 'items' => $sellerMore])

  @unless($isOwner)
    <section class="pd-s">
      <h2 class="pd-sec">Questions about this product?</h2>
      @auth
        @if($seller)<button type="button" class="btn-secondary pd-ask" id="pdAsk"><i data-lucide="message-square"></i> Chat with seller</button>@endif
      @else
        <a href="{{ route('login') }}" class="btn-secondary pd-ask">Log in to chat with the seller</a>
      @endauth
    </section>
  @endunless

  @include('marketplace.partials.pd-row', ['title' => 'Recently viewed', 'items' => $recentlyViewed])
</div>
<script>
(function () {
  var w = document.createElement('div');
  w.className = 'pd-bgw';
  document.body.appendChild(w);
  document.body.classList.add('pd-white');
  var s = document.getElementById('pdSlides');
  var dots = document.querySelectorAll('#pdDots i');
  var th = document.querySelectorAll('.pd-th');
  if (s) {
    s.addEventListener('scroll', function () {
      var i = Math.round(s.scrollLeft / s.clientWidth);
      dots.forEach(function (d, k) { d.classList.toggle('on', k === i); });
      th.forEach(function (t, k) { t.classList.toggle('on', k === i); });
    }, { passive: true });
    th.forEach(function (t) {
      t.addEventListener('click', function () { s.scrollTo({ left: s.clientWidth * Number(t.dataset.i), behavior: 'smooth' }); });
    });
  }
  document.querySelectorAll('.pd-rl').forEach(function (l) {
    l.addEventListener('click', function (e) {
      e.preventDefault();
      var r = document.getElementById('pdReviews');
      if (r) r.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
  var sh = document.getElementById('pdShare');
  if (sh) {
    sh.addEventListener('click', function () {
      if (navigator.share) {
        navigator.share({ title: sh.dataset.title, url: location.href }).catch(function () {});
      } else if (navigator.clipboard) {
        navigator.clipboard.writeText(location.href).then(function () {
          sh.classList.add('done');
          setTimeout(function () { sh.classList.remove('done'); }, 1500);
        }).catch(function () {});
      }
    });
  }
  var ask = document.getElementById('pdAsk');
  if (ask) {
    ask.addEventListener('click', function () {
      var c = document.getElementById('chatMessages');
      if (!c) return;
      c.scrollIntoView({ behavior: 'smooth', block: 'center' });
      var i = document.querySelector('.chat-form input[name="body"]');
      if (i) i.focus({ preventScroll: true });
    });
  }
  var pb = document.querySelector('.pd-bar');
  if (pb && pb.parentNode !== document.body) { pb.setAttribute('data-pd-bar-move', '1'); document.body.appendChild(pb); }
  if (pb) { document.body.classList.add('pd-has-bar'); }
  var h = document.querySelector('.pd-heart');
  if (h) {
    h.addEventListener('click', function (e) {
      e.preventDefault();
      if (h.dataset.busy) return;
      h.dataset.busy = '1';
      fetch(h.dataset.url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': h.dataset.token, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (typeof d.loved !== 'boolean') return;
          h.classList.toggle('on', d.loved);
          var ci = document.getElementById('pdCartIn'), cf = document.getElementById('pdCartForm');
          if (ci && cf) { ci.hidden = !d.loved; cf.hidden = d.loved; }
          var c = document.querySelector('.pb-ic[aria-label="Cart"]');
          if (c) {
            var em = c.querySelector('.nv-badge');
            if (d.cart_count > 0) {
              if (!em) { em = document.createElement('em'); em.className = 'nv-badge'; c.appendChild(em); }
              em.textContent = d.cart_count;
            } else if (em) { em.remove(); }
          }
        })
        .catch(function () {})
        .then(function () { delete h.dataset.busy; });
    });
  }
  if (window.lucide) lucide.createIcons();
})();
</script>

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
            <p class="text-sm font-medium mb-2">{{ ($otherUser->username ?? null) ? '@'.$otherUser->username : ($otherUser->name ?? 'User') }}</p>
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

    </main>
@endsection
