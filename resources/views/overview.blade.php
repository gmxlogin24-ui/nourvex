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
  $mood = $net > 0 ? 162 : ($net < 0 ? 132 : 148);
  $bubble = $net > 0 ? 'Up ₦' . number_format($net) . ' this week' : ($net < 0 ? 'Spent more than you earned this week' : 'Quiet week. Go earn something');
@endphp
<style>
  body[data-page="dashboard"], body[data-page="dashboard"] .app-shell-flex, body[data-page="dashboard"] .auth-page-shell,
  body[data-page="dashboard"] .app-push-content { background:transparent !important; }
  html:has(body[data-page="dashboard"]) { background:#E9F3FC; }
  body[data-page="dashboard"]::before { content:''; position:fixed; inset:0; z-index:-1; pointer-events:none;
    background:radial-gradient(60% 38% at 12% 0%, rgba(61,139,253,.28), transparent 70%),
               radial-gradient(50% 34% at 100% 52%, rgba(18,184,134,.20), transparent 70%),
               radial-gradient(55% 30% at 0% 100%, rgba(255,122,26,.18), transparent 70%); }

  .ov { --ink:#0F1115; --mute:#8C99A8; max-width:640px; margin:0 auto; padding:14px 16px 180px; color:var(--ink); font-size:12px; overflow-x:hidden; }
  .od { display:inline-flex; line-height:1; font-variant-numeric:tabular-nums; vertical-align:bottom; }
  .od-w { display:inline-block; height:1em; overflow:hidden; line-height:1; vertical-align:bottom; }
  .od-s { display:block; }
  .od-s i { display:block; height:1em; font-style:normal; line-height:1; }
  .od-c { display:inline-block; line-height:1; }

  .ov-title p { margin:0; font-size:26px; font-weight:800; line-height:1.06; letter-spacing:-.02em; text-transform:uppercase; }
  .ov-title .soft { color:#A9B7C6; }
  .ov-bal { display:inline-block; vertical-align:bottom; }

  .ov-panel { position:relative; height:250px; border-radius:28px; margin-top:16px; overflow:hidden; perspective:700px;
              background:linear-gradient(160deg, #CBE3FA, #E4F1FD); box-shadow:0 22px 44px -24px rgba(61,139,253,.7); }
  .ov-stage { position:absolute; inset:0; transform-style:preserve-3d; }
  .ov-plate { position:absolute; left:50%; top:44%; width:240px; height:240px; margin:-120px 0 0 -120px; border-radius:50%;
              background:radial-gradient(circle, #fff, rgba(255,255,255,0) 70%); }
  .ov-panel svg#char { position:absolute; left:50%; bottom:-66px; width:330px; height:344px; margin-left:-165px; opacity:0; }
  .ov-shine { position:absolute; inset:0; pointer-events:none; opacity:.7;
              background:linear-gradient(105deg, transparent 38%, rgba(255,255,255,.65) 50%, transparent 62%); }
  .ov-bubble { position:absolute; left:12px; bottom:14px; max-width:160px; background:#fff; border-radius:14px 14px 14px 4px; padding:8px 11px;
               font-size:10.5px; font-weight:700; line-height:1.25; box-shadow:0 10px 20px -10px rgba(15,17,21,.4); transform-origin:0% 100%; }

  .ov-flow { position:relative; overflow:hidden; margin-top:14px; padding:12px 14px 8px; border-radius:24px; background:#0F1115; color:#fff;
             box-shadow:0 20px 40px -18px rgba(182,240,60,.6); }
  .ov-flow:before { content:''; position:absolute; right:-50px; top:-60px; width:180px; height:180px; border-radius:50%;
                    background:radial-gradient(circle, rgba(182,240,60,.34), transparent 70%); pointer-events:none; }
  .ov-fl { position:relative; display:block; font-size:9px; letter-spacing:.12em; text-transform:uppercase; color:#8b948f; }
  .ov-fh { position:relative; display:flex; justify-content:space-between; align-items:center; margin:5px 0 2px; font-size:12px; font-weight:700; }
  .ov-io { display:flex; gap:12px; } .ov-io .up, .ov-fh .up { color:#b6f03c; } .ov-io .dn, .ov-fh .dn { color:#ff8fa3; }
  .ov-flow svg { position:relative; width:100%; height:auto; display:block; overflow:visible; }
  .ov-days { position:relative; display:flex; justify-content:space-between; font-size:8.5px; color:#6f7873; padding:2px 1px 0; }

  .ov-deck { position:relative; height:168px; margin-top:16px; }
  .dk { position:absolute; left:0; right:0; top:0; height:132px; border-radius:24px; padding:14px 16px; background:var(--bg); color:#fff;
        box-shadow:0 20px 36px -14px var(--glow); user-select:none; -webkit-user-select:none; cursor:grab; overflow:hidden; }
  .dk-top { display:flex; justify-content:space-between; align-items:center; font-size:9.5px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
  .dk-top span { display:flex; align-items:center; gap:6px; }
  .dk-top em { font-style:normal; font-size:8.5px; font-weight:700; opacity:.7; letter-spacing:.08em; }
  .dk-big { font-size:40px; font-weight:800; letter-spacing:-.03em; margin-top:16px; line-height:1; }
  .dk-sub { font-size:10px; font-weight:600; opacity:.85; margin-top:7px; }
  .dk-ghost { position:absolute; right:-10px; bottom:-18px; width:100px; height:100px; opacity:.16; }
  .ov-hint { text-align:center; font-size:9px; letter-spacing:.1em; text-transform:uppercase; color:var(--mute); margin:2px 0 0; }

  .ov-svc { margin-top:16px; display:grid; grid-template-columns:repeat(4,1fr); gap:6px; padding:14px 8px 11px; background:#fff; border-radius:26px;
            box-shadow:0 14px 28px -18px rgba(15,17,21,.4); }
  .ov-svc a { display:flex; flex-direction:column; align-items:center; gap:7px; text-decoration:none; color:var(--ink); font-size:9.5px; font-weight:700; }
  .ov-svc a span { width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#0F1115; color:#fff; }

  .ov-coin { position:fixed; left:-84px; bottom:176px; width:60px; height:60px; z-index:40; perspective:300px; touch-action:none; cursor:grab; }
  .ov-coin-in { width:100%; height:100%; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff;
                background:radial-gradient(circle at 30% 25%, #ffc27a, #FF7A1A 55%, #d85f00);
                box-shadow:0 0 26px rgba(255,122,26,.75), 0 12px 22px -6px rgba(255,122,26,.7), inset 0 0 0 3px rgba(255,255,255,.3); }
  .ov-cord { position:fixed; right:22px; top:-200px; width:54px; height:200px; z-index:40; }
  .ov-cord-str { position:absolute; left:25px; top:0; width:4px; height:140px; border-radius:4px; background:linear-gradient(#12B886, #5FE3C0); box-shadow:0 0 10px rgba(18,184,134,.7); }
  .ov-cord-knob { position:absolute; left:0; top:138px; width:54px; height:54px; border-radius:18px; display:flex; align-items:center; justify-content:center; color:#fff;
                  background:linear-gradient(135deg, #34D3A5, #0B9C73); box-shadow:0 0 28px rgba(18,184,134,.75), 0 12px 22px -8px rgba(11,122,90,.8);
                  touch-action:none; cursor:grab; }
  .ov-shut { position:fixed; inset:0; z-index:95; display:flex; pointer-events:none; }
  .ov-shut i { flex:1; transform:scaleY(0); transform-origin:top; }
  .ov-wipe { position:fixed; inset:0; z-index:90; pointer-events:none; clip-path:circle(0px at 0 0); }
</style>

<div class="ov">
  <div class="ov-title">
    <p>Hey {{ $firstName }},<br>you have <span class="ov-bal" data-odo="₦{{ number_format($balance) }}" data-d=".1">₦{{ number_format($balance) }}</span></p>
    <p class="soft">in your wallet right now</p>
  </div>

  <div class="ov-panel" id="ovPanel">
    <div class="ov-stage" id="ovStage">
      <div class="ov-plate" id="ovPlate"></div>
      <svg id="char" viewBox="0 0 240 250">
        <g id="cAll" stroke="#0F1115" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none">
          <path class="dr" d="M60,250 C60,208 84,184 108,180"/>
          <path class="dr" d="M180,250 C180,208 156,184 132,180"/>
          <path class="dr" d="M104,180 C110,192 130,192 136,180"/>
          <path class="dr" d="M108,162 L108,180"/>
          <path class="dr" d="M132,162 L132,180"/>
          <g id="cHead">
            <ellipse class="dr hd" cx="120" cy="118" rx="44" ry="46" fill="#fff"/>
            <path class="dr" d="M77,112 C62,108 62,134 79,133"/>
            <path class="dr" d="M163,112 C178,108 178,134 161,133"/>
            <path class="dr fl" d="M76,104 C72,62 98,38 122,38 C152,38 172,62 166,104 Z" fill="#0F1115"/>
            <path class="dr fl" d="M62,106 C102,92 152,92 198,110 C168,114 104,114 62,106 Z" fill="#0F1115"/>
            <path d="M100,66 L126,63" stroke="#fff" stroke-width="4"/>
            <ellipse id="eL" cx="104" cy="128" rx="3.6" ry="4.6" fill="#0F1115" stroke="none"/>
            <ellipse id="eR" cx="136" cy="128" rx="3.6" ry="4.6" fill="#0F1115" stroke="none"/>
            <path id="smile" d="M106,144 Q120,{{ $mood }} 134,144"/>
          </g>
        </g>
      </svg>
      <div class="ov-bubble" id="ovBubble">{{ $bubble }}</div>
    </div>
    <div class="ov-shine" id="ovShine"></div>
  </div>

  <div class="ov-flow">
    <small class="ov-fl">7-day flow</small>
    <div class="ov-fh">
      <div class="ov-io"><span class="up">&#9650; ₦{{ number_format($weekIn) }}</span><span class="dn">&#9660; ₦{{ number_format($weekOut) }}</span></div>
      <span class="{{ $net >= 0 ? 'up' : 'dn' }}">{{ $net >= 0 ? '+' : '-' }}₦{{ number_format(abs($net)) }}</span>
    </div>
    <svg viewBox="0 0 300 70">
      <defs>
        <linearGradient id="pg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#b6f03c" stop-opacity=".4"/><stop offset="1" stop-color="#b6f03c" stop-opacity="0"/></linearGradient>
        <filter id="glow" x="-20%" y="-50%" width="140%" height="200%"><feGaussianBlur stdDeviation="2.6" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
      </defs>
      <path id="pArea" d="{{ $areaPath }}" fill="url(#pg)"/>
      <path id="pLine" d="{{ $linePath }}" fill="none" stroke="#b6f03c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)"/>
      <circle id="pDot" cx="{{ $pts[0][0] }}" cy="{{ $pts[0][1] }}" r="3.8" fill="#fff" stroke="#b6f03c" stroke-width="2" filter="url(#glow)"/>
    </svg>
    <div class="ov-days">@foreach($labels as $l)<span>{{ $l }}</span>@endforeach</div>
  </div>

  <div class="ov-deck" id="ovDeck">
    <div class="dk" data-href="{{ route('gigs.index') }}" data-color="#12B886" style="--bg:linear-gradient(135deg,#34D3A5,#0B8F6B);--glow:rgba(18,184,134,.75)">
      <div class="dk-top"><span><i data-lucide="briefcase" style="width:13px;height:13px"></i>Hustles</span><em>enter</em></div>
      <div class="dk-big" data-odo="{{ number_format($gigCount) }}" data-d=".5">{{ number_format($gigCount) }}</div>
      <div class="dk-sub">open hustles right now</div>
      <i class="dk-ghost" data-lucide="briefcase" style="stroke-width:1.5"></i>
    </div>
    <div class="dk" data-href="{{ route('marketplace.index') }}" data-color="#FF7A1A" style="--bg:linear-gradient(135deg,#FFA24D,#FF6A00);--glow:rgba(255,122,26,.75)">
      <div class="dk-top"><span><i data-lucide="shopping-bag" style="width:13px;height:13px"></i>Campus Market</span><em>enter</em></div>
      <div class="dk-big" data-odo="{{ number_format($listingCount) }}" data-d=".6">{{ number_format($listingCount) }}</div>
      <div class="dk-sub">items on sale</div>
      <i class="dk-ghost" data-lucide="shopping-bag" style="stroke-width:1.5"></i>
    </div>
  </div>
  <p class="ov-hint">swipe a card away &middot; tap to enter</p>

  <div class="ov-svc">
    <a href="{{ route('services.index') }}"><span><i data-lucide="smartphone" style="width:18px;height:18px"></i></span>Airtime</a>
    <a href="{{ route('services.index') }}"><span><i data-lucide="wifi" style="width:18px;height:18px"></i></span>Data</a>
    <a href="{{ route('services.index') }}"><span><i data-lucide="tv" style="width:18px;height:18px"></i></span>Cable</a>
    <a href="{{ route('services.index') }}"><span><i data-lucide="layout-grid" style="width:18px;height:18px"></i></span>More</a>
  </div>
</div>

<div class="ov-coin" id="ovCoin" data-go="{{ route('marketplace.index') }}"><div class="ov-coin-in" id="ovCoinIn"><i data-lucide="shopping-bag" style="width:26px;height:26px"></i></div></div>
<div class="ov-cord" id="ovCord" data-go="{{ route('gigs.index') }}"><i class="ov-cord-str" id="cordStr"></i><div class="ov-cord-knob" id="cordKnob"><i data-lucide="briefcase" style="width:24px;height:24px"></i></div></div>
<div class="ov-shut" id="ovShut"></div>
<div class="ov-wipe" id="ovWipe"></div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/Draggable.min.js"></script>
<script>
(function () {
  var ch = document.getElementById('char'); if (ch) ch.style.opacity = 1;
  if (!window.gsap) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var canDrag = !!window.Draggable;
  if (canDrag) gsap.registerPlugin(Draggable);

  function odo(el) {
    var text = el.dataset.odo, delay = parseFloat(el.dataset.d || .2);
    el.setAttribute('aria-label', text); el.textContent = '';
    var box = document.createElement('span'); box.className = 'od'; el.appendChild(box);
    Array.from(text).forEach(function (c) {
      if (/\d/.test(c)) {
        var w = document.createElement('span'); w.className = 'od-w';
        var s = document.createElement('span'); s.className = 'od-s';
        for (var k = 0; k < 10; k++) { var i = document.createElement('i'); i.textContent = k; s.appendChild(i); }
        w.appendChild(s); box.appendChild(w);
        gsap.fromTo(s, { yPercent: 0 }, { yPercent: -10 * parseInt(c, 10), duration: reduce ? 0 : 1.1 + Math.random() * .6, ease: 'expo.out', delay: reduce ? 0 : delay });
      } else {
        var t = document.createElement('span'); t.className = 'od-c'; t.textContent = c; box.appendChild(t);
      }
    });
  }
  document.querySelectorAll('[data-odo]').forEach(odo);

  var line = document.getElementById('pLine'), len = line.getTotalLength();
  gsap.set(line, { strokeDasharray: len, strokeDashoffset: reduce ? 0 : len });

  if (!reduce) {
    gsap.to(line, { strokeDashoffset: 0, duration: 1.6, delay: .9, ease: 'power2.inOut' });
    gsap.from('#pArea', { opacity: 0, duration: 1, delay: 1.8 });
    var dot = document.getElementById('pDot'), o = { p: 0 };
    gsap.set(dot, { opacity: 0 });
    gsap.to(o, { p: 1, duration: 2.6, delay: 2.8, repeat: -1, repeatDelay: 2.4, ease: 'power1.inOut',
      onUpdate: function () { var pt = line.getPointAtLength(o.p * len); dot.setAttribute('cx', pt.x); dot.setAttribute('cy', pt.y); gsap.set(dot, { opacity: 1 }); },
      onRepeat: function () { gsap.set(dot, { opacity: 0 }); } });
    gsap.to('.ov-flow', { boxShadow: '0 24px 50px -16px rgba(182,240,60,.85)', duration: 2.2, yoyo: true, repeat: -1, ease: 'sine.inOut' });

    gsap.from('.ov-title p', { y: 14, opacity: 0, duration: .6, stagger: .1, ease: 'power3.out' });
    gsap.from('.ov-panel', { y: 18, opacity: 0, duration: .6, delay: .15, ease: 'power3.out' });
    gsap.from('.ov-flow', { y: 16, opacity: 0, duration: .6, delay: .7, ease: 'power3.out' });
    gsap.from('.ov-svc', { y: 16, opacity: 0, duration: .6, delay: 1.1, ease: 'power3.out' });
    gsap.from('.ov-svc a', { scale: .6, opacity: 0, duration: .5, stagger: .06, delay: 1.2, ease: 'back.out(2)' });

    // drawn character
    document.querySelectorAll('#char .dr').forEach(function (p) {
      var L = p.getTotalLength(); p.style.strokeDasharray = L; p.style.strokeDashoffset = L;
    });
    gsap.set('#char .fl, #char .hd', { attr: { 'fill-opacity': 0 } });
    gsap.set('#eL, #eR, #smile', { opacity: 0 });
    var ct = gsap.timeline({ delay: .35 });
    ct.to('#char .dr', { strokeDashoffset: 0, duration: .9, stagger: .11, ease: 'power2.inOut' })
      .to('#char .fl, #char .hd', { attr: { 'fill-opacity': 1 }, duration: .4 }, '-=.5')
      .to('#eL, #eR, #smile', { opacity: 1, duration: .3, stagger: .06 })
      .from('#ovBubble', { scale: 0, opacity: 0, duration: .5, ease: 'back.out(2)' }, '-=.1');
    var sm = { c: 148 }, smile = document.getElementById('smile');
    ct.to(sm, { c: {{ $mood }}, duration: .7, ease: 'back.out(2)',
      onUpdate: function () { smile.setAttribute('d', 'M106,144 Q120,' + sm.c + ' 134,144'); } }, '-=.4');
    ct.add(function () {
      gsap.to('#cHead', { y: -2, rotation: 2.2, svgOrigin: '120 165', duration: 2.4, yoyo: true, repeat: -1, ease: 'sine.inOut' });
      gsap.to('#ovBubble', { y: -4, duration: 1.8, yoyo: true, repeat: -1, ease: 'sine.inOut' });
      (function blink() {
        gsap.to('#eL, #eR', { scaleY: .1, svgOrigin: '120 128', duration: .08, yoyo: true, repeat: 1 });
        gsap.delayedCall(2.5 + Math.random() * 3, blink);
      })();
    });

    // 3D tilt: follows the phone's orientation, or the finger / mouse as a fallback
    var stage = document.getElementById('ovStage'), panel = document.getElementById('ovPanel'), shine = document.getElementById('ovShine');
    gsap.set('#char', { z: 40 }); gsap.set('#ovBubble', { z: 90 }); gsap.set('#ovPlate', { z: -40 });
    var rx = gsap.quickTo(stage, 'rotationX', { duration: .5, ease: 'power3.out' });
    var ry = gsap.quickTo(stage, 'rotationY', { duration: .5, ease: 'power3.out' });
    var sh = gsap.quickTo(shine, 'xPercent', { duration: .5, ease: 'power3.out' });
    var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
    var gotOrient = false, base = null;
    window.addEventListener('deviceorientation', function (e) {
      if (e.gamma == null || e.beta == null) return;
      gotOrient = true;
      if (!base) base = { g: e.gamma, b: e.beta };
      var dg = clamp(e.gamma - base.g, -30, 30), db = clamp(e.beta - base.b, -30, 30);
      ry(dg * .7); rx(-db * .5); sh(dg * 2);
    });
    if (window.DeviceOrientationEvent && typeof DeviceOrientationEvent.requestPermission === 'function') {
      document.addEventListener('pointerdown', function ask() {
        document.removeEventListener('pointerdown', ask);
        DeviceOrientationEvent.requestPermission().catch(function () {});
      });
    }
    panel.addEventListener('pointermove', function (e) {
      if (gotOrient) return;
      var r = panel.getBoundingClientRect();
      var x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      ry(x * 34); rx(-y * 24); sh(x * 60);
    });
    panel.addEventListener('pointerleave', function () { if (!gotOrient) { ry(0); rx(0); sh(0); } });
    gsap.fromTo(stage, { rotationY: -16, rotationX: 6 }, { rotationY: 0, rotationX: 0, duration: 1.6, delay: 1.2, ease: 'elastic.out(1,.5)' });
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
  if (!reduce) gsap.from('.ov-deck', { y: 20, opacity: 0, duration: .6, delay: .9, ease: 'power3.out' });

  function wipeCircle(url, color, x, y) {
    var w = document.getElementById('ovWipe'); w.style.background = color;
    gsap.fromTo(w, { clipPath: 'circle(0px at ' + x + 'px ' + y + 'px)' },
      { clipPath: 'circle(150% at ' + x + 'px ' + y + 'px)', duration: reduce ? 0 : .6, ease: 'power2.in', onComplete: function () { location.href = url; } });
  }
  function openCard(c, ev) {
    var p = ev ? (ev.changedTouches ? ev.changedTouches[0] : ev) : null;
    wipeCircle(c.dataset.href, c.dataset.color, (p && p.clientX) || innerWidth / 2, (p && p.clientY) || innerHeight / 2);
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
        onClick: function () { openCard(c, this.pointerEvent); } });
    });
  } else {
    order.forEach(function (c) { c.addEventListener('click', function (e) { openCard(c, e); }); });
  }
  if (!reduce) gsap.to(order[0], { x: 18, rotation: 1.2, duration: .3, yoyo: true, repeat: 1, delay: 3, ease: 'power1.inOut' });

  // coin -> Market (orange), cord -> Gigs (green)
  var coin = document.getElementById('ovCoin'), cord = document.getElementById('ovCord');
  var knob = document.getElementById('cordKnob'), str = document.getElementById('cordStr');
  var shut = document.getElementById('ovShut'), strips = [];
  ['#12B886', '#0FA377', '#12B886', '#0FA377', '#12B886', '#0FA377'].forEach(function (col) {
    var s = document.createElement('i'); s.style.background = col; shut.appendChild(s); strips.push(s);
  });
  function shutters(url) {
    gsap.timeline({ onComplete: function () { location.href = url; } })
      .to(strips, { scaleY: 1, duration: reduce ? 0 : .45, stagger: reduce ? 0 : .07, ease: 'power3.in' });
  }

  var tl = null;
  gsap.set(cord, { transformOrigin: '27px 0px' });
  if (!reduce) {
    gsap.to('#ovCoinIn', { rotationY: 360, duration: 2.2, repeat: -1, ease: 'none' });
    tl = gsap.timeline({ repeat: -1, repeatDelay: 3, delay: 2.2 });
    tl.to(coin, { x: 104, duration: .55, ease: 'back.out(1.6)' })
      .to(coin, { x: 0, duration: .5, ease: 'power2.in' }, '+=9')
      .to(cord, { y: 200, duration: .9, ease: 'elastic.out(1,.5)' }, '+=1')
      .to(cord, { rotation: 8, duration: 1.4, yoyo: true, repeat: 7, ease: 'sine.inOut' }, '>-.1')
      .to(cord, { y: 0, duration: .5, ease: 'power2.in' }, '+=.3');
  } else {
    gsap.set(coin, { x: 104 }); gsap.set(cord, { y: 200 });
  }

  var sx = 0, down = false;
  coin.addEventListener('pointerdown', function (e) { sx = e.clientX; down = true; if (tl) tl.pause(); coin.setPointerCapture(e.pointerId); });
  coin.addEventListener('pointerup', function (e) {
    if (!down) return; down = false;
    var dx = e.clientX - sx;
    if (dx > 24 || Math.abs(dx) < 6) {
      var r = coin.getBoundingClientRect();
      wipeCircle(coin.dataset.go, '#FF7A1A', r.left + r.width / 2, r.top + r.height / 2);
    } else if (tl) tl.resume();
  });
  coin.addEventListener('pointercancel', function () { down = false; if (tl) tl.resume(); });

  function resetKnob() {
    gsap.to(knob, { y: 0, duration: .7, ease: 'elastic.out(1,.45)' });
    gsap.to(str, { height: 140, duration: .7, ease: 'elastic.out(1,.45)' });
  }
  if (canDrag) {
    Draggable.create(knob, { type: 'y', bounds: { minY: 0, maxY: 130 }, edgeResistance: .85,
      onPress: function () { if (tl) tl.pause(); },
      onDrag: function () { gsap.set(str, { height: 140 + this.y }); },
      onDragEnd: function () {
        if (this.y > 80) { shutters(cord.dataset.go); }
        else { resetKnob(); if (tl) gsap.delayedCall(1.5, function () { tl.resume(); }); }
      },
      onClick: function () { shutters(cord.dataset.go); } });
  } else {
    knob.addEventListener('click', function () { shutters(cord.dataset.go); });
  }

  window.addEventListener('pageshow', function () {
    gsap.set('#ovWipe', { clipPath: 'circle(0px at 0 0)' });
    gsap.set(strips, { scaleY: 0 });
  });
})();
</script>
@endsection
