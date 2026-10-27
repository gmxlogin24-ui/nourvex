<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>NAURVEX Hustles - @yield('title', 'Hustles')</title>
  @include('partials.favicons')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#14A800">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/gsap.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('css/world-extras.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/world-gigs.css') }}?v={{ @filemtime(public_path('css/world-gigs.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/world-glass.css') }}?v={{ @filemtime(public_path('css/world-glass.css')) }}">
</head>
@php
  $wgPost = auth()->user()->planAllows('gigs_post');
@endphp
<body class="wg">
@include('partials.world-lens')
<header class="wg-head">
  <div class="wg-row">
    <a href="{{ route('dashboard') }}" class="wg-exit" aria-label="Back to Overview"><i data-lucide="chevron-left"></i></a>
    <a href="{{ route("gigs.index") }}" class="wg-logo"><i data-lucide="briefcase"></i><span>Naurvex <b>Hustles</b><small>Work · Earn · Grow</small></span></a>
    @if($wgPost)<a href="{{ route("gigs.create") }}" class="wg-post"><i data-lucide="plus"></i><em>Post a job</em></a>@endif
  </div>
</header>
<main class="wg-main">
  @yield('content')
</main>
<nav class="wg-tabs"><a href="{{ route("gigs.index") }}" class="{{ request()->routeIs("gigs.index") || request()->routeIs("gigs.show") ? "on" : "" }}"><i data-lucide="search"></i><span>Jobs</span></a><a href="{{ route("posts.index") }}" class="{{ request()->routeIs("posts.*") ? "on" : "" }}"><i data-lucide="layers"></i><span>My jobs</span></a>@if($wgPost)<a href="{{ route("gigs.create") }}" class="{{ request()->routeIs("gigs.create") ? "on" : "" }}"><i data-lucide="plus-circle"></i><span>Post</span></a>@endif<a href="{{ route("chat.index") }}"><i data-lucide="message-square"></i><span>Messages</span></a><a href="{{ route("notifications.index") }}"><i data-lucide="bell"></i><span>Alerts</span></a></nav>
@include('partials.dg-lens')
<link rel="stylesheet" href="{{ asset('css/dashboard-glass.css') }}?v={{ time() }}">
<div class="dg-coin" id="dgCoin" data-go="{{ route('marketplace.index') }}"><div class="dg-coin-in" id="dgCoinIn"><i data-lucide="shopping-bag" style="width:24px;height:24px"></i></div></div>
<div class="dg-shut" id="dgShut"></div>
<script>lucide.createIcons();</script>
@include('partials.world-switch-js')
@yield('scripts')
    @include('partials.world-edge')
<script src="/js/auto-contrast.js?v=3" defer></script>
<script src="/js/auto-dark.js?v=8" defer></script>
</body>
</html>
