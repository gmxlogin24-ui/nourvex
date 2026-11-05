@extends('layouts.app-admin')
@section('title', 'Studio')
@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-studio.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/admin-overview.css') }}?v={{ time() }}">
@php
  $ini = fn ($s) => strtoupper(substr(trim((string) $s) ?: 'U', 0, 1));
  $sumTx = (int) $txSeries->sum(); $sumSg = (int) $signups->sum(); $sumVol = (float) $volSeries->sum();
  $sTotal = max(1, $subActive + $subPending + $subExpired + $subCancelled);
  $pUsers = $userCount ? round(($userCount - $bannedCount) / $userCount * 100) : 0;
  $pMk = $gigCount ? round($openGigCount / $gigCount * 100) : 0;
  $pPay = round($subActive / $sTotal * 100);
  $tm = max(1, (int) $tierCounts->max());
@endphp
<div class="stu" id="stRoot">
  <i class="st-stone st-s1"></i><i class="st-stone st-s2"></i><i class="st-stone st-s3"></i>
  <div class="st-panel">
    <div class="st-shell">

      <aside class="st-rail">
        <a class="st-logo on" href="{{ $links['studio'] }}" title="Studio"><i data-lucide="layout-dashboard"></i></a>
        <nav>
          @foreach($tabs->take(8) as $t)
          <a href="{{ $t['url'] }}" title="{{ $t['label'] }}"><i data-lucide="{{ $t['icon'] }}"></i><span>{{ $t['label'] }}</span></a>
          @endforeach
        </nav>
        <div class="st-me">
          <i>{{ $ini($me->name ?? 'A') }}</i>
          <b>{{ \Illuminate\Support\Str::limit($me->name ?? 'Admin', 12) }}</b>
          <span>Admin</span>
        </div>
      </aside>

      <main class="st-main">
        <div class="st-top">
          <h1>Studio<small>Admin overview</small></h1>
          <div class="st-pills">
            <div class="st-pill"><i data-lucide="calendar"></i><span>{{ now()->format('d M Y') }}</span></div>
            <button type="button" class="st-pill st-search" onclick="var k=document.getElementById('axK');if(k)k.click();"><i data-lucide="search"></i><span>Search pages</span></button>
            <div class="ov-sw"><a href="{{ $links['map'] }}">Map</a><a class="on">Studio</a></div>
          </div>
        </div>

        <div class="st-grid">

          <section class="st-card st-gantt">
            <h2>Live timeline <small>latest activity</small></h2>
            @forelse($gantt as $g)
            <div class="st-gr">
              <span class="st-gt">{{ $g['time'] }}</span>
              <div class="st-gtr"><div class="st-gp {{ $g['k'] }}" style="left:{{ $g['left'] }}%"><em>{{ \Illuminate\Support\Str::limit($g['text'], 34) }}</em><i>{{ $g['ini'] }}</i></div></div>
            </div>
            @empty
            <p class="st-none">No activity yet.</p>
            @endforelse
            <div class="st-leg"><span><u class="d"></u>Payments</span><span><u class="m"></u>Joined</span><span><u class="l"></u>Gigs / listings</span></div>
          </section>

          <section class="st-card st-hero">
            <h2>Activity <small>14 days</small></h2>
            <div class="st-big">{{ $txPct >= 0 ? '+' : '' }}{{ $txPct }}<em>%</em></div>
            <p class="st-sub">Transactions vs previous week · {{ $txWeek }} this week</p>
            <div class="st-cv"><canvas id="stLine"></canvas></div>
            <div class="st-dots">
              <div><u style="background:#0B0C10"></u>Volume<b>₦{{ number_format($sumVol, 0) }}</b></div>
              <div><u style="background:#9A9DA5"></u>Signups<b>{{ $sumSg }}</b></div>
              <div><u style="background:#C9CCD2"></u>Transactions<b>{{ $sumTx }}</b></div>
            </div>
          </section>

          <section class="st-card st-dark st-pulse">
            <h2>Platform pulse <small>live</small></h2>
            <div class="st-orb"><i></i><i></i><i></i><b></b></div>
            <p class="st-sub">Wallets total</p>
            <div class="st-pv">₦{{ number_format($totalWalletBalance, 2) }}</div>
            <div class="st-av">
              @foreach($recentUsers->take(4) as $u)<i>{{ $ini($u->name) }}</i>@endforeach
              <span>{{ $userCount }} users</span>
            </div>
          </section>

          <section class="st-card st-files">
            <h2>Recent users <a href="{{ $links['users'] }}" class="st-plus">+</a></h2>
            @foreach($recentUsers->take(5) as $u)
            <button type="button" class="st-f bn-u" data-name="{{ $u->name }}" data-email="{{ $u->email }}" data-tier="{{ $u->tier }}" data-bal="₦{{ number_format($u->wallet_balance, 2) }}" data-joined="{{ $u->created_at?->format('d M Y') }}" data-banned="{{ $u->is_banned ? 1 : 0 }}">
              <i>{{ $ini($u->name) }}</i>
              <b>{{ \Illuminate\Support\Str::limit($u->name, 16) }}</b>
              <span>{{ $u->tier }}{{ $u->is_banned ? ' · banned' : '' }}</span>
            </button>
            @endforeach
          </section>

          <div class="st-right">
            <section class="st-card st-team">
              <h2>Statistics <small>14 days</small></h2>
              <div class="st-tm">
                <div><small>Transactions</small><b>{{ $sumTx }}</b><svg class="sp" data-spark='@json($txSeries)'></svg></div>
                <div><small>Signups</small><b>{{ $sumSg }}</b><svg class="sp" data-spark='@json($signups)'></svg></div>
                <div><small>Volume</small><b>₦{{ number_format($sumVol, 0) }}</b><svg class="sp" data-spark='@json($volSeries)'></svg></div>
              </div>
            </section>

            <section class="st-card st-dark st-cols">
              <div>
                <h3>Users</h3><p>Verified &amp; active</p>
                <div class="st-av">@foreach($recentUsers->take(3) as $u)<i>{{ $ini($u->name) }}</i>@endforeach</div>
                <div class="st-pr"><span>Progress</span><b>{{ $pUsers }}%</b></div><div class="st-pb"><u style="width:{{ $pUsers }}%"></u></div>
                <div class="st-ct"><span><i data-lucide="users"></i>{{ $userCount }}</span><span><i data-lucide="ban"></i>{{ $bannedCount }}</span></div>
              </div>
              <div>
                <h3>Marketplace</h3><p>Open gigs &amp; listings</p>
                <div class="st-av">@foreach($pinGigs->take(3) as $g)<i>G</i>@endforeach</div>
                <div class="st-pr"><span>Open</span><b>{{ $pMk }}%</b></div><div class="st-pb"><u style="width:{{ $pMk }}%"></u></div>
                <div class="st-ct"><span><i data-lucide="briefcase"></i>{{ $openGigCount }}</span><span><i data-lucide="store"></i>{{ $listingCount }}</span></div>
              </div>
              <div>
                <h3>Payments</h3><p>Subscriptions</p>
                <div class="st-av">@foreach($pinTx->take(3) as $x)<i>{{ $ini($x->user->name ?? 'U') }}</i>@endforeach</div>
                <div class="st-pr"><span>Active</span><b>{{ $pPay }}%</b></div><div class="st-pb"><u style="width:{{ $pPay }}%"></u></div>
                <div class="st-ct"><span><i data-lucide="check-circle"></i>{{ $subActive }}</span><span><i data-lucide="clock"></i>{{ $subPending }}</span></div>
              </div>
            </section>
          </div>

          <section class="st-card st-mk">
            <h2>Marketplace activity <small>gigs &amp; listings, 14 days</small></h2>
            <div class="st-cv st-cv2"><canvas id="stMk"></canvas></div>
          </section>

          <section class="st-card st-dn">
            <h2>Subscriptions</h2>
            <div class="st-cv st-cv3"><canvas id="stDn"></canvas></div>
            <div class="st-leg col"><span><u style="background:#0B0C10"></u>{{ $subActive }} active</span><span><u style="background:#9A9DA5"></u>{{ $subPending }} pending</span><span><u style="background:#C9CCD2"></u>{{ $subExpired }} expired</span><span><u style="background:#E4E5E8"></u>{{ $subCancelled }} cancelled</span></div>
          </section>

          <section class="st-card st-att">
            <h2>Needs attention</h2>
            @foreach($attention as $a)
            <a href="{{ $a['url'] }}" class="ms-q {{ $a['level'] }}"><em>{{ $a['count'] }}</em><div><b>{{ $a['label'] }}</b><span>{{ $a['hint'] }}</span></div><i>→</i></a>
            @endforeach
          </section>

          <section class="st-card st-isoc">
            <h2>Platform floors <small>live map of the platform</small></h2>
            <div class="st-iso">
              <div class="st-tabs">
                <small>Tabs</small>
                @foreach($tabs as $t)<a href="{{ $t['url'] }}"><i data-lucide="{{ $t['icon'] }}"></i>{{ $t['label'] }}</a>@endforeach
              </div>
              <div class="st-scene">
                <div class="st-live">
                  <b>Lobby <small>{{ $recentUsers->count() }} recent</small></b>
                  @foreach($recentUsers->take(2) as $u)
                  <div><i>{{ $ini($u->name) }}</i><span>{{ \Illuminate\Support\Str::limit($u->name, 14) }}</span></div>
                  @endforeach
                </div>
                <div class="st-world">
                  <div class="st-floor f1"><div class="st-plane"><em>1st floor · Users</em>
                    @php $up = [[22,30],[55,22],[72,52],[40,64],[18,70]]; @endphp
                    @foreach($pinUsers as $i => $u)<div class="st-pin" style="left:{{ $up[$i][0] }}%;top:{{ $up[$i][1] }}%"><i>{{ $ini($u->name) }}</i></div>@endforeach
                  </div></div>
                  <div class="st-floor f2"><div class="st-plane"><em>2nd floor · Marketplace</em>
                    @php $gp = [[24,34],[58,26],[70,60],[34,68],[48,46],[80,30]]; $mk = $pinGigs->map(fn ($g) => 'G')->concat($pinListings->map(fn ($l) => 'L')); @endphp
                    @foreach($mk as $i => $c)<div class="st-pin" style="left:{{ $gp[$i][0] }}%;top:{{ $gp[$i][1] }}%"><i>{{ $c }}</i></div>@endforeach
                  </div></div>
                  <div class="st-floor f3"><div class="st-plane"><em>Reception · Payments</em>
                    @php $tp = [[26,36],[60,30],[66,64],[34,66]]; @endphp
                    @foreach($pinTx as $i => $x)<div class="st-pin" style="left:{{ $tp[$i][0] }}%;top:{{ $tp[$i][1] }}%"><i>{{ $ini($x->user->name ?? 'U') }}</i></div>@endforeach
                  </div></div>
                </div>
              </div>
            </div>
          </section>

        </div>
      </main>
    </div>
  </div>

  <div class="bn-sh" id="bnSh">
    <div class="bn-sh-bk"></div>
    <div class="bn-sh-pn">
      <i id="bnShA"></i>
      <h3 id="bnShN"></h3>
      <p id="bnShE"></p>
      <div class="bn-sh-g"><div><small>Tier</small><b id="bnShT"></b></div><div><small>Balance</small><b id="bnShB"></b></div><div><small>Joined</small><b id="bnShJ"></b></div><div><small>Status</small><b id="bnShS"></b></div></div>
      <a href="{{ $links['users'] }}" class="bn-go">Open Users</a>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
