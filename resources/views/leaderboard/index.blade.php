@extends('layouts.app-authenticated')
@section('title', $type === 'earnings' ? 'Top Earners' : 'Rankings')
@section('content')
@php
    $rkDark = (auth()->user()->theme_style ?? '') === 'qelvo-dark';
    $fmt = fn ($v) => $type === 'earnings' ? '₦' . number_format($v) : number_format($v) . ' pts';
    $link = fn (array $over = []) => route('leaderboard.index', array_filter(
        array_merge(['type' => $type, 'period' => $period, 'area' => $area], $over),
        fn ($x) => $x !== null && $x !== ''
    ));
    $periodLabels = ['week' => 'This week', 'month' => 'This month', 'all' => 'All time'];
    $labels = [
        'daily_login' => 'Log in each day',
        'streak_7' => '7-day login streak',
        'streak_30' => '30-day login streak',
        'qcoin_claim' => 'Claim your daily Q Coins',
        'referral' => 'Refer a friend who signs up',
        'gig_completed' => 'Complete a gig',
        'service_purchase' => 'Buy airtime, data or cable (more for bigger spend)',
    ];
    $me = auth()->user();
    $mult = config('ranking.plan_multiplier.' . $me->planKey(), 1);
    $top = $rows->take(3)->values();
    $rest = $rows->slice(3)->values();
    $resets = $period === 'week' ? 'resets every Monday' : ($period === 'month' ? 'resets on the 1st' : '');
@endphp
<link rel="stylesheet" href="{{ asset('css/rank-redesign.css') }}?v={{ time() }}">
<div class="rk {{ $rkDark ? 'rk-dark' : '' }}">
  <header class="rk-head">
    <h1>{{ $type === 'earnings' ? 'Top earners' : 'Rankings' }}</h1>
    <p>{{ $area ? $areas[$area] : 'All areas' }} · {{ $periodLabels[$period] }}@if($resets) · {{ $resets }}@endif</p>
  </header>

  @if (session('status'))<div class="rk-flash">{{ session('status') }}</div>@endif

  <div class="rk-filters">
    <div class="rk-seg">
      <a href="{{ $link(['type' => 'points']) }}" class="{{ $type === 'points' ? 'on' : '' }}">Points</a>
      <a href="{{ $link(['type' => 'earnings']) }}" class="{{ $type === 'earnings' ? 'on' : '' }}">Earnings</a>
    </div>
    <div class="rk-seg">
      @foreach ($periodLabels as $k => $label)
        <a href="{{ $link(['period' => $k]) }}" class="{{ $period === $k ? 'on' : '' }}">{{ $label }}</a>
      @endforeach
    </div>
    <div class="rk-chips">
      <a href="{{ $link(['area' => null]) }}" class="{{ !$area ? 'on' : '' }}">All areas</a>
      @foreach ($areas as $k => $label)
        <a href="{{ $link(['area' => $k]) }}" class="{{ $area === $k ? 'on' : '' }}">{{ $label }}</a>
      @endforeach
    </div>
  </div>

  <div class="rk-main">
    <section class="rk-me">
      <div>
        <b>{{ $optedIn ? "You're on the board" : 'Join the leaderboard' }}</b>
        <small>Your points: {{ number_format($myPoints) }}@if ($myRank) · Rank #{{ $myRank }}@elseif (!$optedIn) · join to appear on the board @endif</small>
        <small>Only your first name, area and total show.</small>
      </div>
      <form method="POST" action="{{ route('leaderboard.toggle') }}">
        @csrf
        <button type="submit" class="rk-btn">{{ $optedIn ? 'Leave' : 'Join' }}</button>
      </form>
    </section>

    @if ($top->count())
    <div class="rk-podium">
      @foreach ([1, 0, 2] as $slot)
        @if (isset($top[$slot]))
        <div class="rk-place {{ ['first', 'second', 'third'][$slot] }}">
          <span class="rk-av">{{ strtoupper(substr($top[$slot]->name, 0, 1)) }}</span>
          <span class="rk-nm">{{ explode(' ', $top[$slot]->name)[0] }}</span>
          <span class="rk-amt">{{ $fmt($top[$slot]->total) }}</span>
          <span class="rk-bar">{{ $slot + 1 }}</span>
        </div>
        @endif
      @endforeach
    </div>
    @endif

    <section class="rk-list">
      @forelse ($rest as $i => $entry)
      <div class="rk-row">
        <span class="rk-no">{{ $i + 4 }}</span>
        <span class="rk-rn">{{ explode(' ', $entry->name)[0] }}@if ($entry->area && isset($areas[$entry->area]))<span class="rk-ra">· {{ $areas[$entry->area] }}</span>@endif</span>
        <span class="rk-rv">{{ $fmt($entry->total) }}</span>
      </div>
      @empty
        @if ($rows->count() === 0)<p class="rk-empty">Nobody here yet. Be the first.</p>@endif
      @endforelse
    </section>
  </div>

  @if ($type === 'points')
  <aside class="rk-side">
    <h3>How to earn points</h3>
    @foreach ($labels as $key => $label)
      @if (config("ranking.points.$key"))
      <div class="it"><span>{{ $label }}</span><b>+{{ (int) round(config("ranking.points.$key") * $mult) }}</b></div>
      @endif
    @endforeach
    <p>Your {{ $me->planLabel() }} plan gives ×{{ $mult }} points.
      @if ($me->planKey() !== 'tier3')<a href="{{ route('subscriptions.index') }}">Upgrade for a higher multiplier</a>@endif
    </p>
  </aside>
  @endif
</div>
@endsection
