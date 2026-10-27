@php $avatar = $avatar ?? ($navAvatar ?? (auth()->user()->avatar_url ?? null)); $initial = $initial ?? ($navInitial ?? strtoupper(substr(auth()->user()->name ?? "U", 0, 1))); $gvUrl = $gvUrl ?? url("/golfvex"); @endphp
<header class="dgd-bar">
  <a href="{{ route('dashboard') }}" class="dgd-brand">Naurvex</a>
  <nav>
    <a href="{{ route('dashboard') }}" class="on">Home</a>
    <a href="{{ $gvUrl }}">NaGolf</a>
    <a href="{{ route('wallet.index') }}">Wallet</a>
    <a href="{{ route('profile.index') }}">Profile</a>
  </nav>
  <a href="{{ route('profile.index') }}" class="dgd-av">@if($avatar)<img src="{{ $avatar }}" alt="">@else<span>{{ $initial }}</span>@endif</a>
</header>
