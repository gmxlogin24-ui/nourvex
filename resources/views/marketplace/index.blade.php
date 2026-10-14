@extends('layouts.app-authenticated')
@section('title', 'Campus Market')

@section('content')
@php
  $types = ['item' => 'Items', 'service' => 'Services', 'rent' => 'Rentals', 'store' => 'Stores'];
  $cats = ['Fashion' => 'shirt', 'Electronics' => 'cpu', 'Books' => 'book', 'Food' => 'utensils', 'Services' => 'wrench', 'Rentals' => 'key', 'Other' => 'grid'];
  $type = request('type'); $cat = request('category');
  $u = function ($extra = []) {
      $p = array_merge(request()->only(['q', 'type', 'category']), $extra);
      return route('marketplace.index', array_filter($p, fn ($v) => $v !== null && $v !== ''));
  };
  $imgOf = function ($l) {
      $url = $l->image_url ?? null;
      if (!$url) {
          $imgs = $l->images ?? null;
          $first = ($imgs && method_exists($imgs, 'first')) ? $imgs->first() : null;
          $url = $first ? ($first->url ?? $first->path ?? $first->image_path ?? $first->image_url ?? null) : null;
      }
      if (!$url) return null;
      return preg_match('~^(https?:)?//~', $url) ? $url : asset('storage/' . ltrim($url, '/'));
  };
@endphp
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}">

<div class="wp wp-market">
  <div class="wp-top">
    <div>
      <h1>Campus Market</h1>
      <p class="wp-sub">{{ count($listings) }} listings</p>
    </div>
    <div class="wp-acts">
      <a href="{{ route('cart.index') }}" class="wp-ico" title="Cart"><i data-lucide="shopping-cart" style="width:17px;height:17px"></i></a>
      @if(auth()->user()->planAllows('market_sell'))
        <a href="{{ route('marketplace.create') }}" class="wp-btn"><i data-lucide="plus" style="width:15px;height:15px"></i>Sell</a>
      @endif
    </div>
  </div>

  <form method="GET" action="{{ route('marketplace.index') }}" class="wp-search">
    <i data-lucide="search"></i>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search listings...">
    @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
    @if($cat)<input type="hidden" name="category" value="{{ $cat }}">@endif
  </form>

  <div class="wp-chips">
    <a href="{{ $u(['type' => null]) }}" class="wp-chip {{ !$type ? 'on' : '' }}">All</a>
    @foreach($types as $val => $label)
      <a href="{{ $u(['type' => $val]) }}" class="wp-chip {{ $type === $val ? 'on' : '' }}">{{ $label }}</a>
    @endforeach
    <span class="wp-sep"></span>
    <a href="{{ route('marketplace.favorites') }}" class="wp-chip">Saved</a>
    <a href="{{ route('marketplace.orders') }}" class="wp-chip">Orders</a>
  </div>
  <div class="wp-chips">
    <a href="{{ $u(['category' => null]) }}" class="wp-chip {{ !$cat ? 'on' : '' }}">All categories</a>
    @foreach($cats as $name => $icon)
      <a href="{{ $u(['category' => $name]) }}" class="wp-chip {{ $cat === $name ? 'on' : '' }}">{{ $name }}</a>
    @endforeach
  </div>

  <div class="mk-grid">
    @forelse($listings as $item)
      @php $img = $imgOf($item); @endphp
      <a href="{{ route('marketplace.show', $item->id) }}" class="mc">
        <span class="mc-i">
          @if($img)
            <img src="{{ $img }}" alt="" loading="lazy">
          @else
            <i data-lucide="{{ $cats[$item->category] ?? 'shopping-bag' }}" style="width:26px;height:26px"></i>
          @endif
          @if(!empty($item->is_verified_seller))<span class="mc-v">VERIFIED</span>@endif
        </span>
        <span class="mc-b">
          <span class="mc-p">₦{{ number_format($item->amount) }}</span>
          <span class="mc-t">{{ $item->title }}</span>
          @if(!empty($item->location))<span class="mc-l">{{ $item->location }}</span>@endif
        </span>
      </a>
    @empty
      <div class="wp-empty"><b>Nothing listed here yet</b>Try another category, or be the first to sell.</div>
    @endforelse
  </div>
</div>
@endsection

@section('scripts')
<script>
if (window.gsap) {
  gsap.from(gsap.utils.toArray('.mc').slice(0, 30), { y: 14, opacity: 0, duration: .45, stagger: .03, ease: 'power2.out', clearProps: 'all' });
}
</script>
@endsection
