@extends('layouts.app-authenticated')
@section('title', 'Overview')

@section('content')
@php
  $net = $weekIn - $weekOut;
  $lagos = now()->setTimezone('Africa/Lagos');
  $hr = (int) $lagos->format('G');
  $greet = $hr < 12 ? 'Good morning' : ($hr < 17 ? 'Good afternoon' : 'Good evening');
  $dateLabel = $lagos->format('l, d M Y');
  $isVerified = (bool) (auth()->user()->is_verified_seller ?? false);
  $ovImg = file_exists(public_path('images/overview-circle.png'));

  $gigBal = (float) \App\Models\Transaction::where('user_id', auth()->id())->where('type', 'gig_earning')->where('amount', '>', 0)->sum('amount');
  $mktBal = (float) \App\Models\Transaction::where('user_id', auth()->id())->where('type', 'marketplace_sale')->where('amount', '>', 0)->sum('amount');

  $dayRows = \App\Models\Transaction::where('user_id', auth()->id())->whereDate('created_at', now()->toDateString())->get(['amount']);
  $dayIn = (float) $dayRows->where('amount', '>', 0)->sum('amount');
  $dayOut = abs((float) $dayRows->where('amount', '<', 0)->sum('amount'));

  $wkRows = \App\Models\Transaction::where('user_id', auth()->id())->where('created_at', '>=', now()->subDays(6)->startOfDay())->get(['amount', 'created_at']);
  $bars = [];
  for ($i = 6; $i >= 0; $i--) {
    $d = now()->subDays($i);
    $key = $d->toDateString();
    $rows = $wkRows->filter(fn ($r) => $r->created_at->toDateString() === $key);
    $bi = (float) $rows->where('amount', '>', 0)->sum('amount');
    $bo = abs((float) $rows->where('amount', '<', 0)->sum('amount'));
    $bars[] = ['label' => substr($d->format('D'), 0, 1), 'name' => $d->format('D'), 'in' => $bi, 'out' => $bo, 'net' => $bi - $bo];
  }
  $bmax = 1;
  foreach ($bars as $b) { $bmax = max($bmax, $b['in'], $b['out']); }
  foreach ($bars as $k => $b) {
    $bars[$k]['pi'] = max(4, round($b['in'] / $bmax * 100));
    $bars[$k]['po'] = max(4, round($b['out'] / $bmax * 100));
  }
  $nmax = 1;
  foreach ($bars as $b) { $nmax = max($nmax, abs($b['net'])); }
  $peak = 0;
  foreach ($bars as $k => $b) { if ($b['net'] > $bars[$peak]['net']) { $peak = $k; } }
  $trend = [];
  foreach ($bars as $k => $b) { $trend[] = round(($k + .5) / 7 * 100, 2) . ',' . round(70 - ($b['net'] / $nmax) * 50, 2); }
  $trendStr = implode(' ', $trend);

  $ovJson = json_encode(['day' => ['in' => $dayIn, 'out' => $dayOut, 'net' => $dayIn - $dayOut], 'week' => ['in' => (float) $weekIn, 'out' => (float) $weekOut, 'net' => (float) $net]], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
  $barsJson = json_encode($bars, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
@endphp

<style>
  @import url('https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Sora:wght@600;700;800&display=swap');

  html:has(body[data-page="dashboard"]) { background:var(--ov-edge, #D7E2EE) !important; }
  body[data-page="dashboard"], body[data-page="dashboard"] .app-shell-flex, body[data-page="dashboard"] .auth-page-shell,
  body[data-page="dashboard"] .app-push-content, body[data-page="dashboard"] .auth-reveal-content { background:transparent !important; }
  body[data-page="dashboard"] .app-shell-flex, body[data-page="dashboard"] .auth-page-shell,
  body[data-page="dashboard"] .app-push-content, body[data-page="dashboard"] .auth-reveal-content { overflow-x:clip !important; overflow-y:visible !important; }

  .ov { --ink:#0F1115; --mute:#7C8596; --line:rgba(255,255,255,.9);
        position:relative; z-index:1; max-width:760px; margin:0 auto; padding:10px 14px 110px; color:var(--ink); overflow-x:clip; }

  .ov-head { position:relative; display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding:4px 0 10px; }
  .ov-head::before { content:''; position:absolute; left:-14px; right:-14px; top:-10px; height:130px; z-index:-1;
    background:linear-gradient(120deg, rgba(18,184,134,.14), rgba(255,122,26,.08) 70%, transparent);
    clip-path:polygon(0 0, 100% 0, 100% 62%, 0 100%); }
  .ov-left { min-width:0; flex:1; }
  .ov-hello { font-family:'Caveat', cursive; font-size:22px; line-height:1; color:#12B886; margin:0; }
  .ov-name { font-size:30px; font-weight:900; letter-spacing:-.03em; line-height:1; margin:2px 0 0; }
  .ov-ver { display:inline-flex; vertical-align:middle; margin-left:6px; color:#12B886; }
  .ov-ver svg { width:18px; height:18px; }
  .ov-sub { font-family:'Caveat', cursive; font-size:17px; color:#0B9C73; margin:4px 0 0; position:relative; display:inline-block; }
  .ov-sub::after { content:''; position:absolute; left:0; right:-8px; bottom:-3px; height:3px; border-radius:3px; background:linear-gradient(90deg,#FF7A1A,#FFB070); transform:rotate(-2deg); }
  .ov-greet { font-size:11px; color:var(--mute); margin:8px 0 0; line-height:1.3; }
  .ov-right { flex-shrink:0; max-width:46%; display:flex; flex-direction:column; align-items:flex-end; gap:8px; }
  .ov-date { transform:rotate(-4deg); display:flex; align-items:center; gap:5px; padding:6px 9px; border-radius:14px; font-size:9px; font-weight:700; line-height:1.3; max-width:130px;
    background:rgba(255,255,255,.6); backdrop-filter:blur(16px) saturate(170%); border:1px solid var(--line); box-shadow:0 12px 24px -16px rgba(15,17,21,.35); }
  .ov-date svg { width:12px; height:12px; color:#12B886; flex-shrink:0; }
  .ov-bell { position:relative; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--ink); text-decoration:none;
    background:rgba(255,255,255,.6); backdrop-filter:blur(16px) saturate(170%); border:1px solid var(--line); box-shadow:0 12px 24px -16px rgba(15,17,21,.35); }
  .ov-bell svg { width:16px; height:16px; }
  .ov-bell::after { content:''; position:absolute; top:7px; right:8px; width:7px; height:7px; border-radius:50%; background:#FF7A1A; box-shadow:0 0 0 2px #fff; }

  .ov-main { display:grid; grid-template-columns:76px minmax(0,1fr); grid-template-rows:auto auto; align-items:center; }
  .ov-dock-slot { grid-column:1; grid-row:1; align-self:center; }
  .ov-hero { --D:min(520px, calc(200vw - 260px)); grid-column:2; grid-row:1; position:relative; height:calc(var(--D) * .9); }

  .ov-stage { position:absolute; top:50%; left:100%; width:var(--D); height:var(--D); margin:calc(var(--D) * -.5) 0 0 calc(var(--D) * -.5); }
  .ov-orb { position:absolute; inset:0; border-radius:50%; overflow:hidden; background:none; box-shadow:none; }
  .ov-orb img { width:100%; height:100%; display:block; object-fit:cover; user-select:none; -webkit-user-drag:none; }
  .ov-ground { position:absolute; left:8%; right:8%; bottom:-4%; height:14%; border-radius:50%; background:radial-gradient(closest-side, rgba(15,17,21,.45), transparent); filter:blur(8px); z-index:0; pointer-events:none; }
  .ov-big { position:absolute; inset:0; border-radius:50%; border:24px solid #fff; box-sizing:border-box; background:#fff;
    box-shadow:0 36px 56px -14px rgba(15,17,21,.5), 0 16px 26px -10px rgba(15,17,21,.35); }
  .ov-white { position:absolute; inset:11%; border-radius:50%; background:#fff; box-shadow:0 32px 48px -12px rgba(15,17,21,.5); }
  .ov-spin { position:absolute; inset:17%; border-radius:50%;
    background:conic-gradient(#9AA3B0, #FF7A1A, #12B886, #9AA3B0, #FF7A1A, #12B886, #9AA3B0);
    animation:ovSpin 7s linear infinite; box-shadow:0 26px 40px -10px rgba(15,17,21,.5); }
  .ov-black { position:absolute; inset:20%; border-radius:50%; background:#07090C; }

  .ov-cycle { position:absolute; top:16.5%; bottom:16.5%; left:16.5%; right:50%; z-index:3; pointer-events:none; }
  .ov-cs { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; color:#fff; opacity:0;
    font-family:'Sora', system-ui, sans-serif; transition:opacity .6s ease; padding:0 6px; }
  .ov-cs.on { opacity:1; }
  .ov-cs small { font-size:9px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.8); line-height:1.25; }
  .ov-cs b { display:block; margin-top:6px; font-size:clamp(17px, 4.8vw, 24px); font-weight:800; letter-spacing:-.02em; font-variant-numeric:tabular-nums; white-space:nowrap; text-shadow:0 2px 10px rgba(0,0,0,.45); }

  .ov-tw { grid-column:1 / -1; grid-row:2; margin-top:6px; }
  .stx { position:relative; overflow:hidden; margin:6px -14px 0; padding:18px 18px 16px; border-radius:28px; color:#fff;
    background:linear-gradient(160deg,#16193A 0%,#0A0C1C 60%,#07080F 100%); box-shadow:0 26px 44px -24px rgba(10,12,28,.75), inset 0 1px 0 rgba(255,255,255,.08); }
  .stx::before { content:''; position:absolute; right:-80px; top:-90px; width:260px; height:260px; border-radius:50%;
    background:radial-gradient(circle, rgba(124,130,240,.5), transparent 62%); pointer-events:none; }
  .stx-top { position:relative; z-index:1; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
  .stx-kicker { font-size:10px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:#A9B2FF; margin:0; }
  .stx-value { font-family:'Sora', system-ui, sans-serif; font-size:30px; font-weight:800; letter-spacing:-.02em; margin:4px 0 0; font-variant-numeric:tabular-nums; }
  .stx-sub { font-size:11px; color:rgba(255,255,255,.6); margin:4px 0 0; font-variant-numeric:tabular-nums; }
  .stx-tabs { position:relative; display:grid; grid-template-columns:1fr 1fr; padding:3px; border-radius:12px; background:rgba(255,255,255,.08); flex-shrink:0; }
  .stx-tabs button { position:relative; z-index:1; border:0; background:transparent; color:rgba(255,255,255,.6); font:inherit; font-size:11px; font-weight:800; padding:7px 12px; cursor:pointer; min-width:56px; }
  .stx-tabs button.on { color:#0A0C1C; }
  .stx-ind { position:absolute; top:3px; bottom:3px; left:3px; width:calc(50% - 3px); border-radius:9px; background:#fff; }

  .stx-day { position:relative; z-index:1; margin-top:18px; display:flex; flex-direction:column; gap:14px; }
  .stx-row { display:grid; grid-template-columns:44px minmax(0,1fr) auto; align-items:center; gap:10px; font-size:12px; font-weight:700; }
  .stx-row span:last-child { font-variant-numeric:tabular-nums; }
  .stx-track { height:16px; border-radius:99px; background:rgba(255,255,255,.08); overflow:hidden; }
  .stx-fill { height:100%; width:0; border-radius:99px; }
  .stx-fill.in { background:linear-gradient(90deg,#14B57E,#5CF2B5); box-shadow:0 0 16px rgba(92,242,181,.5); }
  .stx-fill.out { background:linear-gradient(90deg,#FF7A1A,#FFB866); box-shadow:0 0 16px rgba(255,122,26,.5); }

  .stx-week { position:relative; z-index:1; margin-top:18px; height:160px; display:none; align-items:flex-end; gap:8px; }
  .stx-week.show { display:flex; }
  .stx-col { position:relative; flex:1; height:100%; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; gap:6px; }
  .stx-bars { width:100%; height:calc(100% - 20px); display:flex; align-items:flex-end; justify-content:center; gap:3px; }
  .stx-bar { width:38%; max-width:12px; border-radius:7px 7px 3px 3px; transform-origin:bottom; transform:scaleY(0); }
  .stx-bar.in { background:linear-gradient(180deg,#5CF2B5,#14B57E); box-shadow:0 0 12px rgba(92,242,181,.45); }
  .stx-bar.out { background:linear-gradient(180deg,#FFB866,#FF7A1A); box-shadow:0 0 12px rgba(255,122,26,.45); }
  .stx-lbl { font-size:9px; font-weight:700; color:rgba(255,255,255,.55); }
  .stx-col.peak .stx-lbl { color:#fff; }
  .stx-line { position:absolute; left:0; right:0; bottom:20px; top:0; width:100%; height:calc(100% - 20px); pointer-events:none; overflow:visible; display:none; }
  .stx-line.show { display:block; }
  .stx-line polyline { fill:none; stroke:#C9CEFF; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; vector-effect:non-scaling-stroke; filter:drop-shadow(0 0 6px rgba(201,206,255,.6)); }

  .stx-foot { position:relative; z-index:1; margin-top:16px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
  .stx-legend { display:flex; gap:8px; }
  .stx-chip { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:99px; background:rgba(255,255,255,.08); font-size:10px; font-weight:700; color:rgba(255,255,255,.8); }
  .stx-chip i { width:7px; height:7px; border-radius:50%; display:inline-block; }
  .stx-best { font-size:11px; font-weight:700; color:rgba(255,255,255,.7); }
  .stx-best b { color:#fff; }

  .ov-coin { position:fixed; left:-84px; bottom:176px; width:60px; height:60px; z-index:40; perspective:300px; touch-action:none; cursor:grab; }
  .ov-coin-in { width:100%; height:100%; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#0F1115;
    background:radial-gradient(circle at 30% 25%,rgba(255,255,255,.8),rgba(255,255,255,.3) 60%,rgba(255,255,255,.15));
    backdrop-filter:blur(16px) saturate(180%); border:1px solid rgba(255,255,255,.9);
    box-shadow:0 14px 28px -10px rgba(15,17,21,.35), 0 0 0 4px rgba(255,122,26,.12), inset 0 1px 0 rgba(255,255,255,.95); }
  .ov-coin-in svg { color:#FF7A1A; }
  .ov-shut { position:fixed; inset:0; z-index:95; display:flex; pointer-events:none; }
  .ov-shut i { flex:1; transform:scaleY(0); transform-origin:top; }
  .ov-wipe { position:fixed; inset:0; z-index:90; pointer-events:none; clip-path:circle(0px at 0 0); }

  .ovd-drop { position:fixed; right:16px; top:0; z-index:50; display:block; text-decoration:none; }
  .ovd-swing { display:flex; flex-direction:column; align-items:center; transform-origin:50% 0%; }
  .ovd-cord { display:block; width:3px; height:120px; border-radius:3px; background:linear-gradient(#12B886,#7BE0A8); box-shadow:0 0 10px rgba(18,184,134,.6); }
  .ovd-knob { width:70px; height:70px; border-radius:24px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; color:#fff;
    background:linear-gradient(145deg,#1EC995,#0B9C73); box-shadow:0 18px 30px -12px rgba(11,156,115,.75), inset 0 1px 0 rgba(255,255,255,.4); }
  .ovd-knob svg { width:22px; height:22px; }
  .ovd-count { font-size:10px; font-weight:900; letter-spacing:.02em; }
  .ovd-tag { margin-top:6px; font-size:9px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:#0B7A5A; background:rgba(255,255,255,.85); border-radius:99px; padding:3px 8px; box-shadow:0 6px 12px -8px rgba(15,17,21,.4); }

  @keyframes ovSpin { to { transform:rotate(360deg); } }
  @keyframes sxTw { 0%,100% { opacity:.25; transform:scale(.8); } 50% { opacity:1; transform:scale(1.1); } }

  @media (min-width:900px) {
    .ov { max-width:1200px; padding:30px 40px 90px; }
    .ov-head { padding:0 0 26px; }
    .ov-head::before { left:-40px; right:-40px; height:150px; }
    .ov-hello { font-size:30px; } .ov-name { font-size:56px; } .ov-sub { font-size:24px; } .ov-greet { font-size:14px; }
    .ov-date { font-size:12px; padding:10px 14px; max-width:none; } .ov-date svg { width:15px; height:15px; }
    .ov-bell { width:44px; height:44px; } .ov-bell svg { width:18px; height:18px; } .ov-right { max-width:none; gap:12px; }
    .ov-main { grid-template-columns:minmax(0,1fr) minmax(0,1.05fr); column-gap:48px; grid-template-rows:auto 1fr; align-items:start; }
    .ov-dock-slot { grid-column:1; grid-row:1; align-self:start; margin-bottom:22px; }
    .ov-hero { --D:640px; grid-column:2; grid-row:1 / span 2; height:var(--D); }
    .ov-tw { grid-column:1; grid-row:2; margin-top:0; }
    .ov-cs b { font-size:34px; } .ov-cs small { font-size:11px; }
    .stx { margin:0; padding:24px 24px 20px; }
    .stx-value { font-size:40px; }
    .stx-week { height:200px; }
  }
  @media (prefers-reduced-motion:reduce) { .ov-spin { animation:none; } }
</style>

<link rel="stylesheet" href="{{ asset('css/overview-desktop.css') }}?v={{ time() }}">
<div class="ov">
  <header class="ov-head">
    <div class="ov-left">
      <p class="ov-hello">Hello!</p>
      <h1 class="ov-name">{{ $firstName }}@if($isVerified)<span class="ov-ver"><i data-lucide="badge-check"></i></span>@endif</h1>
      <p class="ov-sub">Wallet Overview</p>
      <p class="ov-greet">{{ $greet }}, make it a great day!</p>
    </div>
    <div class="ov-right">
      <div class="ov-date"><i data-lucide="calendar"></i><span>{{ $dateLabel }}</span></div>
      <a href="{{ route('notifications.index') }}" class="ov-bell" aria-label="Notifications"><i data-lucide="bell"></i></a>
    </div>
  </header>

  <div class="ov-main">
    <div class="ov-dock-slot">
      @include('partials.ov-dock', ['ovDockActive' => 'overview'])
    </div>

    <section class="ov-hero">
      <button type="button" class="ov-eye" id="ovEye" aria-label="Hide or show balance"><i data-lucide="eye"></i></button>
      <style>
        .ov-hero{position:relative}
        .ov-eye{position:absolute;top:6px;right:6px;z-index:5;width:34px;height:34px;border-radius:50%;border:0;display:grid;place-items:center;background:rgba(255,255,255,.18);color:#fff;cursor:pointer}
        .ov-eye svg{width:18px;height:18px}
        .ov-cs .qn{display:block;margin-top:3px;font-style:normal;font-size:10px;font-weight:500;opacity:.45}
        .qh{display:none}
        .ov-hero.hid ~ .ov-tw .stx-value,.ov-hero.hid ~ .ov-tw .stx-sub,.ov-hero.hid ~ .ov-tw .stx-row span:last-child,.ov-hero.hid ~ .ov-tw .stx-best{filter:blur(7px)}
        .ov-hero.hid .qa,.ov-hero.hid .qn{display:none}
        .ov-hero.hid .qh{display:inline}
      </style>
      <script>(function(){var h=document.querySelector('.ov-hero'),b=document.getElementById('ovEye');if(!h||!b)return;
        function set(x){h.classList.toggle('hid',x);b.innerHTML='<i data-lucide="'+(x?'eye-off':'eye')+'"></i>';if(window.lucide)lucide.createIcons();try{localStorage.setItem('ovHide',x?'1':'0')}catch(e){}}
        try{set(localStorage.getItem('ovHide')==='1')}catch(e){}
        b.addEventListener('click',function(){set(!h.classList.contains('hid'))});})();</script>

      <div class="ov-stage">
        @if($ovImg)
          <div class="ov-orb"><img id="ovOrbImg" src="{{ asset('images/overview-circle.png') }}" alt="" draggable="false"></div>
        @else
          <div class="ov-big"></div>
          <div class="ov-white"></div>
          <div class="ov-spin"></div>
          <div class="ov-black"></div>
        @endif
        <div class="ov-ground" aria-hidden="true"></div>

        <div class="ov-cycle" aria-live="polite">
          @php $qr = config('qcoin.q_per_naira_display', 100); @endphp
          <div class="ov-cs on"><small>Overall balance</small><b><span class="qa">{{ number_format($balance * $qr) }} Q</span><span class="qh">•••• Q</span></b><em class="qn">₦{{ number_format($balance) }}</em></div>
          <div class="ov-cs"><small>Gigs</small><b><span class="qa">{{ number_format($gigBal * $qr) }} Q</span><span class="qh">•••• Q</span></b><em class="qn">₦{{ number_format($gigBal) }}</em></div>
          <div class="ov-cs"><small>Marketplace</small><b><span class="qa">{{ number_format($mktBal * $qr) }} Q</span><span class="qh">•••• Q</span></b><em class="qn">₦{{ number_format($mktBal) }}</em></div>
        </div>
      </div>
    </section>

    <section class="ov-tw">
      <div class="stx" id="stx">
        <header class="stx-top">
          <div>
            <p class="stx-kicker">Cash flow</p>
            <p class="stx-value"><span id="stxBig">0 Q</span></p>
            <p class="stx-sub" id="stxSub">In 0 Q · Out 0 Q</p>
          </div>
          <div class="stx-tabs" id="stxTabs" role="tablist" data-p="day">
            <span class="stx-ind" id="stxInd"></span>
            <button type="button" data-p="day" class="on" role="tab">Day</button>
            <button type="button" data-p="week" role="tab">Week</button>
          </div>
        </header>

        <div class="stx-day" id="stxDay">
          <div class="stx-row"><span>In</span><span class="stx-track"><span class="stx-fill in" id="fillIn"></span></span><span id="valIn">0 Q</span></div>
          <div class="stx-row"><span>Out</span><span class="stx-track"><span class="stx-fill out" id="fillOut"></span></span><span id="valOut">0 Q</span></div>
        </div>

        <div class="stx-week" id="stxWeek">
          @foreach($bars as $k => $b)
          <div class="stx-col {{ $k === $peak ? 'peak' : '' }}">
            <div class="stx-bars">
              <i class="stx-bar in" style="height: {{ $b['pi'] }}%"></i>
              <i class="stx-bar out" style="height: {{ $b['po'] }}%"></i>
            </div>
            <span class="stx-lbl">{{ $b['label'] }}</span>
          </div>
          @endforeach
        </div>
        <svg class="stx-line" id="stxLine" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
          <polyline id="stxPoly" points="{{ $trendStr }}"/>
        </svg>

        <footer class="stx-foot">
          <div class="stx-legend">
            <span class="stx-chip"><i style="background:#14B57E"></i>In</span>
            <span class="stx-chip"><i style="background:#FF7A1A"></i>Out</span>
          </div>
          <p class="stx-best">Best day: <b>{{ $bars[$peak]['name'] }}</b> · {{ number_format($bars[$peak]['net'] * $qr) }} Q</p>
        </footer>
      </div>
    </section>
  </div>
</div>

<a id="ovDrop" class="ovd-drop" href="{{ route('gigs.index') }}" aria-label="Hustles, {{ $gigCount }} open">
  <span class="ovd-swing">
    <span class="ovd-cord" aria-hidden="true"></span>
    <span class="ovd-knob">
      <i data-lucide="briefcase"></i>
      <span class="ovd-count">{{ $gigCount }}</span>
    </span>
    <span class="ovd-tag">Hustles</span>
  </span>
</a>

<div class="ov-coin" id="ovCoin" data-go="{{ route('marketplace.index') }}"><div class="ov-coin-in" id="ovCoinIn"><i data-lucide="shopping-bag" style="width:24px;height:24px"></i></div></div>
<div class="ov-shut" id="ovShut"></div>
<div class="ov-wipe" id="ovWipe"></div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/Draggable.min.js"></script>
<script>
(function () {
  /* picture rim colour -> page background (slightly darker) */
  var orb = document.getElementById('ovOrbImg');
  function sampleRim() {
    try {
      var w = orb.naturalWidth, h = orb.naturalHeight; if (!w || !h) return false;
      var c = document.createElement('canvas'); c.width = w; c.height = h;
      var x = c.getContext('2d', { willReadFrequently: true });
      x.drawImage(orb, 0, 0);
      var cx = Math.round((w - 1) / 2), cy = Math.round((h - 1) / 2);
      var row = x.getImageData(cx, cy, w - cx, 1).data, Re = 0;
      for (var i = 0; i < w - cx; i++) { if (row[i * 4 + 3] < 128) { Re = i; break; } }
      if (!Re) Re = Math.round(w * .375);
      var R1 = Re * .90, R2 = Re * .98, r = 0, g = 0, b = 0, wt = 0;
      for (var a = 0; a < 360; a += 3) {
        var rad = a * Math.PI / 180;
        for (var k = 0; k < 4; k++) {
          var rr = R1 + (R2 - R1) * k / 3;
          var d = x.getImageData(Math.round(cx + Math.cos(rad) * rr), Math.round(cy + Math.sin(rad) * rr), 1, 1).data;
          var al = d[3] / 255;
          if (al > .2) { r += d[0] * al; g += d[1] * al; b += d[2] * al; wt += al; }
        }
      }
      if (!wt) return false;
      var __c = ('rgb(' +
        Math.round(r / wt * .94) + ',' + Math.round(g / wt * .94) + ',' + Math.round(b / wt * .94) + ')');
      try { localStorage.setItem('nvEdge', __c); } catch (e) {}
      document.documentElement.style.setProperty('--ov-edge', __c);
      return true;
    } catch (e) { return false; }
  }
  if (orb) { if (orb.complete) sampleRim(); else orb.addEventListener('load', sampleRim); }

  /* balances rotate inside the black centre */
  var cs = [].slice.call(document.querySelectorAll('.ov-cs')), ci = 0;
  if (cs.length > 1) {
    setInterval(function () {
      cs[ci].classList.remove('on');
      ci = (ci + 1) % cs.length;
      cs[ci].classList.add('on');
    }, 3500);
  }

  if (window.lucide) lucide.createIcons();
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!window.gsap) return;
  var canDrag = !!window.Draggable;
  if (canDrag) gsap.registerPlugin(Draggable);

  /* statistics card */
  var DATA = {!! $ovJson !!};
  var BARS = {!! $barsJson !!};
  var stxDay = document.getElementById('stxDay'), stxWeek = document.getElementById('stxWeek');
  var stxLine = document.getElementById('stxLine'), poly = document.getElementById('stxPoly');
  var ind = document.getElementById('stxInd'), tabs = document.getElementById('stxTabs');
  var bigEl = document.getElementById('stxBig'), subEl = document.getElementById('stxSub');
  var fillIn = document.getElementById('fillIn'), fillOut = document.getElementById('fillOut');
  var valIn = document.getElementById('valIn'), valOut = document.getElementById('valOut');
  var lenPoly = 0;
  try { lenPoly = poly.getTotalLength(); } catch (e) {}
  var counter = { v: 0 };
  var QR = {{ (float) $qr }};
  function money(n) { return (n < 0 ? '-' : '') + Math.round(Math.abs(n) * QR).toLocaleString() + ' Q'; }
  function countTo(target) {
    gsap.to(counter, { v: target, duration: reduce ? 0 : 1.1, ease: 'power3.out',
      onUpdate: function () { bigEl.textContent = money(counter.v); } });
  }
  function showDay(animate) {
    stxDay.style.display = 'flex';
    stxWeek.classList.remove('show');
    stxLine.classList.remove('show');
    var d = DATA.day, mx = Math.max(d.in, d.out, 1);
    gsap.set([fillIn, fillOut], { width: '0%' });
    gsap.to(fillIn,  { width: (d.in / mx * 100) + '%',  duration: animate && !reduce ? .9 : 0, ease: 'power3.out' });
    gsap.to(fillOut, { width: (d.out / mx * 100) + '%', duration: animate && !reduce ? .9 : 0, ease: 'power3.out', delay: .08 });
    valIn.textContent = money(d.in); valOut.textContent = money(d.out);
    subEl.textContent = 'In ' + money(d.in) + ' · Out ' + money(d.out);
    countTo(d.net);
  }
  function showWeek(animate) {
    stxDay.style.display = 'none';
    stxWeek.classList.add('show');
    stxLine.classList.add('show');
    var w = DATA.week;
    subEl.textContent = 'In ' + money(w.in) + ' · Out ' + money(w.out);
    countTo(w.net);
    var bars = [].slice.call(stxWeek.querySelectorAll('.stx-bar'));
    if (animate && !reduce) {
      gsap.fromTo(bars, { scaleY: 0 }, { scaleY: 1, duration: .8, ease: 'back.out(1.3)', stagger: { each: .05, from: 'start' } });
      if (lenPoly) {
        gsap.set(poly, { strokeDasharray: lenPoly, strokeDashoffset: lenPoly });
        gsap.to(poly, { strokeDashoffset: 0, duration: 1.4, delay: .35, ease: 'power2.inOut' });
      }
    } else {
      gsap.set(bars, { scaleY: 1 });
      gsap.set(poly, { strokeDasharray: 'none', strokeDashoffset: 0 });
    }
  }
  function setTab(p, animate) {
    tabs.setAttribute('data-p', p);
    [].slice.call(tabs.querySelectorAll('button')).forEach(function (b) { b.classList.toggle('on', b.dataset.p === p); });
    gsap.to(ind, { x: p === 'week' ? (ind.parentElement.offsetWidth / 2 - 3) : 0, duration: reduce ? 0 : .45, ease: 'power3.out' });
    if (p === 'week') showWeek(animate); else showDay(animate);
  }
  [].slice.call(tabs.querySelectorAll('button')).forEach(function (b) {
    b.addEventListener('click', function () { setTab(b.dataset.p, true); });
  });
  setTab('day', true);

  /* overview's own entrance */
  gsap.from('.ov-head > *', { y: 14, opacity: 0, duration: .6, stagger: .08, ease: 'power3.out' });
  gsap.from('.ov-stage', { x: 60, opacity: 0, duration: .9, delay: .2, ease: 'power3.out' });
  gsap.from('#stx', { y: 22, opacity: 0, duration: .7, delay: .4, ease: 'power3.out' });

  /* hustles: falls once, stays down (remembered), and the whole strip sways from its top */
  var drop = document.getElementById('ovDrop');
  if (drop) {
    var swing = drop.querySelector('.ovd-swing');
    var KEY = 'ov_hustles_down_v1', already = false;
    try { already = localStorage.getItem(KEY) === '1'; } catch (e) {}
    if (already || reduce) {
      gsap.set(drop, { y: 0 });
    } else {
      gsap.set(drop, { y: -300 });
      gsap.to(drop, { y: 0, duration: 1.1, delay: 1.2, ease: 'bounce.out',
        onComplete: function () { try { localStorage.setItem(KEY, '1'); } catch (e) {} } });
    }
    if (swing && !reduce) gsap.to(swing, { rotation: 3, duration: 1.8, yoyo: true, repeat: -1, ease: 'sine.inOut' });
  }

  /* coin: floats, slides, taps through to Market */
  var coin = document.getElementById('ovCoin'), shut = document.getElementById('ovShut'), strips = [];
  ['#12B886', '#0FA377', '#12B886', '#0FA377', '#12B886', '#0FA377'].forEach(function (col) {
    var s = document.createElement('i'); s.style.background = col; shut.appendChild(s); strips.push(s);
  });
  function shutters(url) {
    gsap.timeline({ onComplete: function () { location.href = url; } })
      .to(strips, { scaleY: 1, duration: reduce ? 0 : .45, stagger: reduce ? 0 : .07, ease: 'power3.in' });
  }
  var tl = null;
  if (!reduce) {
    gsap.to('#ovCoinIn', { rotationY: 360, duration: 2.4, repeat: -1, ease: 'none' });
    tl = gsap.timeline({ repeat: -1, repeatDelay: 3, delay: 2.6 });
    tl.to(coin, { x: 96, duration: .55, ease: 'back.out(1.6)' })
      .to(coin, { x: 0, duration: .5, ease: 'power2.in' }, '+=2');
  } else {
    gsap.set(coin, { x: 96 });
  }
  var sx = 0, down = false;
  coin.addEventListener('pointerdown', function (e) { sx = e.clientX; down = true; if (tl) tl.pause(); coin.setPointerCapture(e.pointerId); });
  coin.addEventListener('pointerup', function (e) {
    if (!down) return; down = false;
    var dx = e.clientX - sx;
    if (dx > 24 || Math.abs(dx) < 6) shutters(coin.dataset.go);
    else if (tl) tl.resume();
  });
  coin.addEventListener('pointercancel', function () { down = false; if (tl) tl.resume(); });

  window.addEventListener('pageshow', function () { gsap.set(strips, { scaleY: 0 }); });
})();
</script>
@endsection
