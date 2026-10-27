@extends('layouts.app-authenticated')
@section('title', 'All services')
@section('content')
@php
  $has = fn ($n) => \Illuminate\Support\Facades\Route::has($n);
  $groups = [
    'Pay & Bills' => [
      ['Airtime', 'services.index', 'smartphone'], ['Data', 'services.index', 'wifi'],
      ['Electricity', 'services.index', 'zap'], ['Betting', 'services.index', 'trophy'],
      ['Services hub', 'services.hub', 'layout-grid'],
      ['Cable TV', 'services.show', 'tv', ['cable']], ['Education', 'services.index', 'graduation-cap', ['tab' => 'education']],
    ],
    'Money' => [
      ['Wallet', 'wallet.index', 'wallet'], ['Send money', 'wallet.send', 'send'],
      ['Investments', 'investment.index', 'trending-up'], ['Q-Coin', 'q-coin.index', 'coins'],
      ['Money tracker', 'money-tracker.index', 'pie-chart'], ['Subscriptions', 'subscriptions.index', 'repeat'],
    ],
    'Earn' => [
      ['Hustles', 'gigs.index', 'briefcase'], ['Invite friends', 'referrals.index', 'user-plus'],
      ['Leaderboard', 'leaderboard.index', 'medal'], ['Levels & badges', 'levels-badges.levels', 'award'],
    ],
    'Shop & Social' => [
      ['Market', 'marketplace.index', 'shopping-bag'], ['Cart', 'cart.index', 'shopping-cart'],
      ['Orders', 'marketplace.orders', 'package'], ['Chat', 'chat.index', 'message-square'],
      ['Blog', 'blog.index', 'newspaper'], ['Games', 'golfvex.index', 'gamepad-2'],
    ],
    'Account' => [
      ['Alerts', 'notifications.index', 'bell'], ['Verification', 'kyc.show', 'shield-check'],
      ['Appearance', 'settings.appearance', 'palette'], ['Concierge', 'concierge.index', 'sparkles'],
    ],
  ];
@endphp
<link rel="stylesheet" href="{{ asset('css/all-services.css') }}?v={{ time() }}">
<div class="as-page">
  <div class="as-head">
    <a href="{{ route('dashboard') }}" class="as-back" aria-label="Back"><i data-lucide="chevron-left"></i></a>
    <b>All services</b>
  </div>
  @foreach($groups as $title => $items)
    @php $items = array_values(array_filter($items, fn ($i) => $has($i[1]))); @endphp
    @if(count($items))
    <section class="as-sec dg-glass">
      <h3>{{ $title }}</h3>
      <div class="as-grid">
        @foreach($items as $i)
          <a href="{{ route($i[1], $i[3] ?? []) }}" class="as-it">
            <span class="as-tile"><i data-lucide="{{ $i[2] }}"></i></span>
            <b>{{ $i[0] }}</b>
          </a>
        @endforeach
      </div>
    </section>
    @endif
  @endforeach
</div>
<script>if (window.lucide) lucide.createIcons();</script>
@endsection
