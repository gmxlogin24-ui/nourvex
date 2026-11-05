@extends('layouts.app-admin')
@section('title', 'Coin Lab')
@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-home.css') }}?v={{ time() }}">
<main class="cl" id="coinSim">
  <h1 class="cl-h">Coin Lab <small>Q-coin economy</small></h1>
  <section class="cl-live" id="clLive"></section>
  <section class="cl-card">
    <h2>Simulator <small>nothing is saved</small></h2>
    <div class="cl-in" id="clIn"></div>
  </section>
  <section class="cl-card">
    <h2>Result</h2>
    <div class="cl-out" id="clOut"></div>
    <canvas id="clCv" class="cl-cv" height="220"></canvas>
    <div class="cl-leg"><span><s style="background:#0B0C10"></s>Coin liability (₦)</span><span><s style="background:#9A9DA5"></s>Cumulative revenue (₦)</span></div>
    <p class="cl-verdict" id="clV"></p>
  </section>
  <section class="cl-card">
    <h2>Last 30 days by type</h2>
    <div class="cl-types" id="clTy"></div>
  </section>
  <section class="cl-card">
    <h2>Current config <small>read only</small></h2>
    <div class="cl-types" id="clCfg"></div>
  </section>
</main>
<script>
(function () {
  var D = @json($data);
  var $ = function (i) { return document.getElementById(i); };
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function f(v) { v = Number(v) || 0; var a = Math.abs(v); return a >= 1e9 ? (v / 1e9).toFixed(2) + 'B' : a >= 1e6 ? (v / 1e6).toFixed(2) + 'M' : a >= 1e4 ? (v / 1e3).toFixed(1) + 'K' : Math.round(v * 100) / 100 + ''; }

  $('clLive').innerHTML = [['Q in wallets', f(D.supply)], ['Holders', f(D.holders)], ['Q txs 24h', f(D.tx24)], ['Q issued 24h', f(D.in24)], ['Q spent 24h', f(Math.abs(D.out24))], ['Rate', D.rate + ' Q = ₦1']]
    .map(function (x) { return '<div><small>' + x[0] + '</small><b>' + x[1] + '</b></div>'; }).join('');
  $('clTy').innerHTML = D.types.length ? D.types.map(function (t) { return '<div><span>' + esc(t.type || '—') + '</span><b>' + f(t.c) + ' txs · ' + f(t.s) + ' Q</b></div>'; }).join('') : '<p class="cm-dim">No Q-coin transactions yet.</p>';
  var cfg = Object.keys(D.cfg);
  $('clCfg').innerHTML = (cfg.length ? cfg.map(function (k) { return '<div><span>' + esc(k) + '</span><b>' + esc(String(D.cfg[k])) + '</b></div>'; }).join('') : '') + '<div><span>investment.platform_fee_percent</span><b>' + D.platformFee + '</b></div>';

  var cnt = [0, 0, 0, 0], tot = 0;
  Object.keys(D.tiers || {}).forEach(function (k) { var n = parseInt(k, 10), c = Number(D.tiers[k]) || 0; if (k !== '' && n >= 0 && n <= 3) { cnt[n] += c; tot += c; } });
  var mix = tot ? cnt.map(function (c) { return Math.round(c / tot * 100); }) : [100, 0, 0, 0];
  var spec = [
    ['rate', 'Q per ₦1 (rate)', D.rate, 1, 200, 1], ['users', 'Users today', D.users, 0, Math.max(1000, D.users * 20), 1],
    ['dau', 'Daily active %', 30, 1, 100, 1], ['grow', 'User growth % / week', 3, 0, 40, 0.5],
    ['m1', 'Tier 1 share %', mix[1], 0, 100, 1], ['m2', 'Tier 2 share %', mix[2], 0, 100, 1], ['m3', 'Tier 3 share %', mix[3], 0, 100, 1],
    ['d0', 'Daily Q, free tier', D.daily[0], 0, 200, 1], ['d1', 'Daily Q, tier 1', D.daily[1], 0, 200, 1], ['d2', 'Daily Q, tier 2', D.daily[2], 0, 200, 1], ['d3', 'Daily Q, tier 3', D.daily[3], 0, 200, 1],
    ['spend', 'Q spent back to platform %', 40, 0, 100, 1], ['avg', '₦ spent per active user / day', 500, 0, 20000, 50], ['fee', 'Platform fee %', D.platformFee, 0, 30, 0.5], ['days', 'Days to project', 90, 7, 365, 1]
  ];
  $('clIn').innerHTML = spec.map(function (s) {
    return '<label><span>' + s[1] + '<b id="v_' + s[0] + '">' + s[2] + '</b></span><input type="range" id="i_' + s[0] + '" min="' + s[3] + '" max="' + s[4] + '" step="' + s[5] + '" value="' + s[2] + '"></label>';
  }).join('');
  function val() { var o = {}; spec.forEach(function (s) { o[s[0]] = parseFloat($('i_' + s[0]).value) || 0; $('v_' + s[0]).textContent = $('i_' + s[0]).value; }); return o; }

  function calc() {
    var v = val(), m = [Math.max(0, 100 - v.m1 - v.m2 - v.m3), v.m1, v.m2, v.m3], avg = 0, i;
    for (i = 0; i < 4; i++) avg += m[i] / 100 * v['d' + i];
    var s = D.supply || 0, cm = 0, cr = 0, L = [], R = [], day1 = 0;
    for (var d = 1; d <= v.days; d++) {
      var act = v.users * Math.pow(1 + v.grow / 100, d / 7) * v.dau / 100, mint = act * avg;
      if (d === 1) day1 = mint;
      s += mint * (1 - v.spend / 100); cm += mint; cr += act * v.avg * v.fee / 100;
      L.push(s / v.rate); R.push(cr);
    }
    var cost = cm / v.rate, cov = cost > 0 ? cr / cost : 0;
    $('clOut').innerHTML = [['Q minted / day', f(day1)], ['Mint cost / day', '₦' + f(day1 / v.rate)], ['Supply at end', f(s) + ' Q'], ['Liability at end', '₦' + f(s / v.rate)], ['Revenue (period)', '₦' + f(cr)], ['Revenue ÷ mint cost', cov.toFixed(2) + '×']]
      .map(function (x) { return '<div><small>' + x[0] + '</small><b>' + x[1] + '</b></div>'; }).join('');
    $('clV').textContent = cost === 0 ? 'No coins are being minted with these settings.' : cov >= 1.5 ? 'Healthy: revenue covers coin issuance with room to spare.' : cov >= 1 ? 'Tight: revenue only just covers coin issuance. Watch growth.' : 'Danger: issuing coins costs more than you earn. Lower daily Q, raise the rate, or raise the spend sink.';
    $('clV').className = 'cl-verdict ' + (cov >= 1.5 ? 'ok' : cov >= 1 ? 'mid' : 'bad');
    chart(L, R);
  }
  function chart(L, R) {
    var c = $('clCv'), r = window.devicePixelRatio || 1, w = c.clientWidth, h = 220;
    c.width = w * r; c.height = h * r; var x = c.getContext('2d'); x.scale(r, r); x.clearRect(0, 0, w, h);
    var mx = Math.max.apply(null, L.concat(R).concat([1])), n = L.length;
    x.strokeStyle = 'rgba(15,17,21,.08)'; x.lineWidth = 1;
    for (var g = 0; g < 5; g++) { var yy = 10 + (h - 30) * g / 4; x.beginPath(); x.moveTo(0, yy); x.lineTo(w, yy); x.stroke(); }
    [[L, '#0B0C10'], [R, '#9A9DA5']].forEach(function (a) {
      x.strokeStyle = a[1]; x.lineWidth = 2.2; x.beginPath();
      a[0].forEach(function (y, i) { var px = n > 1 ? i / (n - 1) * (w - 4) + 2 : 2, py = h - 20 - y / mx * (h - 30); if (i) x.lineTo(px, py); else x.moveTo(px, py); });
      x.stroke();
    });
    x.fillStyle = 'rgba(15,17,21,.55)'; x.font = '11px system-ui'; x.fillText('₦' + f(mx), 4, 12); x.fillText('day ' + n, w - 52, h - 4);
  }
  $('clIn').addEventListener('input', calc);
  window.addEventListener('resize', calc);
  calc();
})();
</script>
@endsection
