@extends('layouts.app-authenticated')
@section('title', 'My Assets')
@section('content')
@php
  $au = auth()->user();
  $qr = config('qcoin.q_per_naira_display', 10);
  $pick = function ($keys, $d = null) use ($__data) {
    foreach ($keys as $k) { if (isset($__data[$k])) return $__data[$k]; }
    return $d;
  };
  $rt = function ($names) {
    foreach ((array) $names as $n) { if (\Illuminate\Support\Facades\Route::has($n)) return route($n); }
    return '#';
  };
  $ago = function ($v) {
    try { return $v ? \Illuminate\Support\Carbon::parse($v)->diffForHumans() : ''; } catch (\Throwable $e) { return ''; }
  };
  $balRaw = $pick(['balance']);
  $bal = is_numeric($balRaw) ? (float) $balRaw : (float) ($au->wallet_balance ?? $au->balance ?? 0);
  $qRaw = $pick(['navQCoinBalance', 'qCoinBalance']);
  $qc = is_numeric($qRaw) ? (int) $qRaw : (int) ($au->q_coin_balance ?? 0);
  $gigE = 0; $mktE = 0; $refE = 0; $series = [];
  try {
    $txq = \App\Models\Transaction::where('user_id', $au->id);
    $gigE = (float) (clone $txq)->where('type', 'gig_earning')->where('amount', '>', 0)->sum('amount');
    $mktE = (float) (clone $txq)->where('type', 'marketplace_sale')->where('amount', '>', 0)->sum('amount');
    $refE = (float) (clone $txq)->where('type', 'like', '%referral%')->where('amount', '>', 0)->sum('amount');
    $week = (clone $txq)->where('created_at', '>=', now()->subDays(6)->startOfDay())->get(['amount', 'created_at']);
    $byDay = $week->groupBy(fn ($r) => \Illuminate\Support\Carbon::parse($r->created_at)->toDateString());
    for ($i = 6; $i >= 0; $i--) {
      $dk = now()->subDays($i)->toDateString();
      $series[] = $byDay->has($dk) ? (float) $byDay[$dk]->sum('amount') : 0.0;
    }
  } catch (\Throwable $e) {}
  if (count($series) !== 7) { $series = array_fill(0, 7, 0.0); }
  $inv = collect(); $invTotal = 0.0;
  try {
    foreach (['App\\Models\\UserInvestment', 'App\\Models\\Investment'] as $m) {
      if (class_exists($m)) { $inv = $m::query()->where('user_id', $au->id)->latest()->take(6)->get(); break; }
    }
  } catch (\Throwable $e) { $inv = collect(); }
  foreach ($inv as $iv) { $invTotal += (float) (data_get($iv, 'amount') ?? data_get($iv, 'principal') ?? 0); }
  $total = $bal + $invTotal;
  $todayNet = $series[6];
  $prev = $total - $todayNet;
  $pct = $prev > 0 ? ($todayNet / $prev) * 100 : 0;
  $cum = []; $run = $total - array_sum($series);
  foreach ($series as $v) { $run += $v; $cum[] = $run; }
  $mn = min($cum); $mx = max($cum);
  $pts = [];
  foreach ($cum as $i => $v) {
    $px = round($i * (160 / 6), 1);
    $py = $mx > $mn ? round(52 - (($v - $mn) / ($mx - $mn)) * 44, 1) : 38;
    $pts[] = $px . ',' . $py;
  }
  $line = implode(' ', $pts);
  $txRaw = $pick(['transactions']);
  if ($txRaw instanceof \Illuminate\Pagination\AbstractPaginator) { $txs = $txRaw->getCollection(); }
  elseif ($txRaw instanceof \Illuminate\Support\Collection) { $txs = $txRaw; }
  elseif (is_array($txRaw)) { $txs = collect($txRaw); }
  else {
    try { $txs = \App\Models\Transaction::where('user_id', $au->id)->latest()->take(6)->get(); }
    catch (\Throwable $e) { $txs = collect(); }
  }
  $txs = $txs->take(5);
  $tiles = [
    ['Investments', 'trending-up', $rt(['investment.index'])],
    ['Q-Coin & Rewards', 'coins', $rt(['q-coin.index'])],
    ['Digital Assets', 'box', $rt(['marketplace.orders'])],
    ['Holdings', 'wallet', $rt(['wallet.index'])],
    ['Activity', 'file-text', $rt(['money-tracker.index'])],
  ];
