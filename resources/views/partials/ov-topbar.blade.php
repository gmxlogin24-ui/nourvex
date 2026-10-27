@php
  $tbUser = auth()->user();
  $tbPhoto = $navAvatar ?? $tbUser->avatar_url ?? null;
  $tbInit = $navInitial ?? strtoupper(substr($tbUser->name ?? 'U', 0, 1));
  $tbHas = fn ($n) => \Illuminate\Support\Facades\Route::has($n);
  $tbUnread = 0;
  try { $tbUnread = (int) $tbUser->unreadNotifications()->count(); } catch (\Throwable $e) { $tbUnread = 0; }
@endphp
<link rel="stylesheet" href="{{ asset('css/topbar.css') }}?v={{ time() }}">
<header class="ovm-bar dg-glass">
  <a href="{{ $tbHas('profile.index') ? route('profile.index') : '#' }}" class="dg-av">@if($tbPhoto)<img src="{{ $tbPhoto }}" alt="">@else<span>{{ $tbInit }}</span>@endif</a>
  <a href="{{ $tbHas('search') ? route('search') : '#' }}" class="ovm-search"><i data-lucide="search"></i><span>Naurvex AI</span></a>
  <a href="{{ $tbHas('referrals.index') ? route('referrals.index') : '#' }}" class="ovm-ic" aria-label="Invite friends"><i data-lucide="user-plus"></i></a>
  <a href="{{ $tbHas('chat.index') ? route('chat.index') : '#' }}" class="ovm-ic ovm-bell" aria-label="Messages"><i data-lucide="message-circle"></i>@if(($navUnreadChats ?? 0) > 0)<em class="nv-badge">{{ $navUnreadChats > 99 ? '99+' : $navUnreadChats }}</em>@endif</a>
  <button type="button" class="ovm-ic" data-theme-btn aria-label="Light or dark mode"><i data-lucide="sun-moon"></i></button>
</header>
