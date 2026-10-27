@php
    $dqmAvatar = auth()->user()?->avatar_url;
    $dqmName = auth()->user()?->name ?? 'Guest';
    $dqmInitial = strtoupper(substr($dqmName, 0, 1));

    $dqmMiniStats = [
        ['label' => 'Earnings', 'value' => number_format(($earnings ?? 0) * config('qcoin.q_per_naira_display', 10)) . ' Q', 'icon' => 'trending-up', 'tint' => 'green', 'seed' => (int)(($earnings ?? 0)/1000)+5],
        ['label' => 'Active Gigs', 'value' => (string) ($myOpenGigs ?? 0), 'icon' => 'briefcase', 'tint' => 'purple', 'seed' => ($myOpenGigs ?? 0)+2],
        ['label' => 'Q Coin', 'value' => number_format($qCoinBalance ?? 0) . ' Q', 'icon' => 'gem', 'tint' => 'gold', 'seed' => (int)(($qCoinBalance ?? 0)/10)+7],
        ['label' => 'Rating', 'value' => $avgRating ? number_format($avgRating, 1) . ' ★' : '—', 'icon' => 'star', 'tint' => 'blue', 'seed' => (int)(($avgRating ?? 1)*10)+1],
    ];

    $dqmWaveSeed = max(1, (int) (($netFlow ?? 0) + ($totalIn ?? 0) + ($totalOut ?? 0)));
    $dqmWavePts = [];
    for ($i = 0; $i <= 6; $i++) {
        $n = sin($i * 1.3 + ($dqmWaveSeed % 7)) * 20 + cos($i * 0.7) * 12;
        $dqmWavePts[] = round(50 - $n, 1);
    }
    $dqmWaveLine = '';
    foreach ($dqmWavePts as $i => $y) {
        $x = $i * 50;
        $dqmWaveLine .= ($i === 0 ? "M{$x},{$y} " : "L{$x},{$y} ");
    }
    $dqmWaveFillPath = "M0,100 L0,{$dqmWavePts[0]} ";
    foreach ($dqmWavePts as $i => $y) {
        if ($i === 0) continue;
        $x = $i * 50;
        $dqmWaveFillPath .= "L{$x},{$y} ";
    }
    $dqmWaveFillPath .= "L300,100 Z";

    $dqmQuickSend = ($recentConversations ?? collect())->take(6);
@endphp

