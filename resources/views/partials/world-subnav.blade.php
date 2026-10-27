@php
  $isM = ($world ?? 'market') === 'market';
  $cartN = count(session('cart', []));
  $items = $isM
    ? [['shop', 'Shop', route('marketplace.index')], ['saved', 'Saved', route('marketplace.favorites')], ['cart', 'Cart' . ($cartN ? ' (' . $cartN . ')' : ''), route('cart.index')], ['orders', 'Orders', route('marketplace.orders')]]
    : [['jobs', 'Hustles', route('gigs.index')], ['posts', 'My posts', route('posts.index')]];
  if ($isM && auth()->user()->planAllows('market_sell')) $items[] = ['sell', 'Sell', route('marketplace.create')];
  if (!$isM && auth()->user()->planAllows('gigs_post')) $items[] = ['post', 'Post', route('gigs.create')];
@endphp
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}">
<link rel="stylesheet" href="{{ asset('css/world-page.css') }}?v={{ @filemtime(public_path('css/world-page.css')) }}">
<div class="wpn {{ $isM ? 'wp-market' : 'wp-gigs' }}">
  @include('partials.world-switcher', ['world' => $isM ? 'market' : 'gigs'])
  <div class="wp-chips">
    @foreach($items as $it)
      <a href="{{ $it[2] }}" class="wp-chip {{ ($active ?? '') === $it[0] ? 'on' : '' }}">{{ $it[1] }}</a>
    @endforeach
  </div>
</div>