@endphp
<link rel="stylesheet" href="{{ asset('css/dashboard-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dg-desktop-layout.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-desktop.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/ovm-layout.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/mono.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/grid-fit.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dash-shade.css') }}?v={{ @filemtime(public_path('css/dash-shade.css')) }}">
<link rel="stylesheet" href="{{ asset('css/assets-layout.css') }}?v={{ time() }}">
<div class="dg" id="dg"><div class="dg-in ovm">
<div class="as3">
  <header class="as3-top">
    <div><h1>My Assets</h1><p>Your investments, rewards and owned items.</p></div>
    <span class="as3-pill"><i data-lucide="shield-check"></i>Secure &amp; Trusted</span>
  </header>

  <section class="as3-hero">
    <div class="as3-h1">
      <span>Total Asset Value</span>
      <button type="button" id="as3Eye" aria-label="Hide amounts"><i data-lucide="eye"></i></button>
      <a href="{{ $rt('wallet.index') }}" class="as3-all">All Assets <i data-lucide="arrow-right"></i></a>
    </div>
    <b class="as3-big as3-mask">₦{{ number_format($total, 2) }}</b>
    <p class="as3-pl as3-mask"><span class="{{ $todayNet < 0 ? 'dn' : 'up' }}">{{ $todayNet < 0 ? '−' : '+' }}{{ number_format(abs($todayNet), 2) }} ({{ number_format(abs($pct), 2) }}%)</span> today</p>
    <svg class="as3-chart" viewBox="0 0 160 60" preserveAspectRatio="none" aria-hidden="true"><polygon points="0,60 {{ $line }} 160,60"/><polyline points="{{ $line }}"/></svg>
  </section>

  <nav class="as3-tiles" aria-label="Asset sections">
    @foreach($tiles as $t)
      <a href="{{ $t[2] }}" class="as3-tile dg-glass"><span class="as3-ti"><i data-lucide="{{ $t[1] }}"></i></span><b>{{ $t[0] }}</b><i data-lucide="arrow-right" class="as3-ar"></i></a>
    @endforeach
  </nav>

  <div class="as3-grid">
    <section class="as3-sec s-act">
      <header class="as3-sh"><span><i data-lucide="bar-chart-3"></i>Active Investments</span><a href="{{ $rt('investment.index') }}">View all <i data-lucide="arrow-right"></i></a></header>
      <div class="as3-panel dg-glass">
        @if($inv->count())
          <div class="as3-invs">
            @foreach($inv->take(6) as $iv)
              <a href="{{ $rt('investment.index') }}" class="as3-inv">
                <span class="as3-ti"><i data-lucide="trending-up"></i></span>
                <b>{{ \Illuminate\Support\Str::limit((string) (data_get($iv, 'name') ?? data_get($iv, 'title') ?? data_get($iv, 'plan_name') ?? 'Investment'), 18) }}</b>
                <small>₦{{ number_format((float) (data_get($iv, 'amount') ?? data_get($iv, 'principal') ?? 0)) }}</small>
              </a>
            @endforeach
          </div>
        @else
          <div class="as3-empty">No active investments yet.<a href="{{ $rt('investment.index') }}">Explore investments</a></div>
        @endif
      </div>
    </section>

    <section class="as3-sec s-q">
      <header class="as3-sh"><span><i data-lucide="coins"></i>Q-Coin &amp; Rewards</span><a href="{{ $rt('q-coin.index') }}">View all <i data-lucide="arrow-right"></i></a></header>
      <div class="as3-panel dg-glass as3-q">
        <div><small>Q-Coin Balance</small><b>{{ number_format($qc) }} <i data-lucide="coins"></i></b></div>
        <div><small>Total Earned</small><b>{{ number_format(($gigE + $mktE + $refE) * $qr) }} <i data-lucide="award"></i></b></div>
        <div><small>Rewards</small><b>{{ number_format($refE * $qr) }} <i data-lucide="gift"></i></b></div>
      </div>
    </section>

    <section class="as3-sec s-dig">
      <header class="as3-sh"><span><i data-lucide="box"></i>Digital Assets</span><a href="{{ $rt('marketplace.orders') }}">View all <i data-lucide="arrow-right"></i></a></header>
      <div class="as3-digs">
        @for($i = 0; $i < 4; $i++)
          <div class="as3-dig dg-glass"><span class="as3-img"><i data-lucide="image"></i></span><span class="as3-sk"></span><span class="as3-sk s"></span><em>Not available</em></div>
        @endfor
      </div>
    </section>

    <section class="as3-sec s-rec">
      <header class="as3-sh"><span><i data-lucide="clock"></i>Recent Activity</span><a href="{{ $rt('money-tracker.index') }}">View all <i data-lucide="arrow-right"></i></a></header>
      <div class="as3-panel dg-glass">
        @forelse($txs as $t)
          @php
            $ta = (float) data_get($t, 'amount', 0);
            $tl = data_get($t, 'description') ?: data_get($t, 'narration') ?: ucfirst(str_replace('_', ' ', (string) data_get($t, 'type', 'Transaction')));
          @endphp
          <div class="as3-tx">
            <span class="as3-ti sm"><i data-lucide="{{ $ta >= 0 ? 'arrow-down' : 'arrow-up' }}"></i></span>
            <span class="t"><b>{{ \Illuminate\Support\Str::limit((string) $tl, 32) }}</b><small>{{ $ago(data_get($t, 'created_at')) }}</small></span>
            <span class="as3-amt">{{ $ta >= 0 ? '+' : '−' }}₦{{ number_format(abs($ta)) }}</span>
          </div>
        @empty
          <div class="as3-empty">No activity yet. Earnings from gigs, sales and referrals will show up here.</div>
        @endforelse
      </div>
    </section>
  </div>
</div>
</div></div>
<script>
(function () {
  var b = document.getElementById('as3Eye'), h = document.querySelector('.as3-hero');
  if (b && h) b.addEventListener('click', function () { h.classList.toggle('hide'); });
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection
