@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-6xl mx-auto p-6">

    <div class="mb-6 reveal-on-scroll flex items-center justify-between flex-wrap gap-3 scroll-hide-under" id="dashboardWelcomeHeader">
        <div>
            <h1 class="text-2xl font-semibold hoverfade-text">Welcome, {{ $user->name ?? 'Student' }}</h1>
            <p class="text-muted text-sm">Here's what's happening today</p>
        </div>
        @if(($streakDays ?? 0) > 0)
        <div class="trust-pill desktop-streak-pill">
            <i data-lucide="flame" class="w-4 h-4 text-accent"></i>
            <span class="font-semibold">{{ $streakDays }} day{{ $streakDays == 1 ? '' : 's' }}</span>
            <span class="text-muted">streak</span>
        </div>
        @endif
    </div>

    @php
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
    @endphp

    <div class="desktop-plan-pill">
    @if($currentSubscription)
    <a href="{{ route('subscriptions.index') }}" class="trust-pill mb-6 reveal-on-scroll" style="width:fit-content;text-decoration:none;">
        <i class="ph-fill ph-crown" style="font-size:1rem;color:var(--color-accent);"></i>
        <span class="font-semibold">{{ \App\Models\Subscription::PLANS[$currentSubscription->plan]['label'] }}</span>
        <span class="text-muted">renews {{ $currentSubscription->expires_at->format('M j, Y') }}</span>
        @if($currentSubscription->isExpiringSoon())
        <span class="text-red-500 text-xs font-medium">· Renew soon</span>
        @endif
    </a>
    @else
    <a href="{{ route('subscriptions.index') }}" class="trust-pill mb-6 reveal-on-scroll" style="width:fit-content;text-decoration:none;">
        <i class="ph-fill ph-crown" style="font-size:1rem;"></i>
        <span class="text-muted">Free plan — </span>
        <span class="text-accent font-medium">Upgrade to post hustles & listings</span>
    </a>
    @endif
    </div>

    <div class="mobile-wallet-hero reveal-on-scroll" id="mobileWalletHero">
        <div class="mwh-top">
            <p class="mwh-welcome">Welcome, {{ $user->name ?? 'Student' }}</p>
            <span class="mwh-wallet-select">Wallet 1 <i data-lucide="chevron-down" class="w-3 h-3"></i></span>
        </div>
        <div class="mwh-cycle" id="mwhCycle">
            <p class="mwh-label">Total Balance</p>
            <p class="mwh-amount" id="mwhAmount">₦0</p>
            <p class="mwh-sub">{{ ($myOpenGigs ?? 0) + ($myListings ?? 0) }} active posts</p>
        </div>
        <div class="mwh-stats-row">
            <div class="mwh-stat">
                <span class="mwh-stat-label">Hustle Income</span>
                <span class="mwh-stat-value" id="mwhHustle">₦0</span>
            </div>
            <div class="mwh-stat">
                <span class="mwh-stat-label">Referral Reach</span>
                <span class="mwh-stat-value" id="mwhReferral">0</span>
            </div>
        </div>
        <div class="mwh-actions">
            <a href="{{ route('wallet.index') }}" class="mwh-pill"><i data-lucide="arrow-down-left" class="w-4 h-4"></i> Top Up</a>
            <a href="{{ route('wallet.index') }}" class="mwh-plus"><i data-lucide="plus" class="w-5 h-5"></i></a>
            <a href="{{ route('wallet.index') }}" class="mwh-pill"><i data-lucide="arrow-up-right" class="w-4 h-4"></i> Withdraw</a>
        </div>
        <span class="mwh-sync"><span class="live-dot"></span> Synchronized</span>
    </div>
    <script>
    (function(){
        var amountEl = document.getElementById('mwhAmount');
        var hustleEl = document.getElementById('mwhHustle');
        var referralEl = document.getElementById('mwhReferral');
        if (!amountEl) return;

        function countUp(el, target, isInt){
            var start = performance.now(), dur = 1100;
            function tick(now){
                var p = Math.min(1, (now - start) / dur);
                var eased = 1 - Math.pow(1 - p, 3);
                var val = target * eased;
                el.textContent = isInt
                    ? Math.floor(val).toLocaleString()
                    : '\u20a6' + val.toLocaleString(undefined, {maximumFractionDigits: 2});
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }

        countUp(amountEl, {{ (float) ($balance ?? 0) }}, false);
        countUp(hustleEl, {{ (float) ($earnings ?? 0) }}, false);
        countUp(referralEl, {{ (float) ($referralCount ?? 0) }}, true);
    })();
    </script>

    <div class="mobile-only-block">
        <div class="mb-4">
    @if($currentSubscription)
    <a href="{{ route('subscriptions.index') }}" class="trust-pill mb-6 reveal-on-scroll" style="width:fit-content;text-decoration:none;">
        <i class="ph-fill ph-crown" style="font-size:1rem;color:var(--color-accent);"></i>
        <span class="font-semibold">{{ \App\Models\Subscription::PLANS[$currentSubscription->plan]['label'] }}</span>
        <span class="text-muted">renews {{ $currentSubscription->expires_at->format('M j, Y') }}</span>
        @if($currentSubscription->isExpiringSoon())
        <span class="text-red-500 text-xs font-medium">· Renew soon</span>
        @endif
    </a>
    @else
    <a href="{{ route('subscriptions.index') }}" class="trust-pill mb-6 reveal-on-scroll" style="width:fit-content;text-decoration:none;">
        <i class="ph-fill ph-crown" style="font-size:1rem;"></i>
        <span class="text-muted">Free plan — </span>
        <span class="text-accent font-medium">Upgrade to post hustles & listings</span>
    </a>
    @endif
        </div>
        <div class="glass-card p-4 mb-4 flex items-center gap-3" id="dashAdSlotMobile" style="border:1px dashed var(--border-color);display:none;">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:var(--color-card);">
                <i data-lucide="megaphone" class="w-5 h-5 text-muted"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium">Sponsored</p>
                <p class="text-muted text-xs">Ad space — nothing running here yet</p>
            </div>
        </div>
        @if(($streakDays ?? 0) > 0)
        <div class="trust-pill" style="display:inline-flex;">
            <i data-lucide="flame" class="w-4 h-4 text-accent"></i>
            <span class="font-semibold">{{ $streakDays }} day{{ $streakDays == 1 ? '' : 's' }}</span>
            <span class="text-muted">streak</span>
        </div>
        @endif
    </div>

    <!-- Quick actions: compact icon buttons -->
    <div class="mb-6 reveal-on-scroll">
        <h2 class="font-semibold mb-3">Quick Actions</h2>
        <div class="quick-icon-row">
            <a href="{{ route('services.index') }}" class="quick-icon-btn" data-color="orange">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="zap" class="w-5 h-5"></i></span>
                <span>Airtime</span>
            </a>
            <a href="{{ route('services.index') }}" class="quick-icon-btn" data-color="green">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="wifi" class="w-5 h-5"></i></span>
                <span>Data</span>
            </a>
            <a href="{{ route('services.index') }}" class="quick-icon-btn" data-color="orange">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="tv" class="w-5 h-5"></i></span>
                <span>Cable TV</span>
            </a>
            <a href="{{ route('services.index') }}" class="quick-icon-btn" data-color="yellow">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="plug-zap" class="w-5 h-5"></i></span>
                <span>Electricity</span>
            </a>
            <a href="{{ route('gigs.index') }}" class="quick-icon-btn" data-color="cyan">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="briefcase" class="w-5 h-5"></i></span>
                <span>Hustles</span>
            </a>
            <a href="{{ route('marketplace.index') }}" class="quick-icon-btn" data-color="purple">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="shopping-bag" class="w-5 h-5"></i></span>
                <span>Market</span>
            </a>
            <a href="{{ route('referrals.index') }}" class="quick-icon-btn" data-color="pink">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="users" class="w-5 h-5"></i></span>
                <span>Referral</span>
            </a>
            <a href="{{ route('subscriptions.index') }}" class="quick-icon-btn" data-color="pink">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="crown" class="w-5 h-5"></i></span>
                <span>Upgrade</span>
            </a>
            <a href="{{ route('news.index') }}" class="quick-icon-btn" data-color="blue">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="newspaper" class="w-5 h-5"></i></span>
                <span>News</span>
            </a>
            <a href="{{ route('money-tracker.index') }}" class="quick-icon-btn" data-color="green">
                <span class="quick-icon-circle" data-face-cursor><i data-lucide="wallet" class="w-5 h-5"></i></span>
                <span>Money Tracker</span>
            </a>
        </div>
    </div>
    <div class="dash-header-row reveal-on-scroll">
        <h2 class="text-xl font-bold">Account statistics</h2>
        <div class="dash-tabs">
            <span class="dash-tab active">30 days</span>
            <span class="dash-tab">90 days</span>
            <span class="dash-tab">6 months</span>
            <span class="dash-tab">12 months</span>
        </div>
    </div>

    <div class="kpi-row reveal-on-scroll">
        <div class="kpi-tile featured ledger-card ledger-folder" id="ledgerCard">
            <div class="folder-back"></div>
            <span class="folder-tab"></span>
            <div class="folder-stack-edge"></div>
            <div class="folder-glow-clip"><div class="ledger-glow"></div></div>
            <div class="folder-card-slot" id="ledgerCardSlot">
                <div class="ledger-track" id="ledgerTrack">
                <div class="ledger-page">
                    <span class="ledger-card-brand">PULSE</span>
                    <div class="ledger-chip-row">
                        <span class="ledger-chip"></span>
                        <i data-lucide="wifi" class="w-4 h-4 ledger-contactless"></i>
                    </div>
                    <div class="ledger-top"><span class="ledger-pulse"></span><span class="ledger-eyebrow">Total Balance</span></div>
                    <p class="ledger-value" data-target="{{ $balance ?? 0 }}">₦0</p>
                    <svg class="ledger-spark" viewBox="0 0 100 30" preserveAspectRatio="none">
                        <polyline points="@php $t = $trend ?? [0,0,0,0,0]; $max = max($t ?: [1]); $n = max(1, count($t)-1); echo collect($t)->values()->map(function($v,$i) use ($max,$n) { $x = $i * (100/$n); $y = 30 - (($v/max(1,$max))*26); return round($x,1).','.round($y,1); })->implode(' '); @endphp" />
                    </svg>
                    <div class="ledger-bottom-row">
                        <span class="ledger-card-mask">•••• {{ str_pad((string) (auth()->id() ?? 0), 4, '0', STR_PAD_LEFT) }}</span>
                        <p class="ledger-caption">{{ ($myOpenGigs ?? 0) + ($myListings ?? 0) }} active posts</p>
                    </div>
                </div>
                <div class="ledger-page" data-tint="blue">
                    <span class="ledger-card-brand">PULSE</span>
                    <div class="ledger-chip-row">
                        <span class="ledger-chip"></span>
                        <i data-lucide="wifi" class="w-4 h-4 ledger-contactless"></i>
                    </div>
                    <div class="ledger-top"><span class="ledger-pulse" style="background:#6ea8ff;"></span><span class="ledger-eyebrow">Hustle Income</span></div>
                    <p class="ledger-value" data-target="{{ $earnings ?? 0 }}">₦0</p>
                    <div class="ledger-bottom-row">
                        <span class="ledger-card-mask">HUSTLES</span>
                        <p class="ledger-caption">{{ $myCompletedGigs ?? 0 }} hustles completed</p>
                    </div>
                </div>
                <div class="ledger-page" data-tint="green">
                    <span class="ledger-card-brand">PULSE</span>
                    <div class="ledger-chip-row">
                        <span class="ledger-chip"></span>
                        <i data-lucide="wifi" class="w-4 h-4 ledger-contactless"></i>
                    </div>
                    <div class="ledger-top"><span class="ledger-pulse" style="background:#4ade80;"></span><span class="ledger-eyebrow">Referral Reach</span></div>
                    <p class="ledger-value" data-target="{{ $referralCount ?? 0 }}" data-format="int">0</p>
                    <div class="ledger-bottom-row">
                        <span class="ledger-card-mask">REFS</span>
                        <p class="ledger-caption">active referrals right now</p>
                    </div>
                </div>
            </div>
            </div>
            <div class="folder-front"></div>
            <div class="ledger-dots" id="ledgerDots"><span class="active"></span><span></span><span></span></div>
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top">
                <div class="kpi-tile-icon" data-face-cursor><i data-lucide="users" class="w-4 h-4"></i></div>
            </div>
            <p class="kpi-tile-value">{{ $referralCount ?? 0 }}</p>
            <p class="kpi-tile-label">Active Referrals</p>
            @if($canEarnReferralBonus ?? false)
            <p class="text-xs text-muted mt-1">₦{{ number_format($referralEarnings ?? 0) }} earned from referrals</p>
            @else
            <p class="text-xs mt-1" style="color:var(--color-accent);">Upgrade to Tier 2 to earn 15% on referrals</p>
            @endif
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top">
                <div class="kpi-tile-icon" data-face-cursor><i data-lucide="flame" class="w-4 h-4"></i></div>
            </div>
            <p class="kpi-tile-value">{{ $streakDays ?? 0 }}</p>
            <p class="kpi-tile-label">Day Streak</p>
            @if($nextStreakMilestone ?? null)
            <p class="text-xs text-muted mt-1">{{ $nextStreakMilestone - ($streakDays ?? 0) }} days to {{ $nextStreakMilestone }}-day badge</p>
            @endif
        </div>
    </div>

    <div class="glass-card p-5 mb-6 reveal-on-scroll" id="dashReviewsCard">
        <div class="mb-4">
            <h3 class="font-semibold text-sm text-muted">Your Reviews</h3>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-2xl font-bold">{{ $avgRating ? number_format($avgRating, 1) : '—' }}</span>
                <div class="flex items-center" style="gap:2px;">
                    @for ($i = 1; $i <= 5; $i++)
                    <i data-lucide="star" class="w-4 h-4" style="{{ $avgRating && $i <= round($avgRating) ? 'color:#f4a261;fill:#f4a261;' : 'color:var(--border-color);' }}"></i>
                    @endfor
                </div>
                <span class="text-muted text-xs">{{ $reviewCount ?? 0 }} review{{ ($reviewCount ?? 0) == 1 ? '' : 's' }}</span>
            </div>
        </div>

        @forelse($myReviews ?? [] as $review)
        <div class="ring-legend-row" style="align-items:flex-start;">
            <span style="flex:1;min-width:0;">
                <span class="font-medium">{{ $review->reviewer->name ?? 'A student' }}</span>
                <span class="text-muted text-xs"> · {{ class_basename($review->reviewable_type) }}{{ $review->reviewable?->title ? ' · '.$review->reviewable->title : '' }}</span>
                @if($review->comment)
                <p class="text-xs text-muted mt-1">{{ \Illuminate\Support\Str::limit($review->comment, 90) }}</p>
                @endif
            </span>
            <strong style="flex-shrink:0;">{{ $review->rating }}★</strong>
        </div>
        @empty
        <p class="text-sm text-muted py-3 text-center">No reviews yet — complete a hustle or sale to start building your rating.</p>
        @endforelse
    </div>

    <div class="analytics-card reveal-on-scroll">
        <div class="analytics-header">
            <div>
                <h3 class="font-semibold text-sm text-muted">Analytics</h3>
                <div class="flex items-center">
                    <span class="an-headline" id="analyticsHeadline">₦0</span>
                    <span class="an-delta up" id="analyticsDelta"></span>
                </div>
            </div>
            <div class="analytics-tabs" id="analyticsTabs">
                <div class="analytics-tab-indicator" id="analyticsIndicator"></div>
                <button class="analytics-tab active" data-tab="trend">Balance</button>
                <button class="analytics-tab" data-tab="flow">Money Flow</button>
                <button class="analytics-tab" data-tab="breakdown">Breakdown</button>
                <button class="analytics-tab" data-tab="posts">Posts</button>
                <button class="analytics-tab" data-tab="leaderboard">Top Earners</button>
            </div>
        </div>

        <div id="analyticsPane-trend">
            <div class="analytics-canvas-wrap"><canvas id="dashTrendChart"></canvas></div>
        </div>

        <div id="analyticsPane-flow" style="display:none;">
            <div class="analytics-stat-row">
                <div class="stat-item"><strong style="color:var(--color-accent3);">₦{{ number_format($totalIn ?? 0) }}</strong>Money In</div>
                <div class="stat-item"><strong style="color:#ef4444;">₦{{ number_format($totalOut ?? 0) }}</strong>Money Out</div>
                <div class="stat-item"><strong>₦{{ number_format(($totalIn ?? 0) - ($totalOut ?? 0)) }}</strong>Net Flow</div>
            </div>
            <div class="analytics-canvas-wrap"><canvas id="dashFlowChart"></canvas></div>
        </div>

        <div id="analyticsPane-breakdown" style="display:none;">
            <div class="analytics-canvas-wrap ring-mode">
                <div class="ring-chart-wrap"><canvas id="dashBreakdownRing"></canvas>
                    <div class="ring-chart-center"><strong id="dashBreakdownCenter">—</strong><span>top category</span></div>
                </div>
                <div class="ring-legend" id="dashBreakdownLegend"></div>
            </div>
        </div>

        <div id="analyticsPane-posts" style="display:none;">
            <div class="analytics-canvas-wrap ring-mode">
                <div class="ring-chart-wrap"><canvas id="dashPostsRing"></canvas>
                    <div class="ring-chart-center"><strong>{{ ($myOpenGigs ?? 0) + ($myListings ?? 0) }}</strong><span>total posts</span></div>
                </div>
                <div class="ring-legend">
                    <div class="ring-legend-row"><span><span class="ring-legend-dot" style="background:#6ea8ff;"></span>Open Hustles</span><strong>{{ $myOpenGigs ?? 0 }}</strong></div>
                    <div class="ring-legend-row"><span><span class="ring-legend-dot" style="background:#f4a261;"></span>Listings</span><strong>{{ $myListings ?? 0 }}</strong></div>
                    <div class="ring-legend-row"><span><span class="ring-legend-dot" style="background:#4ade80;"></span>Completed Hustles</span><strong>{{ $myCompletedGigs ?? 0 }}</strong></div>
                </div>
            </div>
        </div>

        @php
            $weeklyTopEarners = \App\Models\Transaction::where('transactions.created_at', '>=', $weekStart)
                ->where('amount', '>', 0)
                ->whereIn('type', ['gig_earning', 'marketplace_sale', 'referral_bonus'])
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->where('users.leaderboard_opt_in', true)
                ->selectRaw('users.name, SUM(transactions.amount) as total')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->take(5)
                ->get();
        @endphp
        <div id="analyticsPane-leaderboard" style="display:none;">
            <p class="text-xs text-muted mb-3">This week's top earners · resets Monday</p>
            @if($leaderboardRank ?? null)
            <p class="text-xs mb-3" style="color:var(--color-accent);">You're #{{ $leaderboardRank }} this week{{ $leaderboardRank > 5 ? ' — keep earning to crack the top 5' : '' }}</p>
            @elseif(!(auth()->user()->leaderboard_opt_in ?? false))
            <p class="text-xs text-muted mb-3">Not on the leaderboard yet — opt in from the <a href="{{ route('leaderboard.index') }}" class="text-accent">leaderboard page</a></p>
            @endif
            @forelse($weeklyTopEarners as $i => $entry)
            <div class="ring-legend-row">
                <span><span class="ring-legend-dot" style="background:{{ ['#fbbf24','#94a3b8','#ea580c','#6ea8ff','#a78bfa'][$i] ?? '#6ea8ff' }};"></span>{{ $i + 1 }}. {{ explode(' ', $entry->name)[0] }}</span>
                <strong>₦{{ number_format($entry->total) }}</strong>
            </div>
            @empty
            <p class="text-sm text-muted py-3 text-center">No earners yet this week.</p>
            @endforelse
            <a href="{{ route('leaderboard.index') }}" class="text-accent text-xs" style="display:block;margin-top:0.75rem;">View full leaderboard →</a>
        </div>
    </div>

    <script>
    (function () {
        if (typeof Chart === 'undefined') return;

        // Tab switching with sliding indicator
        const tabs = document.querySelectorAll('.analytics-tab');
        const indicator = document.getElementById('analyticsIndicator');
        function moveIndicator(el) {
            if (typeof gsap !== 'undefined') {
                gsap.to(indicator, { x: el.offsetLeft - 4, width: el.offsetWidth, duration: 0.35, ease: 'power3.out' });
            } else {
                indicator.style.transform = 'translateX(' + (el.offsetLeft - 4) + 'px)';
                indicator.style.width = el.offsetWidth + 'px';
            }
        }
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                moveIndicator(tab);
                document.querySelectorAll('[id^="analyticsPane-"]').forEach(p => p.style.display = 'none');
                document.getElementById('analyticsPane-' + tab.dataset.tab).style.display = '';
            });
        });
        setTimeout(() => moveIndicator(document.querySelector('.analytics-tab.active')), 50);

        const trendData = @json($trend ?? []);
        const trendCtx = document.getElementById('dashTrendChart');
        if (trendCtx) {
            const maxVal = Math.max(...trendData);
            const barColors = trendData.map(v => v === maxVal ? '#4f7bff' : '#dbe4ff');
            new Chart(trendCtx, {
                type: 'bar',
                data: { labels: trendData.map((_, i) => 'P' + (i + 1)), datasets: [{ data: trendData, backgroundColor: barColors, borderRadius: 10, borderSkipped: false, maxBarThickness: 36 }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { display: false } }, responsive: true, maintainAspectRatio: false }
            });
            const headline = document.getElementById('analyticsHeadline');
            const delta = document.getElementById('analyticsDelta');
            if (headline && trendData.length) {
                headline.textContent = '₦' + Number(trendData[trendData.length - 1]).toLocaleString();
                if (trendData.length > 1 && delta) {
                    const prev = trendData[trendData.length - 2];
                    const last = trendData[trendData.length - 1];
                    const pct = prev ? (((last - prev) / prev) * 100).toFixed(1) : 0;
                    delta.textContent = (pct >= 0 ? '↑ ' : '↓ ') + Math.abs(pct) + '%';
                    delta.className = 'an-delta ' + (pct >= 0 ? 'up' : 'down');
                }
            }
        }

        const flowCtx = document.getElementById('dashFlowChart');
        if (flowCtx) {
            const inVal = {{ $totalIn ?? 0 }};
            const outVal = {{ $totalOut ?? 0 }};
            const spentPct = inVal > 0 ? Math.round((outVal / inVal) * 100) : 0;
            new Chart(flowCtx, {
                type: 'bar',
                data: { labels: ['Money In', 'Money Out'], datasets: [{ data: [inVal, outVal], backgroundColor: ['#22c55e', '#ef4444'], borderRadius: 10, borderSkipped: false }] },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { display: false }, title: { display: true, text: spentPct + '% of income spent', font: { size: 12 }, padding: { bottom: 12 } } },
                    responsive: true, maintainAspectRatio: false
                }
            });
        }

        const categoryTotals = @json($categoryTotals ?? []);
        const catLabels = Object.keys(categoryTotals);
        const catValues = Object.values(categoryTotals);
        const colors = ['#6ea8ff', '#f4a261', '#4ade80', '#f472b6', '#a78bfa', '#fb923c'];

        const breakdownCtx = document.getElementById('dashBreakdownRing');
        if (breakdownCtx && catLabels.length) {
            const catTotal = catValues.reduce((a, b) => a + Number(b), 0);
            new Chart(breakdownCtx, { type: 'polarArea', data: { labels: catLabels, datasets: [{ data: catValues, backgroundColor: colors.map(c => c + 'cc'), borderWidth: 0 }] }, options: { plugins: { legend: { display: false } }, scales: { r: { display: false } }, responsive: true, maintainAspectRatio: false } });
            const legend = document.getElementById('dashBreakdownLegend');
            catLabels.forEach((label, i) => {
                const pct = catTotal > 0 ? Math.round((catValues[i] / catTotal) * 100) : 0;
                const row = document.createElement('div');
                row.className = 'ring-legend-row';
                row.innerHTML = '<span><span class="ring-legend-dot" style="background:' + colors[i % colors.length] + ';"></span>' + label + '</span><strong>' + pct + '%</strong>';
                legend.appendChild(row);
            });
            document.getElementById('dashBreakdownCenter').textContent = catLabels[0] || '—';
        }

        const postsCtx = document.getElementById('dashPostsRing');
        if (postsCtx) {
            new Chart(postsCtx, {
                type: 'radar',
                data: {
                    labels: ['Open Hustles', 'Listings', 'Completed'],
                    datasets: [{
                        data: [{{ $myOpenGigs ?? 0 }}, {{ $myListings ?? 0 }}, {{ $myCompletedGigs ?? 0 }}],
                        backgroundColor: 'rgba(79,123,255,0.2)',
                        borderColor: '#4f7bff',
                        pointBackgroundColor: '#4f7bff',
                        borderWidth: 2
                    }]
                },
                options: { plugins: { legend: { display: false } }, scales: { r: { ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.06)' } } }, responsive: true, maintainAspectRatio: false }
            });
        }
    })();
    </script>

    <div class="flex gap-3 mb-6 flex-wrap reveal-on-scroll">
        <a href="{{ route('gigs.index') }}" class="quick-action"><i data-lucide="briefcase" class="w-4 h-4"></i> Find a Hustle</a>
        <a href="{{ route('marketplace.create') }}" class="quick-action"><i data-lucide="plus" class="w-4 h-4"></i> Sell an Item</a>
        <a href="{{ route('referrals.index') }}" class="quick-action"><i data-lucide="users" class="w-4 h-4"></i> Invite Friends</a>
        <a href="{{ route('news.index') }}" class="quick-action"><i data-lucide="newspaper" class="w-4 h-4"></i> Campus News</a>
    </div>



    <!-- Campus News: popup toast notifications instead of a fixed section -->
    @if(count($latestNews ?? []) > 0)
    @php
        $campusNewsToastData = $latestNews->map(function ($n) {
            return [
                'title' => $n->title,
                'category' => $n->category,
                'time' => $n->published_at->diffForHumans(),
            ];
        });
    @endphp
    <script>
        window.__campusNewsItems = @json($campusNewsToastData);
    </script>
    <script src="{{ asset('js/news-toast.js') }}?v={{ time() }}"></script>
    @endif

    <div class="grid md:grid-cols-3 gap-6 mb-6">
        <div class="stat-card md:col-span-2 reveal-on-scroll">
            <div class="tab-row">
                <span class="tab-btn active">Available Hustles</span>
                <span class="tab-btn">Campus Market</span>
            </div>
            @forelse(($topGigs ?? []) as $gig)
            <a href="{{ route('gigs.show', $gig->id) }}" class="tx-table-row" style="text-decoration:none;color:inherit;">
                <div class="flex items-center gap-3">
                    <div class="icon-badge icon-badge-gigs" style="width:2rem;height:2rem;"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                    <span>{{ $gig->title }}</span>
                </div>
                <span class="tx-status {{ $gig->status === 'open' ? 'done' : 'pending' }}">{{ $gig->status === 'open' ? 'Open' : 'Taken' }}</span>
                <span class="text-right font-medium">₦{{ number_format($gig->amount) }}</span>
            </a>
            @empty
            <p class="text-muted text-sm py-4">No hustles available right now.</p>
            @endforelse
            @foreach(($topListings ?? []) as $item)
            <a href="{{ route('marketplace.show', $item->id) }}" class="tx-table-row" style="text-decoration:none;color:inherit;">
                <div class="flex items-center gap-3">
                    <div class="icon-badge icon-badge-market" style="width:2rem;height:2rem;"><i data-lucide="shopping-bag" class="w-4 h-4"></i></div>
                    <span>{{ $item->title }}</span>
                </div>
                <span class="tx-status pending">Listed</span>
                <span class="text-right font-medium">₦{{ number_format($item->amount) }}</span>
            </a>
            @endforeach
        </div>

        <div class="gauge-panel reveal-on-scroll">
            <p class="text-sm text-muted mb-3">Total Asset</p>
            <div class="gauge-wrap">
                <canvas id="gaugeChart"></canvas>
                <div class="gauge-center">
                    <p class="amt">₦{{ number_format($balance ?? 0) }}</p>
                    <p class="lbl">Wallet</p>
                </div>
            </div>
            <div class="flex justify-center gap-3 mt-4">
                <button onclick="document.getElementById('dashWithdrawModal').classList.add('open')" class="balance-chip" style="color:var(--color-accent);background:var(--color-card);border:1px solid var(--border-color);">Withdraw</button>
                <button onclick="document.getElementById('dashTopupModal').classList.add('open')" class="balance-chip primary">Top Up</button>
            </div>
        </div>
    </div>

    <!-- Referrals breakdown -->
    <div class="stat-card mb-6 reveal-on-scroll">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold">Your Referrals</h2>
            <a href="{{ route('referrals.index') }}" class="text-accent text-sm">View all →</a>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-muted mb-2">Active ({{ ($referralActive ?? collect())->count() }}) — paid tier, earning</p>
                @forelse(($referralActive ?? []) as $r)
                <div class="flex items-center justify-between text-sm py-2 border-b" style="border-color:var(--border-color);">
                    <span>{{ $r->name }}</span>
                    <span class="text-accent text-xs font-medium">{{ strtoupper($r->tier) }}</span>
                </div>
                @empty
                <p class="text-muted text-xs">No active referrals yet.</p>
                @endforelse
            </div>
            <div>
                <p class="text-xs text-muted mb-2">Referred, not yet active ({{ ($referralInactive ?? collect())->count() }})</p>
                @forelse(($referralInactive ?? []) as $r)
                <div class="flex items-center justify-between text-sm py-2 border-b" style="border-color:var(--border-color);">
                    <span>{{ $r->name }}</span>
                    <span class="text-muted text-xs">Free account</span>
                </div>
                @empty
                <p class="text-muted text-xs">None right now.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent activity -->
    <div class="stat-card mb-6 reveal-on-scroll">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold">Recent Activity</h2>
            <a href="{{ route('wallet.index') }}" class="text-accent text-sm">View all →</a>
        </div>
        @forelse(($recentTx ?? []) as $tx)
        <div class="tx-table-row">
            <div>
                <p class="text-sm font-medium">{{ $tx->description }}</p>
                <p class="text-muted text-xs">{{ $tx->created_at->diffForHumans() }}</p>
            </div>
            <span></span>
            <span class="text-right font-medium {{ $tx->amount >= 0 ? '' : 'text-muted' }}" style="color: {{ $tx->amount >= 0 ? '#16a34a' : '#dc2626' }};">
                {{ $tx->amount >= 0 ? '+' : '' }}₦{{ number_format($tx->amount, 2) }}
            </span>
        </div>
        @empty
        <p class="text-muted text-sm">No activity yet.</p>
        @endforelse
    </div>
