@php
  $ovDockActive = $ovDockActive ?? 'overview';
  $ovGv = Route::has('golfvex.index') ? route('golfvex.index') : url('/golfvex');
  $ovDockItems = [
    ['overview', 'Home', 'layout-dashboard', route('dashboard')],
    ['market', 'Markets', 'shopping-bag', route('marketplace.index')],
    ['hustles', 'Hustles', 'briefcase', route('gigs.index')],
    ['games', 'Games', 'gamepad-2', route('games.index')],
    ['assets', 'Assets', 'wallet', route('assets.new')],
  ];
@endphp
<link rel="stylesheet" href="{{ asset('css/ov-dock.css') }}?v={{ time() }}">
<nav class="ovd" aria-label="Main">
@foreach($ovDockItems as $it)
  <a href="{{ $it[3] }}" class="ovd-btn {{ $ovDockActive === $it[0] ? 'on' : '' }}" aria-label="{{ $it[1] }}" title="{{ $it[1] }}">
    <span class="ovd-tile"><span class="ovd-in"><i data-lucide="{{ $it[2] }}"></i></span></span>
    <span class="ovd-lbl">{{ $it[1] }}</span>
  </a>
@endforeach
</nav>
