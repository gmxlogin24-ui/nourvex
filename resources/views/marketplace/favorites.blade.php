@extends('layouts.world-shell')
@section('world', 'market')
@section('title', 'Saved')
@section('content')
@php $spInCart = session('cart', []); @endphp
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<main class="sp">
  <section class="sp-s sp-s0">
    <div class="sp-head">
      <a href="{{ route('marketplace.index') }}" class="sp-back" aria-label="Back to market"><i data-lucide="arrow-left"></i></a>
      <h1>Saved</h1>
      <span>{{ count($listings) }} {{ count($listings) === 1 ? 'item' : 'items' }}</span>
    </div>
    @include('marketplace.partials.shop-tabs', ['active' => 'saved'])
  </section>

  @if(count($listings))
    <section class="sp-s">
      <h2 class="sp-h">Your saved items</h2>
      <div class="mk-grid" id="mkGrid" data-token="{{ csrf_token() }}">
        @foreach($listings as $item)
          @php $spImg = $item->image_url ?: optional($item->images->first())->image_url; @endphp
          <div class="mc2">
            <a href="{{ route('marketplace.show', $item->id) }}" class="mc2-link">
              <span class="mc2-i">@if($spImg)<img src="{{ $spImg }}" alt="" loading="lazy">@else<i data-lucide="shopping-bag" style="width:26px;height:26px"></i>@endif</span>
              <span class="mc2-b">
                <span class="mc2-t">{{ $item->title }}</span>
                <span class="mc2-f"><span class="mc2-p">₦{{ number_format($item->amount) }}</span>@if(in_array($item->id, $spInCart, true))<span class="mc2-r">In cart</span>@endif</span>
                @if($item->location)<span class="mc2-l"><i data-lucide="map-pin"></i>{{ $item->location }}</span>@endif
              </span>
            </a>
            <button type="button" class="mc2-heart on" data-url="{{ route('marketplace.love', $item->id) }}" aria-label="Remove from saved"><i data-lucide="heart"></i></button>
          </div>
        @endforeach
      </div>
    </section>
  @else
    <section class="sp-s sp-empty">
      <i data-lucide="heart"></i>
      <h2>Nothing saved yet</h2>
      <p>Tap the heart on any listing to save it here and add it to your cart.</p>
      <a href="{{ route('marketplace.index') }}" class="btn-primary sp-btn">Browse Campus Market</a>
    </section>
  @endif
</main>
<script>
(function () {
  var g = document.getElementById('mkGrid');
  if (!g) return;
  function cart(n) {
    var c = document.querySelector('.pb-ic[aria-label="Cart"]');
    if (!c) return;
    var em = c.querySelector('.nv-badge');
    if (n > 0) {
      if (!em) { em = document.createElement('em'); em.className = 'nv-badge'; c.appendChild(em); }
      em.textContent = n;
    } else if (em) { em.remove(); }
  }
  g.addEventListener('click', function (e) {
    var b = e.target.closest('.mc2-heart');
    if (!b) return;
    e.preventDefault();
    if (b.dataset.busy) return;
    b.dataset.busy = '1';
    fetch(b.dataset.url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': g.dataset.token, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (typeof d.loved !== 'boolean') return;
        b.classList.toggle('on', d.loved);
        b.closest('.mc2').style.opacity = d.loved ? '1' : '.45';
        cart(d.cart_count);
      })
      .catch(function () {})
      .then(function () { delete b.dataset.busy; });
  });
})();
</script>
@include('marketplace.partials.shop-boot')
@endsection
