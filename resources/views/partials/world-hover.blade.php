@auth
@php
  $hwPage = $pageKey ?? '';
  $hwCount = (int) ($gigCount ?? \App\Models\Gig::where('status', 'open')->count());
  $hwQ = (int) (optional(auth()->user())->wallet_balance ?? 0);
@endphp
@unless(in_array($hwPage, ['overview'], true))
<style>
  .hw-top {
    position:fixed; top:0; right:18px; z-index:60;
    display:flex; flex-direction:column; align-items:center; text-decoration:none; color:#0F1115; perspective:600px;
  }
  .hw-cord { display:block; width:2px; height:60px; border-radius:2px; background:linear-gradient(#12B886,#7BE0A8); box-shadow:0 0 8px rgba(18,184,134,.5); }
  .hw-3d {
    position:relative; width:54px; height:54px; border-radius:18px;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1px; color:#0B9C73;
    background:linear-gradient(160deg, rgba(255,255,255,.42), rgba(255,255,255,.08) 45%, rgba(255,255,255,.3));
    -webkit-backdrop-filter:blur(2px) saturate(1.8) brightness(1.1);
    backdrop-filter:blur(2px) saturate(1.8) brightness(1.1);
    backdrop-filter:url(#nvLens) saturate(1.8) brightness(1.1);
    border:1px solid rgba(255,255,255,.95);
    box-shadow:inset 0 2px 0 rgba(255,255,255,.95), inset 0 -6px 10px -6px rgba(8,110,80,.35),
      0 4px 0 rgba(8,110,80,.35), 0 9px 0 rgba(8,110,80,.16), 0 18px 26px -12px rgba(15,17,21,.45);
    transform:rotateX(14deg) rotateY(-12deg); transform-style:preserve-3d;
    transition:transform .25s ease, box-shadow .25s ease;
  }
  .hw-3d svg { width:20px; height:20px; }
  .hw-count { font-size:9px; font-weight:900; line-height:1; }
  .hw-top:hover .hw-3d { transform:translateY(3px); box-shadow:inset 0 2px 0 rgba(255,255,255,.95), 0 1px 0 rgba(8,110,80,.35), 0 6px 14px -8px rgba(15,17,21,.45); }
  .hw-tag { margin-top:6px; font-size:8px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:#0B7A5A; background:rgba(255,255,255,.85); border-radius:99px; padding:3px 8px; box-shadow:0 6px 12px -8px rgba(15,17,21,.4); }

  .hw-market {
    position:fixed; left:10px; top:400px; z-index:45;
    display:flex; flex-direction:column; align-items:center; gap:4px; text-decoration:none; color:#0F1115;
  }
  .hw-market .hw-3d { width:46px; height:46px; border-radius:16px; color:#FF7A1A; }
  .hw-market .hw-3d svg { width:17px; height:17px; }
  .hw-market:hover .hw-3d { transform:scale(1.06); }

  .hw-q {
    position:fixed; right:10px; top:200px; z-index:45;
    display:flex; flex-direction:row; align-items:center; gap:6px; text-decoration:none; color:#0F1115;
  }
  .hw-coin {
    position:relative; width:46px; height:46px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; color:#A64300;
    background:radial-gradient(circle at 32% 26%, rgba(255,255,255,.9), rgba(255,236,150,.85) 30%, rgba(246,190,50,.9) 65%, rgba(214,140,18,.95));
    -webkit-backdrop-filter:blur(2px) saturate(1.8); backdrop-filter:blur(2px) saturate(1.8);
    backdrop-filter:url(#nvLens) saturate(1.8);
    border:1.5px solid #000;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.9), 0 0 0 1px rgba(0,0,0,.5), 0 10px 18px -10px rgba(0,0,0,.45);
    animation:hwSpin 4s linear infinite;
  }
  .hw-coin svg { width:18px; height:18px; }
  .hw-bal {
    font-size:10px; font-weight:900; color:#000; line-height:1; white-space:nowrap;
    background:rgba(255,255,255,.8); border-radius:99px; padding:4px 9px; font-variant-numeric:tabular-nums;
    box-shadow:0 6px 12px -8px rgba(15,17,21,.4);
  }
  .hw-q:hover .hw-coin { transform:scale(1.06); }
  @keyframes hwSpin { from { transform:rotateY(0deg); } to { transform:rotateY(360deg); } }
  @media (prefers-reduced-motion: reduce) { .hw-coin { animation:none; } }

  .hw-qc { display:none; }
  @media (min-width:901px) {
    .hw-market { display:none !important; }
    .hw-top { top:300px; right:auto; left:10px; z-index:45; }
    .hw-top .hw-cord { display:none; }
    .hw-top .hw-3d { width:46px; height:46px; border-radius:16px; }
    .hw-top .hw-3d svg { width:17px; height:17px; }
    .hw-coin { display:none !important; }
    .hw-qc { display:block; position:relative; width:46px; height:46px; flex:none; pointer-events:none; }
    .hw-qc .qc { position:absolute; left:50%; top:50%; width:210px; height:210px; margin:-105px 0 0 -105px; transform:scale(.21); }
    .hw-qc .qc-halo, .hw-qc .qc-shadow { display:none; }
    .hw-qc .qc-float { animation:none !important; }
    .hw-qc .qc-spin { animation:qcSpin 3.2s linear infinite !important; }
    .hw-q { top:8px !important; right:auto !important; left:50% !important; transform:translateX(-50%); z-index:60; }
  }
  @media (prefers-reduced-motion: reduce) { .hw-qc .qc-spin { animation:none !important; } }
</style>
<link rel="stylesheet" href="{{ asset('css/qcoin-coin.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/hover-fix.css') }}?v={{ filemtime(public_path('css/hover-fix.css')) }}">
<script src="{{ asset('js/hover-fix.js') }}?v={{ filemtime(public_path('js/hover-fix.js')) }}" defer></script>



<a class="hw-q" href="{{ route('qcoin.index') }}" aria-label="Q-Coin, {{ number_format($hwQ) }} Q">
  <span class="hw-qc" aria-hidden="true">
    <div class="qc qc-gold">
      <div class="qc-float"><div class="qc-spin"><div class="qc-stage">
        @for($i = 1; $i <= 7; $i++)<span class="qc-edge" style="--d: {{ $i * 1.6 }}px"></span>@endfor
        <span class="qc-face"><span class="qc-ring"></span><img class="qc-emblem" src="{{ asset('images/qcoin-logo.svg') }}?v=3" alt="" draggable="false"><span class="qc-gloss"></span></span>
        <span class="qc-back"><span class="qc-ring"></span><span class="qc-brand">NAURVEX</span><span class="qc-bar"></span></span>
      </div></div></div>
    </div>
  </span>
  <span class="hw-coin"><i data-lucide="gem"></i></span>
</a>

<script>(function(){ if (window.lucide) lucide.createIcons(); })();</script>
@endunless
@endauth
