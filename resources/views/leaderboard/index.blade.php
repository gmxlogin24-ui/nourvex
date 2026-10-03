@extends('layouts.app-authenticated')
@section('content')
@php
    $plain = (auth()->user()->theme_style ?? 'glass') === 'plain';
    $fmt = fn ($v) => $type === 'earnings' ? '₦' . number_format($v) : number_format($v) . ' pts';
    $link = fn (array $over = []) => route('leaderboard.index', array_filter(
        array_merge(['type' => $type, 'period' => $period, 'area' => $area], $over),
        fn ($x) => $x !== null && $x !== ''
    ));
    $chip = 'display:inline-block;padding:.3rem .8rem;border-radius:999px;font-size:.8rem;border:1px solid rgba(128,128,128,.35);text-decoration:none;';
    $chipOn = 'background:color-mix(in srgb,var(--color-accent) 15%,transparent);border-color:var(--color-accent);';
    $periodLabels = ['week' => 'This week', 'month' => 'This month', 'all' => 'All time'];
    $labels = [
        'daily_login' => 'Log in each day',
        'streak_7' => '7-day login streak',
        'streak_30' => '30-day login streak',
        'qcoin_claim' => 'Claim your daily Q Coins',
        'referral' => 'Refer a friend who signs up',
        'gig_completed' => 'Complete a gig',
'service_purchase' => 'Buy airtime, data or cable (more for bigger spend)',
    ];
    $me = auth()->user();
    $mult = config('ranking.plan_multiplier.' . $me->planKey(), 1);
@endphp
<main class="max-w-2xl mx-auto p-6 {{ $plain ? 'dqm-page' : '' }}" style="{{ $plain ? 'padding-top:0 !important;' : '' }}">
    <h1 class="text-2xl font-semibold mb-1">{{ $type === 'earnings' ? 'Top Earners' : 'Leaderboard' }} · {{ $periodLabels[$period] }}</h1>
    <p class="text-muted text-sm mb-4">
        {{ $area ? $areas[$area] : 'All areas' }}
        @if ($period === 'week') · resets every Monday @elseif ($period === 'month') · resets on the 1st @endif
    </p>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.6rem;">
        <a href="{{ $link(['type' => 'points']) }}" style="{{ $chip }}{{ $type === 'points' ? $chipOn : '' }}">Points</a>
        <a href="{{ $link(['type' => 'earnings']) }}" style="{{ $chip }}{{ $type === 'earnings' ? $chipOn : '' }}">Earnings</a>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.6rem;">
        @foreach ($periodLabels as $k => $label)
            <a href="{{ $link(['period' => $k]) }}" style="{{ $chip }}{{ $period === $k ? $chipOn : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1.2rem;">
        <a href="{{ $link(['area' => null]) }}" style="{{ $chip }}{{ !$area ? $chipOn : '' }}">All areas</a>
        @foreach ($areas as $k => $label)
            <a href="{{ $link(['area' => $k]) }}" style="{{ $chip }}{{ $area === $k ? $chipOn : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="glass-card p-4 mb-4 flex items-center justify-between reveal-on-scroll {{ $plain ? 'dqm-card' : '' }}">
        <div>
            <p class="text-sm font-medium">{{ $optedIn ? "You're visible on the leaderboard" : "Join the leaderboard" }}</p>
            <p class="text-muted text-xs mt-1">
                Your points: {{ number_format($myPoints) }}
                @if ($myRank) · Rank #{{ $myRank }} @elseif (!$optedIn) · join to appear on the board @endif
            </p>
            <p class="text-muted text-xs mt-1">Only your first name, area and total show.</p>
        </div>
        <form method="POST" action="{{ route('leaderboard.toggle') }}">
            @csrf
            <button type="submit" class="btn-primary text-sm">{{ $optedIn ? 'Leave' : 'Join' }}</button>
        </form>
    </div>

    @if ($rows->count() >= 1)
    <div class="podium-row reveal-on-scroll">
        @if ($rows->count() >= 2)
        <div class="podium-place second">
            <div class="podium-avatar">{{ substr($rows[1]->name, 0, 1) }}</div>
            <div class="podium-name">{{ explode(' ', $rows[1]->name)[0] }}</div>
            <div class="podium-amount">{{ $fmt($rows[1]->total) }}</div>
            <div class="podium-bar">2</div>
        </div>
        @endif

        <div class="podium-place first">
            <div class="podium-avatar">{{ substr($rows[0]->name, 0, 1) }}</div>
            <div class="podium-name">{{ explode(' ', $rows[0]->name)[0] }}</div>
            <div class="podium-amount">{{ $fmt($rows[0]->total) }}</div>
            <div class="podium-bar">1</div>
        </div>

        @if ($rows->count() >= 3)
        <div class="podium-place third">
            <div class="podium-avatar">{{ substr($rows[2]->name, 0, 1) }}</div>
            <div class="podium-name">{{ explode(' ', $rows[2]->name)[0] }}</div>
            <div class="podium-amount">{{ $fmt($rows[2]->total) }}</div>
            <div class="podium-bar">3</div>
        </div>
        @endif
    </div>
    @endif

    <div class="stat-card reveal-on-scroll {{ $plain ? 'dqm-card' : '' }}">
        @forelse ($rows->slice(3) as $i => $entry)
        <div class="tx-table-row">
            <div class="flex items-center gap-3">
                <div class="icon-badge" style="width:2rem;height:2rem;"><span class="text-sm font-semibold">{{ $i + 1 }}</span></div>
                <span>{{ explode(' ', $entry->name)[0] }}
                    @if ($entry->area && isset($areas[$entry->area]))<span class="text-muted text-xs"> · {{ $areas[$entry->area] }}</span>@endif
                </span>
            </div>
            <span></span>
            <span class="text-right font-medium text-accent">{{ $fmt($entry->total) }}</span>
        </div>
        @empty
        @if ($rows->count() === 0)
        <p class="text-muted text-sm py-4 text-center">Nobody here yet — be the first!</p>
        @endif
        @endforelse
    </div>

    @if ($type === 'points')
    <div class="glass-card p-4 mt-6 {{ $plain ? 'dqm-card' : '' }}">
        <p class="text-sm font-medium mb-2">How to earn points</p>
        @foreach ($labels as $key => $label)
            @if (config("ranking.points.$key"))
            <div style="display:flex;justify-content:space-between;font-size:.85rem;padding:.2rem 0;">
                <span>{{ $label }}</span>
                <span class="text-accent">+{{ (int) round(config("ranking.points.$key") * $mult) }}</span>
            </div>
            @endif
        @endforeach
        <p class="text-muted text-xs mt-2">
            Your {{ $me->planLabel() }} plan gives ×{{ $mult }} points.
            @if ($me->planKey() !== 'tier3') <a href="{{ route('subscriptions.index') }}" class="text-accent">Upgrade for a higher multiplier</a>@endif
        </p>
    </div>
    @endif
</main>
@endsection