<div class="dqp-page">

    <div class="dqp-hero-card dqp-anim" style="--i:1">
        <div class="dqp-hero-noise"></div>
        <div class="dqp-shine"></div>
        <div class="dqp-hero-top">
            <span class="dqp-hero-label">Total Balance</span>
            <i data-lucide="wallet" class="w-4 h-4"></i>
        </div>
        <div class="dqp-hero-chip"></div>
        <div class="dqp-hero-amount"><span class="dqm-countup" data-value="{{ ($balance ?? 0) * config('qcoin.q_per_naira_display', 10) }}">0</span> Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($balance ?? 0) }}</small></div>
        @if($user->virtual_account_number ?? false)
            <div class="dqp-hero-masked">•••• •••• {{ substr($user->virtual_account_number, -4) }}</div>
            <div class="dqp-hero-holder-row">
                <span class="dqp-hero-holder">Card holder<b>{{ $dqmName }}</b></span>
            </div>
        @else
            <div class="dqp-hero-holder-row">
                <a href="{{ route('wallet.virtual-account') }}" class="dqp-hero-holder" style="text-decoration:none;">Activate NAURVEX Pay to fund your wallet</a>
            </div>
        @endif
        <div class="dqp-hero-actions">
            <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dqp-btn dqp-btn-primary"><i data-lucide="arrow-up-right" class="w-4 h-4"></i> Send</a>
            <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dqp-btn dqp-btn-ghost">Withdraw</a>
            <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dqp-btn dqp-btn-icon"><i data-lucide="more-horizontal" class="w-4 h-4"></i></a>
        </div>
    </div>

    <div class="dqp-mini-row dqp-anim" style="--i:2">
        @foreach($dqmMiniStats as $stat)
            <div class="dqp-mini-card dqp-tint-{{ $stat['tint'] }}">
                <i data-lucide="{{ $stat['icon'] }}" class="w-4 h-4"></i>
                <span class="v">{{ $stat['value'] }}</span>
                <span class="l">{{ $stat['label'] }}</span>
                @php
                    $sp = [];
                    for ($j=0;$j<=8;$j++){ $n=sin($j*1.1+$stat['seed'])*8+cos($j*0.6+$stat['seed'])*5; $sp[]=round(20-$n,1); }
                    $spPath=''; foreach($sp as $j=>$y){ $x=$j*10; $spPath.=($j===0?"M{$x},{$y} ":"L{$x},{$y} "); }
                @endphp
                <svg class="dqp-spark" viewBox="0 0 80 28" preserveAspectRatio="none"><path d="{{ $spPath }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        @endforeach
    </div>

    <div class="dqp-services-row dqp-anim" style="--i:3">
        <a href="{{ route('services.index') }}" class="dqp-service"><div class="icon dqp-svc-blue"><i data-lucide="smartphone" class="w-5 h-5"></i></div><span>Airtime</span></a>
        <a href="{{ route('services.index') }}" class="dqp-service"><div class="icon dqp-svc-green"><i data-lucide="wifi" class="w-5 h-5"></i></div><span>Data</span></a>
        <a href="{{ route('services.index') }}" class="dqp-service"><div class="icon dqp-svc-purple"><i data-lucide="tv" class="w-5 h-5"></i></div><span>Cable TV</span></a>
        <a href="{{ route('services.index') }}" class="dqp-service"><div class="icon dqp-svc-orange"><i data-lucide="plug-zap" class="w-5 h-5"></i></div><span>Electricity</span></a>
    </div>

    <div class="dqp-card dqp-chart-card dqp-anim" style="--i:4">
        <div class="dqp-chart-top">
            <span class="dqp-flow-pill dqp-flow-pill-in"><i data-lucide="arrow-down" class="w-3 h-3"></i> In {{ number_format(($totalIn ?? 0) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalIn ?? 0) }}</small></span>
            <span class="dqp-flow-pill dqp-flow-pill-out"><i data-lucide="arrow-up" class="w-3 h-3"></i> Out {{ number_format(($totalOut ?? 0) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalOut ?? 0) }}</small></span>
        </div>
        <div class="dqp-card-head"><span>Money Flow</span><span class="dqp-flow-net">{{ number_format(($netFlow ?? 0) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($netFlow ?? 0) }}</small> net</span></div>
        <div class="dqp-wave-wrap">
            <svg class="dqp-wave-svg" viewBox="0 0 300 100" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="dqpWaveFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--user-accent, #7c5cf0)" stop-opacity="0.45"/>
                        <stop offset="100%" stop-color="var(--user-accent, #7c5cf0)" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="dqpWaveStroke" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="var(--user-accent, #7c5cf0)"/>
                        <stop offset="100%" stop-color="#22c55e"/>
                    </linearGradient>
                </defs>
                <path d="{{ $dqmWaveFillPath }}" fill="url(#dqpWaveFill)"></path>
                <path class="dqp-wave-line" d="{{ $dqmWaveLine }}" fill="none" stroke="url(#dqpWaveStroke)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <circle cx="150" cy="{{ $dqmWavePts[3] }}" r="4" fill="#fff"></circle>
            </svg>
        </div>
    </div>

    @if($dqmQuickSend->count() > 0)
    <div class="dqp-card dqp-anim" style="--i:5">
        <div class="dqp-card-head"><span>Quick Send</span><a href="{{ route('chat.index') }}">View all</a></div>
        <div class="dqp-quick-row">
            <div class="dqp-quick-item"><a href="{{ route('chat.index') }}" class="dqp-quick-avatar dqp-quick-add"><i data-lucide="plus" class="w-4 h-4"></i></a><span>Add</span></div>
            @foreach($dqmQuickSend as $conv)
                <div class="dqp-quick-item">
                    <a href="{{ route('chat.index') }}" class="dqp-quick-avatar">
                        @if($conv['other_avatar'])<img src="{{ $conv['other_avatar'] }}" alt="">@else<span>{{ strtoupper(substr($conv['other_name'],0,1)) }}</span>@endif
                    </a>
                    <span>{{ \Illuminate\Support\Str::limit($conv['other_name'], 8, '') }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="dqp-card dqp-anim" style="--i:6">
        <div class="dqp-card-head"><span>Recent Gigs</span><a href="{{ route('gigs.index') }}">View all</a></div>
        @forelse($topGigs as $gig)
            <a href="{{ route('gigs.show', $gig->id) }}" class="dqp-tx-row">
                <div class="dqp-tx-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                <div class="dqp-tx-body">
                    <span class="t">{{ $gig->title }}</span>
                    <span class="s">{{ ucfirst($gig->status ?? '') }}</span>
                </div>
                <span class="dqp-tx-amount">{{ number_format(($gig->amount) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($gig->amount) }}</small></span>
            </a>
        @empty
            <p class="dqp-empty">No gigs yet.</p>
        @endforelse
    </div>

    <div class="dqp-card dqp-anim" style="--i:7">
        <div class="dqp-card-head"><span>Marketplace</span><a href="{{ route('marketplace.index') }}">Open</a></div>
        <div class="dqp-mp-row">
            <div class="dqp-mp-item"><span class="v">{{ $myListings }}</span><span class="l">Active Listings</span></div>
            <div class="dqp-mp-item dqp-mp-soon"><span class="v">Soon</span><span class="l">Sales</span></div>
            <div class="dqp-mp-item dqp-mp-soon"><span class="v">Soon</span><span class="l">Orders</span></div>
        </div>
    </div>

    <div class="dqp-profile-card dqp-anim" style="--i:8">
        <div class="dqp-profile-photo">
            @if($dqmAvatar)<img src="{{ $dqmAvatar }}" alt="">@else<span>{{ $dqmInitial }}</span>@endif
        </div>
        <div class="dqp-profile-scrim"></div>
        <div class="dqp-profile-info">
            <p class="name">{{ $dqmName }}</p>
            <p class="level">{{ auth()->user()->workerLevel()['label'] }}</p>
            <div class="stats"><span>{{ $myCompletedGigs }} Gigs</span><span>{{ $avgRating ? number_format($avgRating, 1) : '—' }} ({{ $reviewCount }})</span></div>
            <a href="{{ route('profile.index') }}" class="edit">Edit Profile</a>
            <a href="{{ route('kyc.show') }}" class="edit">Verify account</a>
        </div>
    </div>

    <div class="dqp-card dqp-anim" style="--i:9">
        <div class="dqp-card-head"><span>Activity</span></div>
        @forelse($todayFeed->take(5) as $item)
            <a href="{{ $item['url'] }}" class="dqp-activity-row">
                <span class="dqp-dot dqp-dot-{{ $item['type'] }}"></span>
                <span class="t">{{ $item['title'] }}</span>
                <span class="time">{{ \Carbon\Carbon::parse($item['time'])->diffForHumans() }}</span>
            </a>
        @empty
            <p class="dqp-empty">Nothing new yet.</p>
        @endforelse
    </div>

    <div class="dqp-card dqp-anim" style="--i:10">
        <div class="dqp-card-head"><span>Messages</span><a href="{{ route('chat.index') }}">Open chat</a></div>
        @forelse($dqmQuickSend as $conv)
            <a href="{{ route('chat.index') }}" class="dqp-chat-row">
                <div class="dqp-chat-avatar">
                    @if($conv['other_avatar'])<img src="{{ $conv['other_avatar'] }}" alt="">@else<span>{{ strtoupper(substr($conv['other_name'],0,1)) }}</span>@endif
                </div>
                <div class="dqp-chat-body">
                    <span class="name">{{ $conv['other_name'] }}</span>
                    <span class="snippet">{{ \Illuminate\Support\Str::limit($conv['last_body'], 42) }}</span>
                </div>
                <div class="dqp-chat-meta">
                    <span class="time">{{ $conv['last_at'] ? \Carbon\Carbon::parse($conv['last_at'])->diffForHumans(null, true) : '' }}</span>
                    @if($conv['unread'] > 0)<span class="unread">{{ $conv['unread'] }}</span>@endif
                </div>
            </a>
        @empty
            <p class="dqp-empty">No conversations yet.</p>
        @endforelse
    </div>

</div>

<script>
(function () {
    var els = document.querySelectorAll('.dqm-countup');
    els.forEach(function (el) {
        var target = parseFloat(el.dataset.value) || 0;
        var duration = 900, start = null;
        function ease(t) { return t * (2 - t); }
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            el.textContent = Math.round(target * ease(p)).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    document.querySelectorAll('.dqp-btn, .dqp-mini-card, .dqp-tx-row, .dqp-service').forEach(function (el) {
        el.addEventListener('pointerdown', function () { el.style.transform = 'scale(0.96)'; });
        el.addEventListener('pointerup', function () { el.style.transform = ''; });
        el.addEventListener('pointerleave', function () { el.style.transform = ''; });
    });
})();
</script>
