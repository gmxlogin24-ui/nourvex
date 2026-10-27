@php
  $wswLinks = [
    'overview' => ['Overview', 'dashboard'],
    'qcoin'    => ['Q-Coin', 'qcoin.index'],
    'rank'     => ['Rankings', 'leaderboard.index'],
    'wallet'   => ['Wallet', 'wallet.index'],
    'market'   => ['Market', 'marketplace.index'],
    'gigs'     => ['Hustles', 'gigs.index'],
    'profile'  => ['Profile', 'profile.index'],
  ];
@endphp
<link rel="stylesheet" href="{{ asset('css/world-extras.css') }}?v={{ time() }}">
<nav class="wsw">
  @foreach($wswLinks as $key => $pair)
    <a href="{{ route($pair[1]) }}" class="{{ ($world ?? '') === $key ? 'on' : '' }}">{{ $pair[0] }}</a>
  @endforeach
</nav>