<div class="glass-card p-4 mb-6 flex items-center gap-3 scroll-hide-over" id="dashAdSlot" style="border:1px dashed var(--border-color);">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:var(--color-card);">
            <i data-lucide="megaphone" class="w-5 h-5 text-muted"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">Sponsored</p>
            <p class="text-muted text-xs">Ad space — nothing running here yet</p>
        </div>
    </div>
    </main>

@endsection

@section('scripts')
<script>
function sparkline(id, color) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [1,2,3,4,5,6,7],
            datasets: [{
                data: [3,5,4,7,6,8,9],
                borderColor: color,
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}
sparkline('sparkGig', '#b8860b');

function miniSpark(id, color) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: { labels: [1,2,3,4,5,6,7], datasets: [{ data: [3,5,4,7,6,8,9], borderColor: color, backgroundColor: 'transparent', borderWidth: 2, pointRadius: 0, tension: 0.4 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
    });
}
miniSpark('balanceSparkIncome', 'rgba(255,255,255,0.9)');
miniSpark('balanceSparkReferral', 'rgba(255,255,255,0.9)');
sparkline('sparkMarket', '#2563eb');
sparkline('sparkRef', '#9333ea');

const gaugeCtx = document.getElementById('gaugeChart');
if (gaugeCtx) {
    new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [70, 30],
                backgroundColor: ['#b8860b', 'rgba(255,255,255,0.06)'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '78%',
            plugins: { legend: { display: false } }
        }
    });
}
</script>
<script src="{{ asset('js/glass-balance-card.js') }}"></script>
<script src="{{ asset('js/ledger-folder.js') }}"></script>
<script src="{{ asset('js/balance-flip-card.js') }}?v={{ time() }}"></script>
<div id="dashTopupModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Top Up Wallet</h3>
        <form method="POST" action="{{ route('flutterwave.initiate') }}">
            @csrf
            <input type="number" name="amount" min="100" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Continue to Payment</button>
        </form>
        <button onclick="document.getElementById('dashTopupModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>

<div id="dashWithdrawModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Withdraw Funds</h3>
        <form method="POST" action="{{ route('wallet.withdraw') }}">
            @csrf
            <input type="number" name="amount" min="100" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Confirm Withdraw</button>
        </form>
        <button onclick="document.getElementById('dashWithdrawModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>

@endsection
