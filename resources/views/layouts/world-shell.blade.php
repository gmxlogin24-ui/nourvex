<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>NAURVEX - @yield('title', 'Market')</title>
  @include('partials.favicons')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#F5F7FA">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/gsap.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('css/world-shell.css') }}?v={{ @filemtime(public_path('css/world-shell.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/profile-hub.css') }}?v={{ @filemtime(public_path('css/profile-hub.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/world-glass.css') }}?v={{ @filemtime(public_path('css/world-glass.css')) }}">
  @stack('styles')
<script>try{if(localStorage.getItem("theme")==="dark")document.documentElement.setAttribute("data-theme","dark");}catch(e){}</script>
<link rel="stylesheet" href="{{ asset("css/app-dark.css") }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/mono.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/buttons-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/readable.css') }}?v={{ time() }}">
</head>
@php
  $mode = trim($__env->yieldContent('world')) ?: 'market';
  $isM = $mode === 'market';
  $isG = $mode === 'gigs';
  $isP = $mode === 'profile';
  $cartN = count(session('cart', []));
  $canPost = auth()->user()->planAllows('gigs_post');

  if ($isM) {
    $nav = [
      ['home', 'Shop', route('marketplace.index'), request()->routeIs('marketplace.index', 'marketplace.show', 'marketplace.edit', 'marketplace.create'), 0],
      ['heart', 'Saved', route('marketplace.favorites'), request()->routeIs('marketplace.favorites'), 0],
      ['shopping-bag', 'Cart', route('cart.index'), request()->routeIs('cart.*'), $cartN],
      ['package', 'Orders', route('marketplace.orders'), request()->routeIs('marketplace.orders'), 0],
    ];
  } elseif ($isG) {
    $nav = [
      ['briefcase', 'Hustles', route('gigs.index'), request()->routeIs('gigs.index', 'gigs.show', 'gigs.edit'), 0],
      ['layers', 'My posts', route('gigs.index') . '#posts', false, 0],
    ];
    if ($canPost) $nav[] = ['plus-circle', 'Post', route('gigs.index') . '#post', false, 0];
    $nav[] = ['message-square', 'Chat', route('gigs.index') . '#chat', false, 0];
    $nav[] = ['bell', 'Alerts', route('gigs.index') . '#alerts', false, 0];
  } else {
    $nav = [
      ['user-circle', 'Profile', route('profile.index'), request()->routeIs('profile.index'), 0],
      ['wallet', 'Wallet', route('wallet.index'), request()->routeIs('wallet.*'), 0],
      ['crown', 'Plan', route('subscriptions.index'), request()->routeIs('subscriptions.*'), 0],
      ['users', 'Referrals', route('referrals.index'), request()->routeIs('referrals.*'), 0],
      ['settings', 'Account', route('profile.account'), request()->routeIs('profile.account'), 0],
    ];
  }
@endphp
<body class="ws2 {{ $isM ? 'ws2-market' : ($isG ? 'ws2-gigs' : 'ws2-profile') }}">
@include('partials.world-lens')

<main class="ws2-main">
@unless(($isP ?? false) || str_starts_with(optional(request()->route())->getName() ?? "", "profile"))@endunless
  @yield('content')
</main>
@include("partials.nv-dock", ["nvItems" => null])
@include('partials.dg-lens')
    @include('partials.page-bar')
<link rel="stylesheet" href="{{ asset('css/dashboard-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/nv-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/no-motion.css') }}?v={{ time() }}">
@if($isM)
@elseif($isG)
<div class="dg-coin" id="dgCoin" data-go="{{ route('marketplace.index') }}"><div class="dg-coin-in" id="dgCoinIn"><i data-lucide="shopping-bag" style="width:24px;height:24px"></i></div></div>
<div class="dg-shut" id="dgShut"></div>
@endif
<script>lucide.createIcons();</script>
@include('partials.world-switch-js')
@yield('scripts')
@stack('scripts')
    @include('partials.world-edge')
    @include('partials.world-hover')
<script src="/js/auto-contrast.js?v=3" defer></script>
<script src="/js/glass-adapt.js?v=3" defer></script>
<script src="/js/theme-btn.js?v=1" defer></script>
<script src="/js/auto-dark.js?v=8" defer></script>
</body>
</html>
