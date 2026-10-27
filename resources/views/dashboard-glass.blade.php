@extends('layouts.app-authenticated')
@section('title', 'Overview')
@section('content')
@php
  $qr = config('qcoin.q_per_naira_display', 10);
  $net = (float) ($weekIn ?? 0) - (float) ($weekOut ?? 0);
  $gvUrl = Route::has('golfvex.index') ? route('golfvex.index') : url('/golfvex');
  $dgUser = auth()->user();
  $dgTx = \App\Models\Transaction::where('user_id', $dgUser->id);
  $dgGigE = (float) (clone $dgTx)->where('type', 'gig_earning')->where('amount', '>', 0)->sum('amount');
  $dgMktE = (float) (clone $dgTx)->where('type', 'marketplace_sale')->where('amount', '>', 0)->sum('amount');
  $dgPhoto = $navAvatar ?? $dgUser->avatar_url ?? null;
  $dgInit = $navInitial ?? strtoupper(substr($dgUser->name ?? 'U', 0, 1));
  $dgQ = (int) ($navQCoinBalance ?? $dgUser->q_coin_balance ?? 0);
  $ovGrid = [
    ['Airtime', route('services.show', 'airtime'), 'smartphone', 'linear-gradient(145deg,#38BDF8,#2563EB)'],
    ['Data', route('services.show', 'data'), 'wifi', 'linear-gradient(145deg,#34D399,#059669)'],
    ['Electricity', route('services.index', ['tab' => 'electricity']), 'plug-zap', 'linear-gradient(145deg,#FACC15,#CA8A04)'],
    ['Betting', route('services.index', ['tab' => 'betting']), 'trophy', 'linear-gradient(145deg,#F472B6,#DB2777)'],
    ['Cycle', route('wallet.index'), 'refresh-cw', 'linear-gradient(145deg,#38BDF8,#2563EB)'],
    ['PayMe', route('wallet.index'), 'send', 'linear-gradient(145deg,#FB923C,#EA580C)'],
    ['Invite Friends', route('referrals.index'), 'user-plus', 'linear-gradient(145deg,#2DD4BF,#0D9488)'],
    ['More', route('services.all'), 'layout-grid', 'linear-gradient(145deg,#B79BFF,#7A5CFA)'],
  ];
  $ovRail = [
    ['Ranks', route('leaderboard.index'), 'medal', 'linear-gradient(145deg,#FACC15,#CA8A04)'],
    ['My store', route('referrals.index'), 'gift', 'linear-gradient(145deg,#B79BFF,#7A5CFA)'],
    ['Wallet', route('wallet.index'), 'wallet', 'linear-gradient(145deg,#38BDF8,#2563EB)'],
  ];
  $dgWorlds = [
    ['Hustle', 'Work · Earn · Grow', route('gigs.index'), asset('images/nv-world-gigs.jpg'), 'briefcase', '46,204,140'],
    ['Market', 'Discover · Buy · Enjoy', route('marketplace.index'), asset('images/nv-world-market.jpg'), 'shopping-bag', '255,150,60'],
  ];
@endphp

<link rel="stylesheet" href="{{ asset('css/dashboard-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dg-desktop-layout.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-desktop.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/ovm-layout.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/mono.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/grid-fit.css') }}?v={{ time() }}">

<div class="dg-bgs" id="dgBgs" aria-hidden="true">
  @foreach($dgWorlds as $i => $w)<i class="dg-bg {{ $i === 0 ? 'on' : '' }}" style="background-image:url('{{ $w[3] }}')"></i>@endforeach
</div>