(function () {
  var L = @json($labels), SG = @json($signups), TX = @json($txSeries), VOL = @json($volSeries), GG = @json($gigSeries), LS = @json($listSeries);
  var SUB = [{{ $subActive }}, {{ $subPending }}, {{ $subExpired }}, {{ $subCancelled }}];
  function grad(c, a, b) { var g = c.getContext('2d').createLinearGradient(0, 0, 0, c.height || 160); g.addColorStop(0, a); g.addColorStop(1, b); return g; }
  var axis = { x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 7 } }, y: { beginAtZero: true, ticks: { precision: 0, font: { size: 9 }, maxTicksLimit: 4 }, grid: { color: 'rgba(15,17,21,.06)' } } };
  function ds(label, data, color, fill, extra) { var o = { label: label, data: data, borderColor: color, backgroundColor: fill, tension: .42, pointRadius: 0, pointHoverRadius: 4, borderWidth: 2.4, fill: !!fill }; for (var k in (extra || {})) o[k] = extra[k]; return o; }
  if (window.Chart) {
    var a = document.getElementById('stLine');
    if (a) new Chart(a, { type: 'line', data: { labels: L, datasets: [ ds('Volume', VOL, '#0B0C10', grad(a, 'rgba(11,12,16,.22)', 'rgba(11,12,16,0)')), ds('Signups', SG, '#9A9DA5', null, { yAxisID: 'y1', borderDash: [5, 4], borderWidth: 2 }) ] },
      options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { display: false } }, scales: { x: axis.x, y: axis.y, y1: { display: false, beginAtZero: true } } } });
    var b = document.getElementById('stMk');
    if (b) new Chart(b, { type: 'line', data: { labels: L, datasets: [ ds('Gigs', GG, '#0B0C10', grad(b, 'rgba(11,12,16,.18)', 'rgba(11,12,16,0)')), ds('Listings', LS, '#9A9DA5', grad(b, 'rgba(154,157,165,.22)', 'rgba(154,157,165,0)')) ] },
      options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { display: false } }, scales: axis } });
    var c = document.getElementById('stDn');
    if (c) new Chart(c, { type: 'doughnut', data: { labels: ['Active', 'Pending', 'Expired', 'Cancelled'], datasets: [{ data: SUB, backgroundColor: ['#0B0C10', '#9A9DA5', '#C9CCD2', '#E4E5E8'], borderWidth: 3, borderColor: '#fff', hoverOffset: 4 }] },
      options: { maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } } });
  }
  document.querySelectorAll('svg.sp').forEach(function (s) {
    var d = JSON.parse(s.getAttribute('data-spark') || '[]'); if (d.length < 2) return;
    var w = 140, h = 40, m = Math.max.apply(null, d) || 1, p = d.map(function (v, i) { return [i / (d.length - 1) * w, h - 4 - (v / m) * (h - 10)]; });
    var path = 'M' + p[0][0] + ' ' + p[0][1];
    for (var i = 1; i < p.length; i++) { var q = p[i - 1], r = p[i], mx = (q[0] + r[0]) / 2; path += ' C' + mx + ' ' + q[1] + ' ' + mx + ' ' + r[1] + ' ' + r[0] + ' ' + r[1]; }
    s.setAttribute('viewBox', '0 0 ' + w + ' ' + h); s.setAttribute('preserveAspectRatio', 'none');
    s.innerHTML = '<path d="' + path + '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="' + p[p.length - 1][0] + '" cy="' + p[p.length - 1][1] + '" r="3.2" fill="currentColor"/>';
  });
  var sh = document.getElementById('bnSh'); function g(i) { return document.getElementById(i); }
  document.querySelectorAll('.bn-u').forEach(function (b) {
    b.addEventListener('click', function () {
      g('bnShA').textContent = b.dataset.name.charAt(0).toUpperCase(); g('bnShN').textContent = b.dataset.name; g('bnShE').textContent = b.dataset.email;
      g('bnShT').textContent = b.dataset.tier; g('bnShB').textContent = b.dataset.bal; g('bnShJ').textContent = b.dataset.joined; g('bnShS').textContent = b.dataset.banned === '1' ? 'Banned' : 'Active';
      sh.classList.add('on');
    });
  });
  sh.querySelector('.bn-sh-bk').addEventListener('click', function () { sh.classList.remove('on'); });
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection
