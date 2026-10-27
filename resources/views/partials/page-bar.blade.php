@auth
@php
  $pbU = auth()->user();
  $pbPhoto = $navAvatar ?? $pbU->avatar_url ?? null;
  $pbInit = $navInitial ?? strtoupper(substr($pbU->name ?? 'U', 0, 1));
  $pbHas = fn ($n) => \Illuminate\Support\Facades\Route::has($n);
  $pbUnread = 0;
  try { $pbUnread = (int) $pbU->unreadNotifications()->count(); } catch (\Throwable $e) { $pbUnread = 0; }
  $pbCart = count(session('cart', []));
@endphp
<link rel="stylesheet" href="{{ asset('css/page-bar.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/pb-glass.css') }}?v={{ time() }}">
<header class="pb-bar" id="pbBar">
  <a href="{{ $pbHas('profile.index') ? route('profile.index') : '#' }}" class="pb-av" aria-label="Profile">@if($pbPhoto)<img src="{{ $pbPhoto }}" alt="">@else<span>{{ $pbInit }}</span>@endif</a>
  @if(request()->routeIs('marketplace.*', 'cart.*'))
  <form method="GET" action="{{ route('marketplace.index') }}" class="pb-search pb-search-f" role="search"><i data-lucide="search"></i><input type="search" name="q" value="{{ request()->routeIs('marketplace.index') ? request('q') : '' }}" placeholder="Search market" autocomplete="off" aria-label="Search market">@if(request()->routeIs('marketplace.index') && request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif @if(request()->routeIs('marketplace.index') && request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif</form>
  @else
  <a href="{{ $pbHas('search') ? route('search') : '#' }}" class="pb-search" aria-label="Search"><i data-lucide="search"></i><span>Search</span></a>
  @endif
  <a href="{{ $pbHas('referrals.index') ? route('referrals.index') : '#' }}" class="pb-ic pb-inv" aria-label="Invite friends"><i data-lucide="user-plus"></i></a>
  <a href="{{ $pbHas('chat.index') ? route('chat.index') : '#' }}" class="pb-ic pb-tg" aria-label="Messages"><i data-lucide="send"></i>@if(($navUnreadChats ?? 0) > 0)<em class="nv-badge">{{ $navUnreadChats > 99 ? '99+' : $navUnreadChats }}</em>@endif</a>
  <button type="button" class="pb-ic" data-theme-btn aria-label="Light or dark mode"><i data-lucide="sun-moon"></i></button>
</header>
<script src="{{ asset('js/page-bar.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/pb-fit.js') }}?v={{ time() }}"></script>
@endauth
