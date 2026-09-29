<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Dashboard Preview</title>
<style>
  :root { box-sizing: border-box; --accent: #7c5cf0; padding-top: env(safe-area-inset-top,0px); padding-bottom: env(safe-area-inset-bottom,0px); }
  *,*::before,*::after{box-sizing:inherit;}
  body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Inter","Segoe UI",Roboto,sans-serif;background:linear-gradient(160deg,#0f2f5e 0%,#1b4b8f 35%,#1c8b9e 68%,#17b9a8 100%);color:#1a1a2e;min-height:100vh;position:relative;overflow-x:hidden;}
  body::before,body::after{content:"";position:fixed;border-radius:50%;filter:blur(60px);z-index:0;pointer-events:none;}
  body::before{width:280px;height:280px;top:-80px;right:-60px;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 70%);}
  body::after{width:320px;height:320px;bottom:10%;left:-100px;background:radial-gradient(circle,rgba(23,185,168,.4),transparent 70%);}
  .page{position:relative;z-index:1;}
  .page{max-width:480px;margin:0 auto;padding:18px 16px 110px;display:flex;flex-direction:column;gap:14px;}
  @keyframes fadeIn{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
  .anim{animation:fadeIn .5s ease both;animation-delay:calc(var(--i,0)*70ms);}

  .banner{display:flex;justify-content:space-between;gap:10px;align-items:center;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:10px 14px;font-size:12px;color:#9a9aa5;}
  .banner b{color:#fff;} .banner a,.banner button{color:var(--accent);font-weight:700;background:none;border:none;cursor:pointer;text-decoration:none;}

  .hero{position:relative;overflow:hidden;border-radius:26px;padding:22px 22px 28px;background:linear-gradient(135deg,#1b4b8f 0%,#1c8b9e 55%,#17b9a8 100%);box-shadow:0 24px 50px -16px rgba(23,139,158,.5),0 8px 24px -8px rgba(0,0,0,.3);z-index:2;}
  .hero::before,.hero::after{content:"";position:absolute;border-radius:50%;filter:blur(30px);z-index:-1;pointer-events:none;}
  .hero::before{width:160px;height:160px;top:-50px;right:-40px;background:rgba(255,255,255,.18);}
  .hero::after{width:120px;height:120px;bottom:-40px;left:-30px;background:rgba(23,185,168,.4);}
  .noise{position:absolute;inset:0;background:repeating-linear-gradient(115deg,rgba(255,255,255,.05) 0px,rgba(255,255,255,.05) 1px,transparent 1px,transparent 40px);pointer-events:none;}
  .shine{position:absolute;top:0;left:-60%;width:40%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.25),transparent);transform:skewX(-20deg);animation:sweep 6s ease-in-out infinite;pointer-events:none;}
  @keyframes sweep{0%{left:-60%;}45%{left:130%;}100%{left:130%;}}
  .hero-top{display:flex;justify-content:space-between;align-items:center;color:rgba(255,255,255,.85);font-size:11px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;}
  .chip{width:32px;height:23px;border-radius:6px;background:linear-gradient(135deg,#fff2d6,#e2c08a);margin-bottom:12px;}
  .amount{font-size:32px;font-weight:800;letter-spacing:-.02em;color:#fff;text-shadow:0 2px 12px rgba(0,0,0,.15);}
  .masked{font-family:'Courier New',monospace;font-size:14px;letter-spacing:2px;color:rgba(255,255,255,.75);margin:8px 0 2px;}
  .holder-row{display:flex;justify-content:space-between;margin-top:10px;}
  .holder{font-size:9.5px;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.05em;}
  .holder b{display:block;font-size:12px;color:#fff;font-weight:700;text-transform:none;letter-spacing:0;margin-top:2px;}
  .hero-actions{display:flex;gap:8px;margin-top:16px;}
  .btn{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;padding:10px 16px;border-radius:999px;text-decoration:none;border:none;cursor:pointer;transition:transform .12s ease;}
  .btn-primary{background:#fff;color:#1b4b8f;} .btn-ghost{background:rgba(255,255,255,.2);color:#fff;} .btn-icon{background:rgba(255,255,255,.2);color:#fff;padding:10px;}
  .btn:active{transform:scale(.95);}

  .mini-row{display:flex;gap:10px;overflow-x:auto;scrollbar-width:none;}
  .mini-row::-webkit-scrollbar{display:none;}
  .mini-card{flex-shrink:0;min-width:108px;background:linear-gradient(160deg,#ffffff 0%,#f2f3f7 100%);border:none;border-radius:16px;padding:14px;display:flex;flex-direction:column;gap:6px;box-shadow:0 10px 24px -10px rgba(15,47,94,.2);}
  .mini-card svg{width:18px;height:18px;} .mini-card .v{font-size:15px;font-weight:800;color:#1a1a2e;} .mini-card .l{font-size:10px;color:#7a7a8a;}
  .spark{width:100%;height:20px;margin-top:2px;}
  .t-green{color:#16a34a;} .t-purple{color:#7c3aed;} .t-gold{color:#d97706;} .t-blue{color:#1c8b9e;}

  .services{display:flex;justify-content:space-between;gap:8px;}
  .service{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;}
  .service .ic{width:46px;height:46px;border-radius:15px;display:flex;align-items:center;justify-content:center;}
  .service .ic svg{width:20px;height:20px;color:#fff;}
  .svc-blue{background:rgba(59,130,246,.18);} .svc-green{background:rgba(34,197,94,.18);} .svc-purple{background:rgba(168,85,247,.18);} .svc-orange{background:rgba(251,146,60,.18);}
  .service span{font-size:10px;color:#9a9aa5;font-weight:600;}

  .card{background:linear-gradient(160deg,#ffffff 0%,#f2f3f7 100%);border:none;border-radius:20px;padding:18px;box-shadow:0 16px 32px -12px rgba(15,47,94,.25),0 4px 10px -4px rgba(15,47,94,.1);}
  .card-head span:first-child{color:#1a1a2e;}
  .card-head a{color:#1c8b9e !important;}
  .card-head{display:flex;justify-content:space-between;align-items:baseline;font-size:14px;font-weight:700;margin-bottom:12px;}
  .card-head a{font-size:11px;font-weight:600;color:var(--accent);text-decoration:none;}

  .chart-top{display:flex;gap:8px;margin-bottom:10px;}
  .pill{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:4px 10px;border-radius:999px;background:#eef1f7;}
  .pill svg{width:11px;height:11px;} .pill-in{color:#4ade80;} .pill-out{color:#fb923c;}
  .net{font-size:12px;font-weight:700;color:var(--accent);}
  .wave-wrap{width:100%;height:92px;filter:drop-shadow(0 0 10px color-mix(in srgb,var(--accent) 35%,transparent));}
  .wave-line{stroke-dasharray:500;stroke-dashoffset:500;animation:draw 1.4s ease forwards .2s;}
  @keyframes draw{to{stroke-dashoffset:0;}}

  .quick-row{display:flex;gap:12px;overflow-x:auto;scrollbar-width:none;padding-top:2px;}
  .quick-row::-webkit-scrollbar{display:none;}
  .quick-item{display:flex;flex-direction:column;align-items:center;gap:6px;flex-shrink:0;}
  .quick-avatar{width:48px;height:48px;border-radius:50%;overflow:hidden;border:2px solid #eef1f7;display:flex;align-items:center;justify-content:center;color:#1a1a2e;font-weight:700;background:#eef1f7;text-decoration:none;}
  .quick-avatar img{width:100%;height:100%;object-fit:cover;}
  .quick-add{background:rgba(255,255,255,.06);border-style:dashed;color:#9a9aa5;}
  .quick-item span{font-size:10px;color:#3a3a4a;font-weight:600;}

  .tx-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid #eceef3;text-decoration:none;}
  .tx-row:first-of-type{border-top:none;}
  .tx-avatar{width:38px;height:38px;border-radius:12px;overflow:hidden;flex-shrink:0;background:#eef1f7;display:flex;align-items:center;justify-content:center;}
  .tx-avatar svg{width:17px;height:17px;color:#1c8b9e;}
  .tx-body{display:flex;flex-direction:column;flex:1;min-width:0;}
  .tx-body .t{font-size:13px;font-weight:600;color:#1a1a2e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .tx-body .s{font-size:10.5px;color:#8a8a95;}
  .tx-amount{font-size:13px;font-weight:700;color:#1a1a2e;}

  .mp-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
  .mp-item{background:#eef1f7;border-radius:12px;padding:10px;}
  .mp-item .v{display:block;font-size:14px;font-weight:700;color:#1a1a2e;} .mp-item .l{font-size:10px;color:#8a8a95;}
  .mp-soon .v{font-style:italic;color:#a8a8b3;font-size:11px;}

  .profile-card{position:relative;border-radius:20px;overflow:hidden;aspect-ratio:16/9;background:#000;}
  .profile-photo{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:36px;font-weight:700;}
  .profile-photo img{width:100%;height:100%;object-fit:cover;}
  .profile-scrim{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.9),transparent 60%);}
  .profile-info{position:absolute;left:0;right:0;bottom:0;padding:16px;}
  .profile-info .name{font-size:15px;font-weight:700;margin:0;} .profile-info .level{font-size:11px;color:rgba(255,255,255,.65);margin:2px 0 8px;}
  .profile-info .stats{display:flex;gap:12px;font-size:11px;color:rgba(255,255,255,.85);margin-bottom:8px;}
  .profile-info .edit{display:inline-block;background:#fff;color:#000;font-size:11px;font-weight:700;padding:6px 12px;border-radius:999px;text-decoration:none;}

  .empty{color:#6c6c76;font-size:12px;}

  .nav{position:fixed;left:50%;bottom:max(14px,env(safe-area-inset-bottom,0px));transform:translateX(-50%);width:calc(100% - 32px);max-width:388px;display:flex;justify-content:space-between;align-items:center;background:rgba(20,20,24,.9);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.08);border-radius:999px;padding:8px;box-shadow:0 12px 32px -8px rgba(0,0,0,.6);z-index:40;}
  .nav-item{display:flex;flex-direction:column;align-items:center;gap:2px;padding:8px 12px;border-radius:999px;color:#8b8b93;text-decoration:none;}
  .nav-item svg{width:18px;height:18px;} .nav-item span{font-size:9px;font-weight:600;}
  .nav-item.active{color:#06110c;background:var(--accent);box-shadow:0 0 16px color-mix(in srgb,var(--accent) 65%,transparent);}

  .streak-card{position:relative;border-radius:22px;overflow:hidden;padding:20px;min-height:140px;display:flex;flex-direction:column;justify-content:flex-end;}
  .streak-card img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
  .streak-scrim{position:absolute;inset:0;background:linear-gradient(160deg,rgba(124,92,240,.55),rgba(0,0,0,.85));}
  .streak-content{position:relative;z-index:1;display:flex;justify-content:space-between;align-items:flex-end;}
  .streak-left .lbl{font-size:11px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.05em;}
  .streak-left .num{font-size:30px;font-weight:800;color:#fff;line-height:1.1;}
  .streak-left .sub{font-size:11px;color:rgba(255,255,255,.6);margin-top:2px;}
  .streak-ring{position:relative;width:64px;height:64px;flex-shrink:0;}
  .streak-ring svg{width:100%;height:100%;transform:rotate(-90deg);}
  .streak-ring .track{fill:none;stroke:rgba(255,255,255,.2);stroke-width:6;}
  .streak-ring .fill{fill:none;stroke:#fff;stroke-width:6;stroke-linecap:round;transition:stroke-dashoffset 1s ease;}
  .streak-ring-center{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;}

  .rank-row{display:flex;gap:12px;align-items:center;}
  .rank-badge{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#fbbf24,#b45309);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#1a0f00;flex-shrink:0;}
  .rank-body{display:flex;flex-direction:column;gap:2px;flex:1;}
  .rank-body .t{font-size:13px;font-weight:700;color:#f0f0f2;}
  .rank-body .s{font-size:11px;color:#8b8b93;}
  .rank-cta{font-size:11px;font-weight:700;color:var(--accent);text-decoration:none;padding:8px 14px;background:rgba(124,92,240,.15);border-radius:999px;white-space:nowrap;}

  .buzz-row{display:flex;gap:10px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px;}
  .buzz-row::-webkit-scrollbar{display:none;}
  .buzz-card{flex-shrink:0;width:160px;border-radius:16px;overflow:hidden;position:relative;height:110px;text-decoration:none;}
  .buzz-card img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
  .buzz-scrim{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85),transparent 55%);}
  .buzz-cat{position:absolute;top:8px;left:8px;font-size:9px;font-weight:700;color:#fff;background:rgba(124,92,240,.85);padding:3px 8px;border-radius:999px;text-transform:uppercase;letter-spacing:.03em;}
  .buzz-title{position:absolute;left:10px;right:10px;bottom:8px;font-size:11.5px;font-weight:700;color:#fff;line-height:1.3;}
</style>
</head>
<body>
<div class="page">

  @if(!($isVerified ?? true))
  <div class="banner anim" style="--i:0">
    <span>Unverified wallet limit — up to ₦{{ number_format($walletCapRemaining ?? 0) }} more before verifying your BVN.</span>
    <a href="{{ route('wallet.virtual-account') }}">Verify</a>
  </div>
  @endif

  <div class="hero anim" style="--i:1">
    <div class="noise"></div>
    <div class="shine"></div>
    <div class="hero-top"><span>Total Balance</span>
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="2"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
    </div>
    <div class="chip"></div>
    <div class="amount">₦{{ number_format($balance ?? 0) }}</div>
    @if($user->virtual_account_number ?? false)
    <div class="masked">•••• •••• {{ substr($user->virtual_account_number, -4) }}</div>
    <div class="holder-row">
      <span class="holder">Card holder<b>{{ $user->name }}</b></span>
      <span class="holder">Bank<b>{{ $user->virtual_account_bank }}</b></span>
    </div>
    @endif
    <div class="hero-actions">
      <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="btn btn-primary"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg> Send</a>
      <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="btn btn-ghost">Withdraw</a>
      <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="btn btn-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg></a>
    </div>
  </div>

  @php
    $dqmCirc2 = 2 * pi() * 28;
    $dqmMilestone2 = $nextStreakMilestone ?? 7;
    $dqmPct2 = $dqmMilestone2 > 0 ? min(100, round((($streakDays ?? 0) / $dqmMilestone2) * 100)) : 0;
    $dqmOffset2 = $dqmCirc2 * (1 - $dqmPct2 / 100);
  @endphp
  <div class="card streak-card anim" style="--i:1.5">
    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&h=300&fit=crop" alt="">
    <div class="streak-scrim"></div>
    <div class="streak-content">
      <div class="streak-left">
        <span class="lbl">Current Streak</span>
        <div class="num">{{ $streakDays ?? 0 }} days</div>
        <span class="sub">Next milestone: {{ $dqmMilestone2 }} days</span>
      </div>
      <div class="streak-ring">
        <svg viewBox="0 0 64 64"><circle class="track" cx="32" cy="32" r="28"></circle><circle class="fill" cx="32" cy="32" r="28" stroke-dasharray="{{ $dqmCirc2 }}" stroke-dashoffset="{{ $dqmOffset2 }}"></circle></svg>
        <div class="streak-ring-center">{{ $dqmPct2 }}%</div>
      </div>
    </div>
  </div>

  @if($leaderboardRank ?? null)
  <div class="card anim" style="--i:1.7">
    <div class="rank-row">
      <div class="rank-badge">#{{ $leaderboardRank }}</div>
      <div class="rank-body">
        <span class="t">You're ranked #{{ $leaderboardRank }} this week</span>
        <span class="s">Keep earning to climb the leaderboard</span>
      </div>
      <a href="{{ route('leaderboard.index') }}" class="rank-cta">View</a>
    </div>
  </div>
  @endif

  @if(($latestNews ?? collect())->count() > 0)
  <div class="card anim" style="--i:1.9">
    <div class="card-head"><span>Buzz</span><a href="{{ route('news.index') }}">See all</a></div>
    <div class="buzz-row">
      @foreach($latestNews->take(5) as $i => $n)
        <a href="{{ route('news.index') }}" class="buzz-card">
          <img src="https://picsum.photos/seed/buzz{{ $n->id ?? $i }}/320/220" alt="">
          <div class="buzz-scrim"></div>
          @if($n->category ?? false)<span class="buzz-cat">{{ $n->category }}</span>@endif
          <span class="buzz-title">{{ \Illuminate\Support\Str::limit($n->title, 48) }}</span>
        </a>
      @endforeach
    </div>
  </div>
  @endif

  @php
    $miniStats = [
      ['label'=>'Earnings','value'=>'₦'.number_format($earnings??0),'tint'=>'t-green','seed'=>(int)(($earnings??0)/1000)+5],
      ['label'=>'Active Gigs','value'=>(string)($myOpenGigs??0),'tint'=>'t-purple','seed'=>($myOpenGigs??0)+2],
      ['label'=>'Q Coin','value'=>number_format($qCoinBalance??0).' Q','tint'=>'t-gold','seed'=>(int)(($qCoinBalance??0)/10)+7],
      ['label'=>'Rating','value'=>$avgRating?number_format($avgRating,1).' ★':'—','tint'=>'t-blue','seed'=>(int)(($avgRating??1)*10)+1],
    ];
  @endphp
  <div class="mini-row anim" style="--i:2">
    @foreach($miniStats as $s)
      @php
        $sp=[]; for($j=0;$j<=8;$j++){ $n=sin($j*1.1+$s['seed'])*8+cos($j*0.6+$s['seed'])*5; $sp[]=round(20-$n,1); }
        $spPath=''; foreach($sp as $j=>$y){ $x=$j*10; $spPath.=($j===0?"M{$x},{$y} ":"L{$x},{$y} "); }
      @endphp
      <div class="mini-card {{ $s['tint'] }}">
        <span class="v">{{ $s['value'] }}</span><span class="l">{{ $s['label'] }}</span>
        <svg class="spark" viewBox="0 0 80 28" preserveAspectRatio="none"><path d="{{ $spPath }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    @endforeach
  </div>

  <div class="services anim" style="--i:3">
    <a href="{{ route('services.index') }}" class="service"><div class="ic svc-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg></div><span>Airtime</span></a>
    <a href="{{ route('services.index') }}" class="service"><div class="ic svc-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg></div><span>Data</span></a>
    <a href="{{ route('services.index') }}" class="service"><div class="ic svc-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M17 2l-5 5-5-5"/></svg></div><span>Cable TV</span></a>
    <a href="{{ route('services.index') }}" class="service"><div class="ic svc-orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><span>Electric</span></a>
  </div>

  @php
    $wSeed = max(1,(int)(($netFlow??0)+($totalIn??0)+($totalOut??0)));
    $wPts=[]; for($i=0;$i<=6;$i++){ $n=sin($i*1.3+($wSeed%7))*20+cos($i*0.7)*12; $wPts[]=round(50-$n,1); }
    $wLine=''; foreach($wPts as $i=>$y){ $x=$i*50; $wLine.=($i===0?"M{$x},{$y} ":"L{$x},{$y} "); }
    $wFill="M0,100 L0,{$wPts[0]} "; foreach($wPts as $i=>$y){ if($i===0) continue; $x=$i*50; $wFill.="L{$x},{$y} "; } $wFill.="L300,100 Z";
  @endphp
  <div class="card anim" style="--i:4">
    <div class="chart-top">
      <span class="pill pill-in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg> In ₦{{ number_format($totalIn??0) }}</span>
      <span class="pill pill-out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 19V5M5 12l7-7 7 7"/></svg> Out ₦{{ number_format($totalOut??0) }}</span>
    </div>
    <div class="card-head"><span>Money Flow</span><span class="net">₦{{ number_format($netFlow??0) }} net</span></div>
    <svg class="wave-wrap" viewBox="0 0 300 100" preserveAspectRatio="none">
      <defs>
        <linearGradient id="wf" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#7c5cf0" stop-opacity=".45"/><stop offset="100%" stop-color="#7c5cf0" stop-opacity="0"/></linearGradient>
        <linearGradient id="ws" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#7c5cf0"/><stop offset="100%" stop-color="#22c55e"/></linearGradient>
      </defs>
      <path d="{{ $wFill }}" fill="url(#wf)"/>
      <path class="wave-line" d="{{ $wLine }}" fill="none" stroke="url(#ws)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="150" cy="{{ $wPts[3] }}" r="4" fill="#fff"/>
    </svg>
  </div>

  @if(($recentConversations ?? collect())->count() > 0)
  <div class="card anim" style="--i:5">
    <div class="card-head"><span>Quick Send</span><a href="{{ route('chat.index') }}">View all</a></div>
    <div class="quick-row">
      <div class="quick-item"><a href="{{ route('chat.index') }}" class="quick-avatar quick-add">+</a><span>Add</span></div>
      @foreach($recentConversations->take(6) as $conv)
        <div class="quick-item">
          <a href="{{ route('chat.index') }}" class="quick-avatar">
            @if($conv['other_avatar'])<img src="{{ $conv['other_avatar'] }}" alt="">@else{{ strtoupper(substr($conv['other_name'],0,1)) }}@endif
          </a>
          <span>{{ \Illuminate\Support\Str::limit($conv['other_name'], 8, '') }}</span>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  <div class="card anim" style="--i:6">
    <div class="card-head"><span>Recent Gigs</span><a href="{{ route('gigs.index') }}">View all</a></div>
    @forelse($topGigs as $gig)
      <a href="{{ route('gigs.show', $gig->id) }}" class="tx-row">
        <div class="tx-avatar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
        <div class="tx-body"><span class="t">{{ $gig->title }}</span><span class="s">{{ ucfirst($gig->status??'') }}</span></div>
        <span class="tx-amount">₦{{ number_format($gig->amount) }}</span>
      </a>
    @empty
      <p class="empty">No gigs yet.</p>
    @endforelse
  </div>

  <div class="card anim" style="--i:7">
    <div class="card-head"><span>Marketplace</span><a href="{{ route('marketplace.index') }}">Open</a></div>
    <div class="mp-row">
      <div class="mp-item"><span class="v">{{ $myListings }}</span><span class="l">Active Listings</span></div>
      <div class="mp-item mp-soon"><span class="v">Soon</span><span class="l">Sales</span></div>
      <div class="mp-item mp-soon"><span class="v">Soon</span><span class="l">Orders</span></div>
    </div>
  </div>

  <div class="profile-card anim" style="--i:8">
    <div class="profile-photo">
      @if($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="">@else{{ strtoupper(substr($user->name,0,1)) }}@endif
    </div>
    <div class="profile-scrim"></div>
    <div class="profile-info">
      <p class="name">{{ $user->name }}</p>
      <p class="level">{{ $user->workerLevel()['label'] }}</p>
      <div class="stats"><span>{{ $myCompletedGigs }} Gigs</span><span>{{ $avgRating?number_format($avgRating,1):'—' }} ({{ $reviewCount }})</span></div>
      <a href="{{ route('profile.index') }}" class="edit">Edit Profile</a>
    </div>
  </div>

</div>

<nav class="nav">
  <a href="{{ route('dashboard') }}" class="nav-item active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg><span>Home</span></a>
  <a href="{{ route('gigs.index') }}" class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><span>Hustles</span></a>
  <a href="{{ route('marketplace.index') }}" class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg><span>Market</span></a>
  <a href="#" class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg><span>Pulse</span></a>
  <a href="{{ route('profile.index') }}" class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg><span>Profile</span></a>
</nav>

</body>
</html>