<div class="dg" id="dg">
  <div class="dg-in ovm">

    <header class="ovm-bar dg-glass">
      <a href="{{ route('profile.index') }}" class="dg-av">@if($dgPhoto)<img src="{{ $dgPhoto }}" alt="">@else<span>{{ $dgInit }}</span>@endif</a>
      <a href="{{ route('search') }}" class="ovm-search"><i data-lucide="search"></i><span>Naurvex AI</span></a>
      <a href="{{ route('referrals.index') }}" class="ovm-ic" aria-label="Invite friends"><i data-lucide="user-plus"></i></a>
      <a href="{{ route('chat.index') }}" class="ovm-ic ovm-bell" aria-label="Messages"><i data-lucide="message-circle"></i>@if(($navUnreadChats ?? 0) > 0)<em class="nv-badge">{{ $navUnreadChats > 99 ? '99+' : $navUnreadChats }}</em>@endif</a>
      <button type="button" class="ovm-ic" data-theme-btn aria-label="Light or dark mode"><i data-lucide="sun-moon"></i></button>
    </header>

    <section class="ovm-assets dg-glass">
      <small>Total Assets</small>
      <div class="ovm-amt">
        <b><span class="dgq">{{ number_format($balance * $qr) }}</span> Q</b>
        <a href="{{ route('wallet.deposit') }}" class="ovm-dep ovm-dep-money"><i data-lucide="plus"></i> Deposit</a>
      </div>
      <p class="ovm-pl">Today's P&amp;L <span class="{{ $net < 0 ? 'neg' : 'pos' }}">{{ $net < 0 ? '−' : '+' }}{{ number_format(abs($net) * $qr) }} Q</span></p>
    </section>

    <nav class="ovm-grid" aria-label="Services">
      @foreach($ovGrid as $q)
      <a href="{{ $q[1] }}" class="dg-it" aria-label="{{ $q[0] }}">
        <span class="dg-tile" style="background-image:{{ $q[3] }}"><i data-lucide="{{ $q[2] }}"></i></span>
        <b>{{ $q[0] }}</b>
      </a>
      @endforeach
    </nav>

@include('partials.ov-announcements')
@include('partials.ov-hot-investments')
    <div class="ovm-slide dg-glass">
      <i data-lucide="megaphone"></i>
      <div class="ovm-track"><span>{{ $gigCount }} open gigs waiting · {{ $listingCount }} listings live in Market · NaGolf: swing for Q coins today</span></div>
    </div>

    <div class="dg-grid">
      <div class="dg-stage-row">
        <section class="dg-stage" id="dgStage" aria-label="Your journey">
          <div class="dg-deck" id="dgDeck">
            @foreach($dgWorlds as $i => $w)
            <a href="{{ $w[2] }}" class="dg-jc" data-pos="{{ $i === 0 ? 0 : 1 }}" data-acc="{{ $w[5] }}">
              <span class="dg-jc-in">
                <img src="{{ $w[3] }}" alt="" draggable="false">
                <span class="dg-jc-ico"><i data-lucide="{{ $w[4] }}"></i></span>
                <b>{{ $w[0] }}</b><small>{{ $w[1] }}</small>
                <span class="dg-go"><i data-lucide="arrow-right"></i></span>
              </span>
            </a>
            @endforeach
          </div>
          <div class="dg-dots" aria-hidden="true">@foreach($dgWorlds as $i => $w)<i class="dg-dot {{ $i === 0 ? 'on' : '' }}"></i>@endforeach</div>
        </section>
      </div>
      <nav class="dg-quick dg-glass" aria-label="More">
        @foreach($ovRail as $q)
        <a href="{{ $q[1] }}" class="dg-it" aria-label="{{ $q[0] }}">
          <span class="dg-tile" style="background-image:{{ $q[3] }}"><i data-lucide="{{ $q[2] }}"></i></span>
          <b>{{ $q[0] }}</b>
        </a>
        @endforeach
      </nav>
    </div>

    <div class="dg-hlh">
      <div><h4>Today’s Highlights</h4><p>Something new, just for you.</p></div>
    </div>
    <link rel="stylesheet" href="{{ asset('css/dash-shade.css') }}?v={{ @filemtime(public_path('css/dash-shade.css')) }}">
    <div class="dg-cards">
      <a href="{{ route('gigs.index') }}" class="dg-card">
        <img src="{{ asset('images/bg-gigs.jpg') }}" alt="" loading="lazy">
        <span class="dg-chipi"><i data-lucide="flame"></i></span>
        <b>Trending Gigs</b><small>{{ $gigCount }} open gigs waiting for you.</small>
        <span class="dg-go"><i data-lucide="arrow-right"></i></span>
      </a>
      <a href="{{ route('marketplace.index') }}" class="dg-card">
        <img src="{{ asset('images/bg-marketplace.jpg') }}" alt="" loading="lazy">
        <span class="dg-chipi"><i data-lucide="shopping-bag"></i></span>
        <b>Top Products</b><small>{{ $listingCount }} listings available now.</small>
        <span class="dg-go"><i data-lucide="arrow-right"></i></span>
      </a>
      <a href="{{ $gvUrl }}" class="dg-card">
        <img src="{{ asset('images/golfvex-course.png') }}" alt="" loading="lazy">
        <span class="dg-chipi"><i data-lucide="flag"></i></span>
        <b>NaGolf</b><small>Swing for Q coins today.</small>
        <span class="dg-go"><i data-lucide="arrow-right"></i></span>
      </a>
    </div>

    @php
      $ovT = [
        ['discover', 'Discover', null, 'all'],
        ['news', 'News', null, 'news'],
        ['ads', 'Offers', null, 'ads'],
        ['upcoming', 'Upcoming', null, 'upcoming'],
        ['leaderboard', 'Leaderboard', route('leaderboard.index'), null],
      ];
    @endphp
    <nav class="ovm-tabs" id="ovmTabs" aria-label="Sections">
      @foreach($ovT as $i => $t)<a href="{{ $t[2] ?? '#' . $t[0] }}" class="{{ $i === 0 ? 'on' : '' }}" data-tab="{{ $t[0] }}" @if($t[3]) data-feed="{{ $t[3] }}" @endif @if($t[2]) data-url="{{ $t[2] }}" @endif>{{ $t[1] }}</a>@endforeach
    </nav>

    <div class="ovm-panel" id="ovmP-feed">
      @include('partials.dg-feed')
      @include('partials.dg-comments')
    </div>
    <div class="ovm-panel" id="ovmP-leaderboard" hidden></div>
  </div>
