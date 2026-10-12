@extends('layouts.app-authenticated')
@section('title', 'Dashboard')
@section('content')
@php
    $u = auth()->user();
    $hr = now()->hour;
    $greet = $hr < 12 ? 'Good morning' : ($hr < 17 ? 'Good afternoon' : 'Good evening');
    $in = $totalIn ?? 0;
    $out = $totalOut ?? 0;
    $bal = $in - $out;
    $chg = $in > 0 ? round((($in - $out) / $in) * 100) : 0;
    $cats = collect($categoryTotals ?? []);
    $palette = ['#b8f03c', '#b9b6ff', '#ff7b6b', '#f3f27a', '#7dd3c0', '#f59e0b'];
    $go = fn($n, $f = '#') => Route::has($n) ? route($n) : $f;
@endphp
<main class="v3">
  <header class="v3-head">
    <a href="{{ $go('profile.index') }}" class="v3-avatar">
      @if($u->avatar_url)<img src="{{ $u->avatar_url }}" alt="">@else<span>{{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}</span>@endif
    </a>
    <div class="v3-hello"><small>{{ $greet }}</small><b>{{ explode(' ', $u->name ?? 'there')[0] }}</b></div>
    <a href="{{ $go('notifications.index') }}" class="v3-bell"><i data-lucide="bell" class="w-5 h-5"></i>@if(($unreadNotifications ?? 0) > 0)<em>{{ $unreadNotifications }}</em>@endif</a>
  </header>

  <div class="v3-search"><i data-lucide="search" class="w-4 h-4"></i><input type="text" placeholder="Search hustles, items, people..."></div>

  <section class="v3-hero">
    <small>Total balance</small>
    <h1>₦{{ number_format($bal) }}</h1>
    <div class="v3-tiles">
      <div class="v3-tile"><span class="ic lime"><i data-lucide="arrow-down-left" class="w-4 h-4"></i></span><b>₦{{ number_format($in) }}</b><small>Total income</small></div>
      <div class="v3-tile"><span class="ic lav"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></span><b>₦{{ number_format($out) }}</b><small>Total spent</small></div>
    </div>
    <a href="{{ $go('wallet.index') }}" class="v3-banner"><div><b>{{ $chg >= 0 ? '+' : '' }}{{ $chg }}% kept</b><small>Share of income you still hold</small></div><i data-lucide="chevron-right" class="w-5 h-5"></i></a>
  </section>

  <nav class="v3-chips">
    <a href="{{ $go('gigs.create', $go('gigs.index')) }}" class="on"><i data-lucide="plus" class="w-4 h-4"></i> Post hustle</a>
    <a href="{{ $go('marketplace.create', $go('marketplace.index')) }}">Sell item</a>
    <a href="{{ $go('wallet.index') }}">Wallet</a>
    <a href="{{ $go('gigs.index') }}">Find hustles</a>
    <a href="{{ $go('marketplace.index') }}">Campus market</a>
  </nav>

  <section class="v3-stats">
    <div class="v3-stat"><i data-lucide="briefcase" class="w-4 h-4"></i><b>{{ $myOpenGigs ?? 0 }}</b><small>Open hustles</small></div>
    <div class="v3-stat"><i data-lucide="check-circle" class="w-4 h-4"></i><b>{{ $myCompletedGigs ?? 0 }}</b><small>Completed</small></div>
    <div class="v3-stat"><i data-lucide="shopping-bag" class="w-4 h-4"></i><b>{{ $myListings ?? 0 }}</b><small>Listings</small></div>
    <div class="v3-stat"><i data-lucide="users" class="w-4 h-4"></i><b>{{ isset($activeReferrals) ? $activeReferrals->count() : 0 }}</b><small>Active referrals</small></div>
  </section>

  @if($cats->count())
  <section class="v3-card">
    <div class="v3-row"><h2>Money flow</h2><small>By type</small></div>
    <div class="v3-ring"><canvas id="v3Ring"></canvas><div class="mid"><small>Total</small><b>₦{{ number_format($cats->sum()) }}</b></div></div>
    <ul class="v3-legend">
      @foreach($cats as $k => $v)
      <li><i style="background:{{ $palette[$loop->index % count($palette)] }}"></i>{{ ucfirst(str_replace('_', ' ', $k)) }}<b>₦{{ number_format($v) }}</b></li>
      @endforeach
    </ul>
  </section>
  @endif

  <section>
    <div class="v3-row"><h2>Top hustles</h2><a href="{{ $go('gigs.index') }}">See all</a></div>
    <div class="v3-scroll">
      @forelse(($gigs ?? collect()) as $g)
      <a href="{{ route('gigs.show', $g->id) }}" class="v3-gig">
        <span class="tag">{{ $g->category ?? 'Gig' }}</span>
        <h3>{{ \Illuminate\Support\Str::limit($g->title, 48) }}</h3>
        <div class="bar"><i style="width: {{ ($g->status ?? '') === 'completed' ? 100 : (($g->status ?? '') === 'in_progress' ? 60 : 20) }}%"></i></div>
        <div class="foot"><b>₦{{ number_format($g->amount ?? 0) }}</b><span>View</span></div>
      </a>
      @empty
      <p class="v3-empty">No hustles yet. Check back soon.</p>
      @endforelse
    </div>
  </section>

  <section class="v3-card">
    <div class="v3-row"><h2>Recent activity</h2><a href="{{ $go('wallet.index') }}">See all</a></div>
    @forelse(($recentTx ?? collect()) as $tx)
    <div class="v3-tx">
      <span class="ic {{ $tx->amount >= 0 ? 'lime' : 'lav' }}"><i data-lucide="{{ $tx->amount >= 0 ? 'arrow-down-left' : 'arrow-up-right' }}" class="w-4 h-4"></i></span>
      <div><b>{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</b><small>{{ $tx->created_at->diffForHumans() }}</small></div>
      <em class="{{ $tx->amount >= 0 ? 'up' : '' }}">{{ $tx->amount >= 0 ? '+' : '-' }}₦{{ number_format(abs($tx->amount)) }}</em>
    </div>
    @empty
    <p class="v3-empty">No transactions yet.</p>
    @endforelse
  </section>

  <section>
    <div class="v3-row"><h2>For you today</h2></div>
    <nav class="v3-chips sm" id="v3Filter">
      <a href="#" data-t="all" class="on">All</a><a href="#" data-t="gig">Hustles</a><a href="#" data-t="listing">Market</a><a href="#" data-t="news">News</a>
    </nav>
    <div class="v3-feed">
      @foreach(($todayFeed ?? collect()) as $f)
      <a href="{{ $f['url'] ?? '#' }}" class="v3-feeditem" data-type="{{ $f['type'] }}">
        <span class="tag {{ $f['type'] }}">{{ $f['type'] === 'gig' ? 'Hustle' : ucfirst($f['type']) }}</span>
        <b>{{ \Illuminate\Support\Str::limit($f['title'], 60) }}</b>
        <small>{{ $f['meta'] ?? '' }}</small>
      </a>
      @endforeach
    </div>
  </section>
</main>

<script>
(function () {
  if (window.lucide) lucide.createIcons();
  var el = document.getElementById('v3Ring');
  if (el && window.Chart) {
    new Chart(el, { type: 'doughnut',
      data: { datasets: [{ data: @json($cats->values()), backgroundColor: @json(array_slice($palette, 0, max(1, $cats->count()))), borderWidth: 0, borderRadius: 10, spacing: 4 }] },
      options: { cutout: '78%', plugins: { legend: { display: false }, tooltip: { enabled: false } } } });
  }
  document.querySelectorAll('#v3Filter a').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelectorAll('#v3Filter a').forEach(function (x) { x.classList.remove('on'); });
      a.classList.add('on');
      document.querySelectorAll('.v3-feeditem').forEach(function (i) {
        i.style.display = (a.dataset.t === 'all' || i.dataset.type === a.dataset.t) ? '' : 'none';
      });
    });
  });
})();
</script>
@endsection
