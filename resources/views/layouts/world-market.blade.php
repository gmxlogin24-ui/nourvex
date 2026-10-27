<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>NAURVEX Market - @yield('title', 'Market')</title>
  @include('partials.favicons')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#FFFFFF">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/gsap.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('css/world-extras.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/world-market.css') }}?v={{ @filemtime(public_path('css/world-market.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/world-shop.css') }}?v={{ @filemtime(public_path('css/world-shop.css')) }}">
</head>
@php
  $wmCart = count(session('cart', []));
@endphp
<body class="wm ws">
<header class="ws-head">
  <a href="{{ route('dashboard') }}" class="ws-ic" aria-label="Back to Overview"><i data-lucide="arrow-left"></i></a>
  <a href="{{ route('marketplace.index') }}" class="ws-title">Market</a>
  <nav class="ws-dnav"><a href="{{ route('marketplace.favorites') }}">Saved</a><a href="{{ route('marketplace.orders') }}">Orders</a></nav>
  <a href="{{ route('marketplace.favorites') }}" class="ws-ic ws-hide-d" aria-label="Saved"><i data-lucide="heart"></i></a>
  <a href="{{ route('cart.index') }}" class="ws-ic" aria-label="Cart"><i data-lucide="shopping-bag"></i>@if($wmCart)<em>{{ $wmCart }}</em>@endif</a>
</header>
<main class="ws-main">
  @yield('content')
</main>
<nav class="ws-tabs">
  <a href="{{ route('marketplace.index') }}" class="{{ request()->routeIs('marketplace.index') ? 'on' : '' }}"><i data-lucide="home"></i><span>Home</span></a>
  <a href="{{ route('marketplace.favorites') }}" class="{{ request()->routeIs('marketplace.favorites') ? 'on' : '' }}"><i data-lucide="heart"></i><span>Saved</span></a>
  <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'on' : '' }}"><i data-lucide="shopping-bag"></i><span>Cart</span></a>
  <a href="{{ route('marketplace.orders') }}" class="{{ request()->routeIs('marketplace.orders') ? 'on' : '' }}"><i data-lucide="package"></i><span>Orders</span></a>
</nav>
@include('partials.dg-lens')
    @include('partials.page-bar')
<link rel="stylesheet" href="{{ asset('css/dashboard-glass.css') }}?v={{ time() }}">
<script>lucide.createIcons();</script>
@include('partials.world-switch-js')
@yield('scripts')
    @include('partials.world-edge')
<script src="/js/auto-contrast.js?v=3" defer></script>
<script src="/js/auto-dark.js?v=8" defer></script>
</body>
</html>
