@extends('layouts.app-authenticated')
@section('title', 'Overview')

@section('content')
@php
  $n = count($flow);
  $maxAbs = max(1, max(array_map('abs', $flow)));
  $pts = [];
  foreach ($flow as $i => $v) { $pts[] = [round($i * (300 / ($n - 1)), 1), round(34 - ($v / $maxAbs) * 26, 1)]; }
  $linePath = 'M' . $pts[0][0] . ',' . $pts[0][1];
  for ($i = 1; $i < $n; $i++) {
    $cx = round(($pts[$i - 1][0] + $pts[$i][0]) / 2, 1);
    $linePath .= ' C' . $cx . ',' . $pts[$i - 1][1] . ' ' . $cx . ',' . $pts[$i][1] . ' ' . $pts[$i][0] . ',' . $pts[$i][1];
  }
  $areaPath = $linePath . ' L300,70 L0,70 Z';
  $net = $weekIn - $weekOut;
@endphp
<style>
  .ov { max-width:640px; margin:0 auto; padding:10px 14px 170px; color:#f2f4f3; font-size:12px; overflow-x:hidden; }
  .od { display:inline-flex; line-height:1; font-variant-numeric:tabular-nums; }
  .od-w { display:inline-block; height:1em; overflow:hidden; line-height:1; }
  .od-s { display:block; }
  .od-s i { display:block; height:1em; font-style:normal; line-height:1; }
  .od-c { display:inline-block; line-height:1; }

  .ov-head small { display:block; font-size:9px; letter-spacing:.16em; color:#8b948f; }
  .ov-bal { font-size:46px; font-weight:800; letter-spacing:-.03em; margin:8px 0 10px; line-height:1; }
  .ov-delta { display:flex; align-items:center; gap:8px; font-size:10px; font-weight:700; }
  .ov-delta .up { color:#b6f03c; } .ov-delta .dn { color:#ff8fa3; }
  .ov-delta em { font-style:normal; font-weight:500; color:#8b948f; }

  .ov-pulse { margin-top:16px; border-radius:20px; padding:12px 12px 8px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); }
  .ov-ph { display:flex; justify-content:space-between; font-size:9px; letter-spacing:.1em; text-transform:uppercase; color:#8b948f; margin-bottom:2px; }
  .ov-ph b { color:#fff; letter-spacing:0; }
  .ov-pulse svg { width:100%; height:auto; display:block; overflow:visible; }
  .ov-days { display:flex; justify-content:space-between; font-size:8.5px; color:#6f7873; padding:2px 1px 0; }

  .ov-deckwrap { margin-top:18px; }
  .ov-deck { position:relative; height:168px; }
  .dk { position:absolute; left:0; right:0; top:0; height:132px; border-radius:22px; padding:14px 16px; background:var(--bg); color:#10140a;
        background-image:linear-gradient(135deg, rgba(255,255,255,.4), rgba(255,255,255,0) 50%);
        box-shadow:0 16px 30px -16px rgba(0,0,0,.7); user-select:none; -webkit-user-select:none; cursor:grab; overflow:hidden; }
  .dk-top { display:flex; justify-content:space-between; align-items:center; font-size:9.5px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
  .dk-top span { display:flex; align-items:center; gap:6px; }
  .dk-top em { font-style:normal; font-size:8.5px; font-weight:700; opacity:.55; letter-spacing:.08em; }
  .dk-big { font-size:38px; font-weight:800; letter-spacing:-.03em; margin-top:16px; line-height:1; }
  .dk-sub { font-size:10px; font-weight:600; opacity:.65; margin-top:7px; }
  .dk-ghost { position:absolute; right:-12px; bottom:-18px; width:96px; height:96px; opacity:.13; }
  .ov-hint { text-align:center; font-size:9px; letter-spacing:.1em; text-transform:uppercase; color:#6f7873; margin-top:4px; }

  .ov-svc { display:grid; grid-template-columns:repeat(4,1fr); gap:6px; margin-top:20px; }
  .ov-svc a { display:flex; flex-direction:column; align-items:center; gap:6px; text-decoration:none; color:#d9dedb; font-size:9.5px; font-weight:600; }
  .ov-svc a span { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); }

  .ov-stats { display:grid; grid-template-columns:repeat(3,1fr); margin-top:22px; border-top:1px solid rgba(255,255,255,.08); padding-top:14px; }
  .ov-stat { text-decoration:none; color:#fff; padding:0 0 0 12px; border-left:1px solid rgba(255,255,255,.08); }
  .ov-stat:first-child { border-left:0; padding-left:0; }
  .ov-stat small { display:block; font-size:8.5px; letter-spacing:.14em; color:#8b948f; margin-bottom:7px; }
  .ov-stat b { font-size:24px; font-weight:800; letter-spacing:-.02em; }

  .ov-wipe { position:fixed; inset:0; z-index:90; pointer-events:none; clip-path:circle(0px at 0 0); }
  .ov-coin { position:fixed; left:-64px; bottom:176px; width:46px; height:46px; z-index:40; perspective:300px; touch-action:none; cursor:grab; }
  .ov-coin-in { width:100%; height:100%; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#1b1540;
                background:radial-gradient(circle at 30% 25%, #e4dcff, #b3a6ff 55%, #7d6ad8); box-shadow:0 0 18px rgba(179,166,255,.6), inset 0 0 0 3px rgba(0,0,0,.12); }
  .ov-cord { position:fixed; right:24px; top:-170px; width:40px; height:170px; z-index:40; }
  .ov-cord-str { position:absolute; left:19px; top:0; width:2px; height:120px; background:linear-gradient(#b6f03c, rgba(182,240,60,.35)); }
  .ov-cord-knob { position:absolute; left:0; top:118px; width:40px; height:40px; border-radius:13px; display:flex; align-items:center; justify-content:center;
                  color:#10140a; background:linear-gradient(135deg, #d6ff7a, #b6f03c); box-shadow:0 0 18px rgba(182,240,60,.55); touch-action:none; cursor:grab; }
  .ov-shut { position:fixed; inset:0; z-index:95; display:flex; pointer-events:none; }
  .ov-shut i { flex:1; transform:scaleY(0); transform-origin:top; }</style>

<div class="ov">
  <div class="ov-head">
    <small>TOTAL BALANCE</small>
    <div class="ov-bal" data-odo="₦{{ number_format($balance) }}" data-d=".1">₦{{ number_format($balance) }}</div>
    <div class="ov-delta"><span class="up">&#9650; ₦{{ number_format($weekIn) }}</span><span class="dn">&#9660; ₦{{ number_format($weekOut) }}</span><em>this week</em></div>
  </div>

  <div class="ov-pulse">
    <div class="ov-ph"><span>7-day flow</span><b>{{ $net >= 0 ? '+' : '-' }}₦{{ number_format(abs($net)) }}</b></div>
    <svg viewBox="0 0 300 70">
      <defs><linearGradient id="pg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#b6f03c" stop-opacity=".35"/><stop offset="1" stop-color="#b6f03c" stop-opacity="0"/></linearGradient></defs>
      <path id="pArea" d="{{ $areaPath }}" fill="url(#pg)"/>
      <path id="pLine" d="{{ $linePath }}" fill="none" stroke="#b6f03c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <circle id="pDot" cx="{{ $pts[0][0] }}" cy="{{ $pts[0][1] }}" r="3.5" fill="#fff" stroke="#b6f03c" stroke-width="2"/>
    </svg>
    <div class="ov-days">@foreach($labels as $l)<span>{{ $l }}</span>@endforeach</div>
  </div>

  <div class="ov-deckwrap">
    <div class="ov-deck" id="ovDeck">
      <div class="dk" data-href="{{ route('gigs.index') }}" data-color="#b6f03c" style="--bg:#b6f03c">
        <div class="dk-top"><span><i data-lucide="briefcase" style="width:13px;height:13px"></i>Hustles</span><em>world</em></div>
        <div class="dk-big" data-odo="{{ $openGigs }}" data-d=".5">{{ $openGigs }}</div>
        <div class="dk-sub">open now &middot; {{ $completedGigs }} completed</div>
        <i class="dk-ghost" data-lucide="briefcase" style="stroke-width:1.5"></i>
      </div>
      <div class="dk" data-href="{{ route('marketplace.index') }}" data-color="#b3a6ff" style="--bg:#b3a6ff">
        <div class="dk-top"><span><i data-lucide="shopping-bag" style="width:13px;height:13px"></i>Market</span><em>world</em></div>
        <div class="dk-big" data-odo="{{ $listings }}" data-d=".6">{{ $listings }}</div>
        <div class="dk-sub">your listings</div>
        <i class="dk-ghost" data-lucide="shopping-bag" style="stroke-width:1.5"></i>
      </div>
      <div class="dk" data-href="{{ route('wallet.index') }}" data-color="#5ee7f5" style="--bg:#5ee7f5">
        <div class="dk-top"><span><i data-lucide="wallet" style="width:13px;height:13px"></i>Wallet</span><em>open</em></div>
        <div class="dk-big" data-odo="₦{{ number_format($totalIn) }}" data-d=".7">₦{{ number_format($totalIn) }}</div>
        <div class="dk-sub">total income &middot; ₦{{ number_format($totalOut) }} spent</div>
        <i class="dk-ghost" data-lucide="wallet" style="stroke-width:1.5"></i>
      </div>
    </div>
    <p class="ov-hint">swipe a card away &middot; tap to enter</p>
  </div>

  <div class="ov-svc">
    <a href="{{ route('services.index') }}"><span><i data-lucide="smartphone" style="width:17px;height:17px"></i></span>Airtime</a>
    <a href="{{ route('services.index') }}"><span><i data-lucide="wifi" style="width:17px;height:17px"></i></span>Data</a>
    <a href="{{ route('services.index') }}"><span><i data-lucide="tv" style="width:17px;height:17px"></i></span>Cable</a>
    <a href="{{ route('services.index') }}"><span><i data-lucide="layout-grid" style="width:17px;height:17px"></i></span>More</a>
  </div>

  <div class="ov-stats">
    <a class="ov-stat" href="{{ route('leaderboard.index') }}"><small>RANK</small><b data-odo="{{ $rankPoints }}" data-d=".9">{{ $rankPoints }}</b></a>
    <a class="ov-stat" href="{{ route('qcoin.index') }}"><small>Q COIN</small><b data-odo="{{ number_format($qCoin) }}" data-d="1">{{ number_format($qCoin) }}</b></a>
    <a class="ov-stat" href="{{ route('qcoin.index') }}"><small>STREAK</small><b data-odo="{{ $streak }}" data-d="1.1">{{ $streak }}</b></a>
  </div>
</div>
<div class="ov-coin" id="ovCoin" data-go="{{ route('marketplace.index') }}"><div class="ov-coin-in" id="ovCoinIn"><i data-lucide="shopping-bag" style="width:20px;height:20px"></i></div></div>
<div class="ov-cord" id="ovCord" data-go="{{ route('gigs.index') }}"><i class="ov-cord-str" id="cordStr"></i><div class="ov-cord-knob" id="cordKnob"><i data-lucide="briefcase" style="width:18px;height:18px"></i></div></div>
<div class="ov-shut" id="ovShut"></div>
<div class="ov-wipe" id="ovWipe"></div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/Draggable.min.js"></script>
<script>
(function () {
  if (!window.gsap) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var canDrag = !!window.Draggable;
  if (canDrag) gsap.registerPlugin(Draggable);

  function odo(el) {
    var text = el.dataset.odo, delay = parseFloat(el.dataset.d || .2);
    el.setAttribute('aria-label', text);
    el.textContent = '';
    var box = document.createElement('span'); box.className = 'od'; el.appendChild(box);
    Array.from(text).forEach(function (ch) {
      if (/\d/.test(ch)) {
        var w = document.createElement('span'); w.className = 'od-w';
        var s = document.createElement('span'); s.className = 'od-s';
        for (var k = 0; k < 10; k++) { var i = document.createElement('i'); i.textContent = k; s.appendChild(i); }
        w.appendChild(s); box.appendChild(w);
        gsap.fromTo(s, { yPercent: 0 }, { yPercent: -10 * parseInt(ch, 10), duration: reduce ? 0 : 1.1 + Math.random() * .6, ease: 'expo.out', delay: reduce ? 0 : delay });
      } else {
        var t = document.createElement('span'); t.className = 'od-c'; t.textContent = ch; box.appendChild(t);
      }
    });
  }
  document.querySelectorAll('[data-odo]').forEach(odo);

  var line = document.getElementById('pLine'), len = line.getTotalLength();
  gsap.set(line, { strokeDasharray: len, strokeDashoffset: reduce ? 0 : len });
  if (!reduce) {
    gsap.to(line, { strokeDashoffset: 0, duration: 1.6, delay: .45, ease: 'power2.inOut' });
    gsap.from('#pArea', { opacity: 0, duration: 1, delay: 1.4 });
    var dot = document.getElementById('pDot'), o = { p: 0 };
    gsap.set(dot, { opacity: 0 });
    gsap.to(o, { p: 1, duration: 2.6, delay: 2.1, repeat: -1, repeatDelay: 2.2, ease: 'power1.inOut',
      onUpdate: function () { var pt = line.getPointAtLength(o.p * len); dot.setAttribute('cx', pt.x); dot.setAttribute('cy', pt.y); gsap.set(dot, { opacity: 1 }); },
      onRepeat: function () { gsap.set(dot, { opacity: 0 }); } });

    gsap.from('.ov-head small, .ov-delta', { y: 12, opacity: 0, duration: .6, stagger: .08, ease: 'power3.out' });
    gsap.from('.ov-pulse', { y: 18, opacity: 0, duration: .7, delay: .2, ease: 'power3.out' });
    gsap.from('.ov-svc a', { scale: .6, opacity: 0, duration: .5, stagger: .06, delay: .9, ease: 'back.out(2)' });
    gsap.from('.ov-stat', { y: 10, opacity: 0, duration: .5, stagger: .07, delay: 1, ease: 'power2.out' });
  }

  var order = [].slice.call(document.querySelectorAll('.dk'));
  function layout(animate) {
    order.forEach(function (c, i) {
      c.style.pointerEvents = i === 0 ? 'auto' : 'none';
      gsap.to(c, { x: 0, y: i * 12, scale: 1 - i * .055, rotation: 0, opacity: i < 3 ? 1 : 0, zIndex: order.length - i,
        duration: animate && !reduce ? .5 : 0, ease: 'back.out(1.5)', overwrite: true });
    });
  }
  layout(false);

  function open(c, ev) {
    var p = ev ? (ev.changedTouches ? ev.changedTouches[0] : ev) : null;
    var x = (p && p.clientX) || innerWidth / 2, y = (p && p.clientY) || innerHeight / 2;
    var w = document.getElementById('ovWipe'); w.style.background = c.dataset.color;
    gsap.fromTo(w, { clipPath: 'circle(0px at ' + x + 'px ' + y + 'px)' },
      { clipPath: 'circle(150% at ' + x + 'px ' + y + 'px)', duration: reduce ? 0 : .6, ease: 'power2.in', onComplete: function () { location.href = c.dataset.href; } });
  }

  function fling(c, dir) {
    gsap.timeline()
      .to(c, { x: dir * innerWidth, rotation: dir * 18, opacity: 0, duration: .35, ease: 'power2.in', overwrite: true })
      .add(function () { gsap.set(c, { x: 0, rotation: 0, opacity: 0 }); order.push(order.shift()); layout(true); });
  }

  if (canDrag) {
    order.forEach(function (c) {
      Draggable.create(c, { type: 'x', minimumMovement: 6, edgeResistance: .9,
        onDrag: function () { gsap.set(c, { rotation: this.x / 18 }); },
        onDragEnd: function () {
          if (Math.abs(this.x) > 70 || Math.abs(this.deltaX) > 14) fling(c, (this.x || this.deltaX) > 0 ? 1 : -1);
          else gsap.to(c, { x: 0, rotation: 0, duration: .6, ease: 'elastic.out(1,.6)', overwrite: true });
        },
        onClick: function () { open(c, this.pointerEvent); } });
    });
  } else {
    order.forEach(function (c) { c.addEventListener('click', function (e) { open(c, e); }); });
  }

  if (!reduce) gsap.to(order[0], { x: 18, rotation: 1.2, duration: .3, yoyo: true, repeat: 1, delay: 2.4, ease: 'power1.inOut' });
  var coin = document.getElementById('ovCoin'), cord = document.getElementById('ovCord');
  var knob = document.getElementById('cordKnob'), str = document.getElementById('cordStr');
  var shut = document.getElementById('ovShut'), strips = [];
  ['#b6f03c', '#a5de2c', '#b6f03c', '#a5de2c', '#b6f03c', '#a5de2c'].forEach(function (col) {
    var s = document.createElement('i'); s.style.background = col; shut.appendChild(s); strips.push(s);
  });

  function wipeCircle(url, color, x, y) {
    var w = document.getElementById('ovWipe'); w.style.background = color;
    gsap.fromTo(w, { clipPath: 'circle(0px at ' + x + 'px ' + y + 'px)' },
      { clipPath: 'circle(150% at ' + x + 'px ' + y + 'px)', duration: reduce ? 0 : .6, ease: 'power2.in', onComplete: function () { location.href = url; } });
  }
  function shutters(url) {
    gsap.timeline({ onComplete: function () { location.href = url; } })
      .to(strips, { scaleY: 1, duration: reduce ? 0 : .45, stagger: reduce ? 0 : .07, ease: 'power3.in' });
  }

  var tl = null;
  if (!reduce) {
    gsap.to('#ovCoinIn', { rotationY: 360, duration: 2.2, repeat: -1, ease: 'none' });
    tl = gsap.timeline({ repeat: -1, repeatDelay: 2.5, delay: 1.8 });
    tl.to(coin, { x: 84, duration: .55, ease: 'back.out(1.6)' })
      .to(coin, { x: 0, duration: .5, ease: 'power2.in' }, '+=4')
      .to(cord, { y: 165, duration: .8, ease: 'elastic.out(1,.5)' }, '+=.6')
      .to(cord, { y: 0, duration: .5, ease: 'power2.in' }, '+=4.5');
  } else {
    gsap.set(coin, { x: 84 }); gsap.set(cord, { y: 165 });
  }

  var sx = 0, down = false;
  coin.addEventListener('pointerdown', function (e) { sx = e.clientX; down = true; if (tl) tl.pause(); coin.setPointerCapture(e.pointerId); });
  coin.addEventListener('pointerup', function (e) {
    if (!down) return; down = false;
    var dx = e.clientX - sx;
    if (dx > 24 || Math.abs(dx) < 6) {
      var r = coin.getBoundingClientRect();
      wipeCircle(coin.dataset.go, '#b3a6ff', r.left + r.width / 2, r.top + r.height / 2);
    } else if (tl) tl.resume();
  });
  coin.addEventListener('pointercancel', function () { down = false; if (tl) tl.resume(); });

  function resetKnob() {
    gsap.to(knob, { y: 0, duration: .7, ease: 'elastic.out(1,.45)' });
    gsap.to(str, { height: 120, duration: .7, ease: 'elastic.out(1,.45)' });
  }
  if (canDrag) {
    Draggable.create(knob, { type: 'y', bounds: { minY: 0, maxY: 120 }, edgeResistance: .85,
      onPress: function () { if (tl) tl.pause(); },
      onDrag: function () { gsap.set(str, { height: 120 + this.y }); },
      onDragEnd: function () {
        if (this.y > 70) { shutters(cord.dataset.go); }
        else { resetKnob(); if (tl) gsap.delayedCall(1.5, function () { tl.resume(); }); }
      },
      onClick: function () { shutters(cord.dataset.go); } });
  } else {
    knob.addEventListener('click', function () { shutters(cord.dataset.go); });
  }
  window.addEventListener('pageshow', function () { gsap.set(strips, { scaleY: 0 }); });
  window.addEventListener('pageshow', function () { gsap.set('#ovWipe', { clipPath: 'circle(0px at 0 0)' }); });
})();
</script>
@endsection