</div>

<script>
(function () {
  var bar = document.getElementById('ovmTabs'); if (!bar) return;
  var tabs = [].slice.call(bar.querySelectorAll('a'));
  function load(url, p) {
    p.innerHTML = '<div class="ovm-loading dg-glass">Loading…</div>';
    fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok || r.redirected) throw 0; return r.text(); })
      .then(function (html) {
        var d = new DOMParser().parseFromString(html, 'text/html');
        var src = d.querySelector('#appPushContent') || d.querySelector('.ws2-main') || d.querySelector('main') || d.body;
        [].slice.call(d.querySelectorAll('link[rel="stylesheet"]')).forEach(function (l) {
          var h = l.getAttribute('href');
          if (h && !document.querySelector('link[href="' + h + '"]')) { var n = document.createElement('link'); n.rel = 'stylesheet'; n.href = h; document.head.appendChild(n); }
        });
        [].slice.call(src.querySelectorAll('script,.nav-icons')).forEach(function (n) { n.remove(); });
        p.innerHTML = src.innerHTML; p.dataset.loaded = '1';
        if (window.lucide) lucide.createIcons();
      })
      .catch(function () { window.location.href = url; });
  }
  function show(a) {
    tabs.forEach(function (x) { x.classList.toggle('on', x === a); });
    var feed = document.getElementById('ovmP-feed'), lb = document.getElementById('ovmP-leaderboard');
    if (a.dataset.url) {
      feed.hidden = true; lb.hidden = false;
      if (!lb.dataset.loaded) load(a.dataset.url, lb);
    } else {
      lb.hidden = true; feed.hidden = false;
      var inner = feed.querySelector('.dgf-tab[data-tab="' + a.dataset.feed + '"]');
      if (inner) inner.click();
    }
    try { history.replaceState(null, '', '#' + a.dataset.tab); } catch (e) {}
  }
  tabs.forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a); }); });
  var h = (location.hash || '').replace('#', '');
  tabs.forEach(function (a) { if (h && a.dataset.tab === h) show(a); });
})();
</script>
<script>
(function () {
  var lb = document.getElementById('ovmP-leaderboard'), t = document.querySelector('#ovmTabs a[data-tab="leaderboard"]');
  if (!lb || !t) return;
  var base = t.getAttribute('href');
  function reload(url) {
    lb.innerHTML = '<div class="ovm-loading dg-glass">Loading…</div>';
    fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok) throw 0; return r.text(); })
      .then(function (html) {
        var d = new DOMParser().parseFromString(html, 'text/html');
        var src = d.querySelector('#appPushContent') || d.querySelector('main') || d.body;
        [].slice.call(src.querySelectorAll('script,.nav-icons')).forEach(function (n) { n.remove(); });
        lb.innerHTML = src.innerHTML; lb.dataset.loaded = '1';
        if (window.lucide) lucide.createIcons();
      })
      .catch(function () { window.location.href = url; });
  }
  lb.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('a') : null;
    if (a && a.href && a.href.indexOf(base.split('?')[0]) === 0) { e.preventDefault(); reload(a.href); }
  });
})();
</script>
@endsection
