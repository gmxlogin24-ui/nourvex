@extends('layouts.app-authenticated')

@section('title', 'Dashboard')

@section('content')
@php
    $dtxName = auth()->user()?->name ?? 'Guest';
    $dtxFirst = explode(' ', $dtxName)[0];
    $dtxHour = now()->hour;
    $dtxGreeting = $dtxHour < 12 ? 'Good morning' : ($dtxHour < 17 ? 'Good afternoon' : 'Good evening');

    $dtxTrend = $trend ?? [0,0,0,0,0,0];
    $dtxTrendMax = max(1, max($dtxTrend));
    $dtxTrendTotal = array_sum($dtxTrend);

    $dtxStreak = $streakDays ?? 0;
    $dtxMilestone = $nextStreakMilestone ?: 7;
    $dtxStreakPct = $dtxMilestone > 0 ? min(100, round(($dtxStreak / $dtxMilestone) * 100)) : 0;
    $dtxDayLabels = ['M','T','W','T','F','S','S'];
    $dtxFilledDays = min(7, $dtxStreak);

    $dtxWaveSeed = max(1, (int) (($netFlow ?? 0) + ($totalIn ?? 0) + ($totalOut ?? 0)));
    $dtxWaveLines = [];
    foreach ([1.3, 0.9, 1.7] as $k => $mult) {
        $pts = [];
        for ($i = 0; $i <= 6; $i++) {
            $n = sin($i * $mult + ($dtxWaveSeed % 7) + $k * 2) * 18 + cos($i * 0.6 + $k) * 10;
            $pts[] = round(50 - $n, 1);
        }
        $line = '';
        foreach ($pts as $i => $y) {
            $x = $i * 50;
            $line .= ($i === 0 ? "M{$x},{$y} " : "L{$x},{$y} ");
        }
        $dtxWaveLines[] = $line;
    }
@endphp

<div class="dtx-page">

    <div class="dtx-hero-card" id="dtxHeroCard">
        <div class="dtx-hero-bg dtx-hero-bg-gradient"></div>
        <div class="dtx-hero-tint"></div>
        <div class="dtx-hero-content">

            <div class="dtx-balance-top">
                <span>your total balance</span>
                <button type="button" class="dtx-eye-btn" id="dtxEyeBtn"><i data-lucide="eye-off" class="w-4 h-4"></i></button>
            </div>
            <div class="dtx-balance-amount">
                <span id="dtxBalanceReal">₦{{ number_format($balance ?? 0, 2) }}</span>
                <span id="dtxBalanceMasked" style="display:none;">₦••••••</span>
            </div>

            <div class="dtx-hero-extra">
                <div class="dtx-hero-top-row">
                    <span class="dtx-hero-hi">{{ $dtxGreeting }}, {{ $dtxFirst }}</span>
                    <span class="dtx-hero-chip"><i data-lucide="credit-card" class="w-4 h-4"></i></span>
                </div>
                @if(auth()->user()->virtual_account_number ?? false)
                    <p class="dtx-hero-masked">•••• {{ substr(auth()->user()->virtual_account_number, -4) }} &middot; {{ auth()->user()->virtual_account_bank }}</p>
                @endif
                <div class="dtx-hero-stat-row">
                    <div class="dtx-hero-stat">
                        <i data-lucide="gem" class="w-3.5 h-3.5"></i>
                        <span>{{ number_format($qCoinBalance ?? 0) }} Q</span>
                    </div>
                    <div class="dtx-hero-stat">
                        <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                        <span>{{ $dtxStreak }}d streak</span>
                    </div>
                    <div class="dtx-hero-stat">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        <span>₦{{ number_format($dtxTrendTotal) }} earned</span>
                    </div>
                </div>
            </div>

            <div class="dtx-balance-actions">
                <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dtx-btn dtx-btn-ghost" onclick="event.stopPropagation();">request</a>
                <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dtx-btn dtx-btn-solid" onclick="event.stopPropagation();">send</a>
            </div>

            <span class="dtx-hero-toggle-hint"><i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></span>
        </div>
    </div>

    <div class="dtx-section">
        <div class="dtx-section-head"><span>vtu services</span></div>
        <div class="dtx-vtu-row">
            <a href="{{ route('services.index') }}" class="dtx-vtu-item">
                <span class="dtx-vtu-icon dtx-vtu-1"><i data-lucide="smartphone" class="w-5 h-5"></i></span>
                <span>Airtime</span>
            </a>
            <a href="{{ route('services.index') }}" class="dtx-vtu-item">
                <span class="dtx-vtu-icon dtx-vtu-2"><i data-lucide="wifi" class="w-5 h-5"></i></span>
                <span>Data</span>
            </a>
            <a href="{{ route('services.index') }}" class="dtx-vtu-item">
                <span class="dtx-vtu-icon dtx-vtu-3"><i data-lucide="tv" class="w-5 h-5"></i></span>
                <span>Cable</span>
            </a>
            <a href="{{ route('services.index') }}" class="dtx-vtu-item">
                <span class="dtx-vtu-icon dtx-vtu-4"><i data-lucide="plug-zap" class="w-5 h-5"></i></span>
                <span>Electric</span>
            </a>
            <a href="{{ route('marketplace.index') }}" class="dtx-vtu-item">
                <span class="dtx-vtu-icon dtx-vtu-5"><i data-lucide="shopping-bag" class="w-5 h-5"></i></span>
                <span>Market</span>
            </a>
            <a href="{{ route('referrals.index') }}" class="dtx-vtu-item">
                <span class="dtx-vtu-icon dtx-vtu-6"><i data-lucide="users" class="w-5 h-5"></i></span>
                <span>Refer</span>
            </a>
        </div>
    </div>

    <div class="dtx-section dtx-insights-row">
        <div class="dtx-streak-card">
            <div class="dtx-streak-top">
                <div class="dtx-streak-flame"><i data-lucide="flame" class="w-4 h-4"></i></div>
                <div class="dtx-streak-meta">
                    <span class="l">STREAK</span>
                    <span class="v">{{ $dtxStreak }}d</span>
                </div>
            </div>
            <div class="dtx-streak-days">
                @foreach($dtxDayLabels as $i => $label)
                    <div class="dtx-streak-dot {{ $i < $dtxFilledDays ? 'dtx-streak-done' : ($i === $dtxFilledDays ? 'dtx-streak-current' : '') }}">
                        @if($i < $dtxFilledDays)
                            <i data-lucide="check" class="w-2 h-2"></i>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="dtx-streak-track"><div class="dtx-streak-fill" style="width: {{ $dtxStreakPct }}%;"></div></div>
        </div>

        <div class="dtx-flowstats-card" id="dtxFlowStats">
            <div class="dtx-fs-head">
                <span>Flow &amp; Earnings</span>
                <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
            </div>
            <svg class="dtx-wave-svg" viewBox="0 0 300 100" preserveAspectRatio="none">
                <path d="{{ $dtxWaveLines[0] }}" fill="none" stroke="#ffd23f" stroke-width="3" stroke-linecap="round" opacity="0.9"/>
                <path d="{{ $dtxWaveLines[1] }}" fill="none" stroke="#2ba884" stroke-width="3" stroke-linecap="round" opacity="0.9"/>
                <path d="{{ $dtxWaveLines[2] }}" fill="none" stroke="#4dabf7" stroke-width="3" stroke-linecap="round" opacity="0.9"/>
            </svg>
            <div class="dtx-fs-bars">
                @foreach($dtxTrend as $v)
                    <span style="height: {{ max(10, round(($v / $dtxTrendMax) * 100)) }}%;"></span>
                @endforeach
            </div>
            <div class="dtx-fs-footer">
                <span>Net ₦{{ number_format($netFlow ?? 0) }}</span>
                <span>Earned ₦{{ number_format($dtxTrendTotal) }}</span>
            </div>
        </div>
    </div>

    <div class="dtx-section">
        <div class="dtx-section-head">
            <span>marketplace</span>
            <a href="{{ route('marketplace.index') }}">view all</a>
        </div>
        @forelse(($topListings ?? collect())->take(4) as $i => $item)
        @php $dtxNothing = null; @endphp
        @empty
            <p class="dtx-empty">No listings yet.</p>
        @endforelse
        @if(($topListings ?? collect())->count() > 0)
        <div class="dtx-market-row" id="dtxMarketRow">
            @foreach(($topListings ?? collect())->take(4) as $i => $item)
                <a href="{{ route('marketplace.show', $item->id) }}" class="dtx-market-card dtx-anim" style="--i:{{ $i }}">
                    <div class="dtx-market-img">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                        @else
                            <div class="dtx-market-img-placeholder"><i data-lucide="image" class="w-5 h-5"></i></div>
                        @endif
                        <span class="dtx-market-badge">{{ ucfirst($item->condition ?? 'New') }}</span>
                    </div>
                    <div class="dtx-market-body">
                        <span class="dtx-market-price">₦{{ number_format($item->amount) }}</span>
                        <span class="dtx-market-title">{{ \Illuminate\Support\Str::limit($item->title, 22) }}</span>
                        <span class="dtx-market-seller"><i data-lucide="store" class="w-3 h-3"></i> {{ \Illuminate\Support\Str::limit($item->seller_name ?? $item->store_name ?? 'Seller', 14) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="dtx-section" style="margin-bottom:40px;">
        <div class="dtx-section-head">
            <span>recommended gigs</span>
            <a href="{{ route('gigs.index') }}">view all</a>
        </div>
        <div class="dtx-history-list">
            @forelse(($topGigs ?? collect())->take(2) as $gig)
                <a href="{{ route('gigs.show', $gig->id) }}" class="dtx-history-row">
                    <div class="dtx-history-left">
                        <div class="dtx-history-icon">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </div>
                        <div class="dtx-history-info">
                            <span class="dtx-history-name">{{ \Illuminate\Support\Str::limit($gig->title, 22) }}</span>
                            <span class="dtx-history-date">{{ ucfirst($gig->status ?? '') }}</span>
                        </div>
                    </div>
                    <span class="dtx-history-amount dtx-positive">₦{{ number_format($gig->amount) }}</span>
                </a>
            @empty
                <p class="dtx-empty">No gigs to recommend yet.</p>
            @endforelse
        </div>
    </div>


    <div class="dtx-section">
        <a href="{{ route('concierge.show') }}" class="dtx-ai-card">
            <div class="dtx-ai-inner">
                <div class="dtx-ai-top-row">
                    <span class="dtx-ai-placeholder">Ask anything</span>
                    <div class="dtx-ai-pulse"><span></span><span></span><span></span><span></span><span></span></div>
                </div>
                <div class="dtx-ai-actions">
                    <span class="dtx-ai-btn dtx-ai-btn-icon"><i data-lucide="plus" class="w-4 h-4"></i></span>
                    <span class="dtx-ai-btn"><i data-lucide="sparkles" class="w-4 h-4"></i> Ask Concierge</span>
                    <span class="dtx-ai-btn"><i data-lucide="lightbulb" class="w-4 h-4"></i> Ideas</span>
                    <span class="dtx-ai-btn dtx-ai-btn-icon"><i data-lucide="more-horizontal" class="w-4 h-4"></i></span>
                </div>
            </div>
        </a>
    </div>

    <div class="dtx-section">
        <div class="dtx-section-head">
            <span>history</span>
            <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}">view all</a>
        </div>
        <div class="dtx-history-list">
            @forelse($recentTx ?? [] as $tx)
                @php $dtxPositive = ($tx->amount ?? 0) >= 0; @endphp
                <div class="dtx-history-row">
                    <div class="dtx-history-left">
                        <div class="dtx-history-icon">
                            <i data-lucide="{{ $dtxPositive ? 'arrow-down-left' : 'arrow-up-right' }}" class="w-4 h-4"></i>
                        </div>
                        <div class="dtx-history-info">
                            <span class="dtx-history-name">{{ ucfirst(str_replace('_', ' ', $tx->type ?? 'transaction')) }}</span>
                            <span class="dtx-history-date">{{ $tx->created_at ? $tx->created_at->format('d/m/Y') : '' }}</span>
                        </div>
                    </div>
                    <span class="dtx-history-amount {{ $dtxPositive ? 'dtx-positive' : 'dtx-negative' }}">
                        {{ $dtxPositive ? '+' : '-' }}₦{{ number_format(abs($tx->amount ?? 0)) }}
                    </span>
                </div>
            @empty
                <p class="dtx-empty">No transactions yet.</p>
            @endforelse
        </div>
    </div>

</div>

<style>
.dtx-page{max-width:420px;margin:0 auto;padding:10px 10px 130px;color:#111;background:#fff;min-height:100vh;box-sizing:border-box;}

.dtx-hero-card{position:relative;overflow:hidden;border-radius:28px;padding:22px;margin:0;min-height:190px;box-sizing:border-box;cursor:pointer;transition:min-height 0.35s ease;animation:dtxGlowCycle 32s steps(1) infinite;}
.dtx-hero-card.dtx-expanded{min-height:430px;}
@keyframes dtxGlowCycle{
    0%,24.9%{box-shadow:0 8px 22px -6px rgba(43,168,132,0.55),0 0 0 1px rgba(43,168,132,0.35),5px 5px 14px rgba(0,0,0,0.6),-4px -4px 10px rgba(255,255,255,0.03);}
    25%,49.9%{box-shadow:0 8px 22px -6px rgba(77,171,247,0.55),0 0 0 1px rgba(77,171,247,0.35),5px 5px 14px rgba(0,0,0,0.6),-4px -4px 10px rgba(255,255,255,0.03);}
    50%,74.9%{box-shadow:0 8px 22px -6px rgba(132,94,247,0.55),0 0 0 1px rgba(132,94,247,0.35),5px 5px 14px rgba(0,0,0,0.6),-4px -4px 10px rgba(255,255,255,0.03);}
    75%,100%{box-shadow:0 8px 22px -6px rgba(255,210,63,0.55),0 0 0 1px rgba(255,210,63,0.35),5px 5px 14px rgba(0,0,0,0.6),-4px -4px 10px rgba(255,255,255,0.03);}
}
.dtx-hero-bg{position:absolute;inset:0;background-size:cover;background-position:center;}
.dtx-hero-bg-gradient{background-image:none;background:#000;}
.dtx-hero-tint{position:absolute;inset:0;background:linear-gradient(160deg, rgba(0,0,0,0.4), rgba(0,0,0,0.7));}
.dtx-hero-content{position:relative;z-index:1;display:flex;flex-direction:column;gap:14px;color:#fff;}
.dtx-hero-extra{max-height:0;opacity:0;overflow:hidden;transition:max-height 0.4s ease, opacity 0.3s ease, padding-top 0.4s ease;display:flex;flex-direction:column;gap:12px;box-sizing:border-box;}
.dtx-hero-card.dtx-expanded .dtx-hero-extra{max-height:220px;opacity:1;padding-top:14px;border-top:1px solid rgba(255,255,255,0.1);}
.dtx-hero-top-row{display:flex;justify-content:space-between;align-items:center;gap:10px;}
.dtx-hero-hi{font-size:12px;color:rgba(255,255,255,0.65);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dtx-hero-chip{width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;}
.dtx-hero-masked{font-size:11px;color:rgba(255,255,255,0.5);margin:0;letter-spacing:0.04em;}
.dtx-hero-stat-row{display:flex;gap:8px;flex-wrap:wrap;}
.dtx-hero-stat{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,0.1);backdrop-filter:blur(6px);border-radius:999px;padding:7px 11px;font-size:10.5px;font-weight:600;color:#fff;border:1px solid rgba(255,255,255,0.08);white-space:nowrap;}
.dtx-hero-toggle-hint{position:absolute;bottom:10px;right:16px;color:rgba(255,255,255,0.4);transition:transform 0.3s ease;}
.dtx-hero-card.dtx-expanded .dtx-hero-toggle-hint{transform:rotate(180deg);}
.dtx-balance-top{display:flex;align-items:center;justify-content:space-between;font-size:12px;color:rgba(255,255,255,0.6);margin-bottom:10px;}
.dtx-eye-btn{background:none;border:none;color:rgba(255,255,255,0.6);padding:0;}
.dtx-balance-amount{font-size:38px;font-weight:800;margin-bottom:8px;}

.dtx-balance-actions{display:flex;gap:10px;}
.dtx-btn{flex:1;text-align:center;padding:13px;border-radius:999px;font-size:13px;font-weight:700;text-decoration:none;text-transform:lowercase;}
.dtx-btn-ghost{background:rgba(255,255,255,0.1);color:#fff;}
.dtx-btn-solid{background:#fff;color:#0a1f1a;}

.dtx-section{margin:0 0 28px;}
.dtx-section-head{display:flex;justify-content:space-between;align-items:baseline;font-size:14px;font-weight:700;margin-bottom:14px;}
.dtx-section-head a{font-size:11px;font-weight:500;color:rgba(0,0,0,0.45);text-decoration:none;}

.dtx-insights-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.dtx-streak-card{background:#141414;border-radius:18px;padding:12px 10px;height:132px;box-sizing:border-box;display:flex;flex-direction:column;justify-content:space-between;border:1px solid rgba(255,255,255,0.08);}
.dtx-streak-top{display:flex;align-items:center;gap:6px;}
.dtx-streak-flame{width:24px;height:24px;border-radius:50%;background:rgba(255,71,87,0.15);color:#ff4757;display:flex;align-items:center;justify-content:center;flex-shrink:0;animation:dtxFlamePulse 1.8s ease-in-out infinite;}
@keyframes dtxFlamePulse{0%,100%{box-shadow:0 0 0 0 rgba(255,71,87,0.55);}50%{box-shadow:0 0 0 7px rgba(255,71,87,0);}}
.dtx-streak-meta{display:flex;flex-direction:column;}
.dtx-streak-meta .l{font-size:8px;letter-spacing:0.06em;color:rgba(255,255,255,0.4);}
.dtx-streak-meta .v{font-size:13px;font-weight:800;}
.dtx-streak-days{display:flex;justify-content:space-between;gap:2px;}
.dtx-streak-dot{width:14px;height:14px;border-radius:50%;background:#232323;display:flex;align-items:center;justify-content:center;color:#fff;}
.dtx-streak-done{background:#2ba884;}
.dtx-streak-current{border:2px solid #2ba884;}
.dtx-streak-track{width:100%;height:5px;border-radius:999px;background:#232323;overflow:hidden;}
.dtx-streak-fill{height:100%;background:linear-gradient(90deg,#2ba884,#3fd67a);border-radius:999px;}

.dtx-flowstats-card{background:radial-gradient(circle at 25% 15%, #3c3c3c 0%, #1b1b1b 45%, #060606 100%);border:1px solid rgba(255,255,255,0.1);border-radius:18px;padding:12px 10px;height:132px;box-sizing:border-box;overflow:hidden;cursor:pointer;transition:height 0.3s ease, box-shadow 0.3s ease;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 0 50px -8px rgba(200,200,220,0.35), 0 0 0 1px rgba(255,255,255,0.04) inset;}
.dtx-flowstats-card.dtx-expanded{grid-column:1/-1;height:260px;box-shadow:0 0 70px -6px rgba(200,200,220,0.5), 0 0 0 1px rgba(255,255,255,0.06) inset;}
.dtx-fs-head{display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:700;color:rgba(255,255,255,0.85);}
.dtx-wave-svg{width:100%;height:44px;}
.dtx-flowstats-card.dtx-expanded .dtx-wave-svg{height:80px;}
.dtx-wave-svg path{stroke-dasharray:10 7;animation:dtxFlowDash 1s linear infinite;}
@keyframes dtxFlowDash{to{stroke-dashoffset:-34;}}
.dtx-fs-bars{display:flex;align-items:flex-end;gap:3px;height:28px;}
.dtx-flowstats-card.dtx-expanded .dtx-fs-bars{height:56px;}
.dtx-fs-bars span{flex:1;background:rgba(255,255,255,0.25);border-radius:2px;min-height:10%;}
.dtx-fs-footer{display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,0.5);}

.dtx-vtu-row{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;}
.dtx-vtu-item{display:flex;flex-direction:column;align-items:center;gap:7px;text-decoration:none;color:rgba(0,0,0,0.7);font-size:9.5px;font-weight:600;}
.dtx-vtu-icon{width:44px;height:44px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:linear-gradient(145deg,#1c1c1c,#0a0a0a);box-shadow:5px 5px 10px rgba(0,0,0,0.7),-4px -4px 9px rgba(255,255,255,0.045),inset 0 1px 1px rgba(255,255,255,0.04);transition:transform 0.15s ease;}
.dtx-vtu-item:active .dtx-vtu-icon{transform:scale(0.93);box-shadow:inset 3px 3px 6px rgba(0,0,0,0.7),inset -3px -3px 6px rgba(255,255,255,0.03);}
.dtx-vtu-1{color:#4dabf7;}
.dtx-vtu-2{color:#2ba884;}
.dtx-vtu-3{color:#af52de;}
.dtx-vtu-4{color:#ff9500;}
.dtx-vtu-5{color:#ffd23f;}
.dtx-vtu-6{color:#ff6b6b;}

.dtx-services-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.dtx-service{display:flex;flex-direction:column;align-items:center;text-align:center;gap:8px;text-decoration:none;color:#fff;background:#141414;border-radius:20px;padding:16px 10px;box-shadow:0 10px 22px rgba(0,0,0,0.6),inset 0 1px 1px rgba(255,255,255,0.05);transition:transform 0.15s ease;}
.dtx-service:active{transform:scale(0.95);}
.dtx-service-icon{width:56px;height:56px;border-radius:18px;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 16px rgba(0,0,0,0.35);}
.dtx-service-title{font-size:12px;font-weight:700;}
.dtx-service-sub{font-size:9px;color:rgba(255,255,255,0.45);}

@keyframes dtxBorderSpin{to{transform:rotate(360deg);}}
.dtx-ai-card{position:relative;display:block;border-radius:26px;padding:1.5px;overflow:hidden;text-decoration:none;box-shadow:0 14px 32px -12px rgba(0,0,0,0.65);}
.dtx-ai-card::before{content:'';position:absolute;inset:-150%;background:conic-gradient(from 0deg,#ff4757,#ffd23f,#2ba884,#4dabf7,#ff4757);animation:dtxBorderSpin 4s linear infinite;}
.dtx-ai-inner{position:relative;z-index:1;background:linear-gradient(160deg,#161616,#0a0a0a);border-top:1px solid rgba(255,255,255,0.07);border-radius:21px;padding:18px;}
.dtx-ai-top-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.dtx-ai-placeholder{display:block;color:rgba(255,255,255,0.5);font-size:14px;}
.dtx-ai-pulse{display:flex;align-items:flex-end;gap:3px;height:20px;}
.dtx-ai-pulse span{width:3px;background:linear-gradient(180deg,#4dabf7,#2ba884);border-radius:2px;animation:dtxAiPulse 1s ease-in-out infinite;}
.dtx-ai-pulse span:nth-child(1){animation-delay:0s;}
.dtx-ai-pulse span:nth-child(2){animation-delay:0.15s;}
.dtx-ai-pulse span:nth-child(3){animation-delay:0.3s;}
.dtx-ai-pulse span:nth-child(4){animation-delay:0.45s;}
.dtx-ai-pulse span:nth-child(5){animation-delay:0.6s;}
@keyframes dtxAiPulse{0%,100%{height:5px;opacity:0.5;}50%{height:20px;opacity:1;}}
.dtx-ai-actions{display:flex;gap:8px;flex-wrap:wrap;}
.dtx-ai-btn{display:flex;align-items:center;gap:6px;background:linear-gradient(145deg,#1c1c1c,#0a0a0a);color:rgba(255,255,255,0.85);font-size:12px;font-weight:600;padding:9px 14px;border-radius:999px;box-shadow:4px 4px 8px rgba(0,0,0,0.6),-3px -3px 7px rgba(255,255,255,0.045);transition:transform 0.15s ease;}
.dtx-ai-btn:active{transform:scale(0.94);box-shadow:inset 2px 2px 5px rgba(0,0,0,0.7),inset -2px -2px 5px rgba(255,255,255,0.03);}
.dtx-ai-btn-icon{padding:9px;}

@keyframes dtxFadeInUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.dtx-anim{animation:dtxFadeInUp 0.5s ease both;animation-delay:calc(var(--i, 0) * 0.08s);}
.dtx-market-row{display:flex;gap:12px;overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:6px;scrollbar-width:none;}
.dtx-market-row::-webkit-scrollbar{display:none;}
.dtx-market-card{flex:0 0 132px;scroll-snap-align:start;display:flex;flex-direction:column;text-decoration:none;color:#fff;background:#161616;border-radius:18px;overflow:hidden;transition:transform 0.15s ease;}
.dtx-market-card:active{transform:scale(0.96);}
.dtx-market-img{position:relative;aspect-ratio:1;background:#232323;}
.dtx-market-img img{width:100%;height:100%;object-fit:cover;}
.dtx-market-img-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.25);}
.dtx-market-badge{position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);color:#fff;font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;padding:3px 7px;border-radius:999px;}
.dtx-market-body{padding:9px 10px 11px;display:flex;flex-direction:column;gap:2px;}
.dtx-market-price{font-size:13px;font-weight:800;}
.dtx-market-title{font-size:10.5px;color:rgba(255,255,255,0.6);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dtx-market-seller{font-size:9px;color:rgba(255,255,255,0.35);display:flex;align-items:center;gap:3px;margin-top:2px;}

.dtx-history-list{display:flex;flex-direction:column;gap:10px;}
.dtx-history-row{display:flex;align-items:center;justify-content:space-between;background:#161616;border-radius:16px;padding:14px 16px;text-decoration:none;color:#fff;}
.dtx-history-left{display:flex;align-items:center;gap:12px;}
.dtx-history-icon{width:38px;height:38px;border-radius:50%;background:#232323;display:flex;align-items:center;justify-content:center;}
.dtx-history-info{display:flex;flex-direction:column;}
.dtx-history-name{font-size:13px;font-weight:600;}
.dtx-history-date{font-size:11px;color:rgba(255,255,255,0.4);}
.dtx-history-amount{font-size:13px;font-weight:700;}
.dtx-positive{color:#2ba884;}
.dtx-negative{color:#ff6b6b;}
.dtx-empty{color:rgba(0,0,0,0.4);font-size:12px;}

@media (min-width: 900px) {
    .dtx-page{max-width:960px;padding:88px 24px 60px;}
    .dtx-hero-card{margin:0 0 28px;padding:32px;}
    .dtx-balance-amount{font-size:36px;}
    .dtx-section{margin:0 0 34px;}
    .dtx-insights-row{gap:18px;}
    .dtx-streak-card{height:220px;padding:22px 20px;border-radius:24px;}
    .dtx-flowstats-card{height:220px !important;padding:22px 20px;border-radius:24px;}
    .dtx-flowstats-card .dtx-wave-svg{height:80px;}
    .dtx-flowstats-card .dtx-fs-bars{height:56px;}
    .dtx-services-row{gap:16px;}
    .dtx-service-icon{width:64px;height:64px;border-radius:20px;}
    .dtx-ai-inner{padding:26px;}
    .dtx-market-card{flex:0 0 200px;}
    .dtx-history-row{padding:18px 20px;}
}
</style>

<script>
(function(){
    var btn = document.getElementById('dtxEyeBtn');
    var real = document.getElementById('dtxBalanceReal');
    var masked = document.getElementById('dtxBalanceMasked');
    if (btn) {
        var shown = true;
        btn.addEventListener('click', function(){
            shown = !shown;
            real.style.display = shown ? '' : 'none';
            masked.style.display = shown ? 'none' : '';
            btn.innerHTML = shown
                ? '<i data-lucide="eye-off" class="w-4 h-4"></i>'
                : '<i data-lucide="eye" class="w-4 h-4"></i>';
            if (window.lucide) window.lucide.createIcons();
        });
    }

    var fs = document.getElementById('dtxFlowStats');
    if (fs) {
        fs.addEventListener('click', function(){
            fs.classList.toggle('dtx-expanded');
        });
    }

    var hero = document.getElementById('dtxHeroCard');
    if (hero) {
        hero.addEventListener('click', function(e){
            if (e.target.closest('.dtx-btn') || e.target.closest('#dtxEyeBtn')) return;
            hero.classList.toggle('dtx-expanded');
        });
    }
})();

(function(){
    var row = document.getElementById('dtxMarketRow');
    if (!row) return;
    var paused = false;
    var pauseTimer = null;

    function pauseAWhile(){
        paused = true;
        clearTimeout(pauseTimer);
        pauseTimer = setTimeout(function(){ paused = false; }, 3000);
    }
    ['touchstart','wheel','pointerdown'].forEach(function(evt){
        row.addEventListener(evt, pauseAWhile, { passive: true });
    });

    setInterval(function(){
        if (paused) return;
        var card = row.querySelector('.dtx-market-card');
        if (!card) return;
        var step = card.offsetWidth + 12;
        var maxScroll = row.scrollWidth - row.clientWidth;
        if (row.scrollLeft >= maxScroll - 4) {
            row.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            row.scrollBy({ left: step, behavior: 'smooth' });
        }
    }, 2600);
})();
</script>
@endsection
