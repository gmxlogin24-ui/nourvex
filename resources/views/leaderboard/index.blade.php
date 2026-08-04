@extends('layouts.app')
@section('content')
<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1">This Week's Top Earners</h1>
    <p class="text-muted text-sm mb-6">Resets every Monday. Open to everyone — no plan required to join.</p>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="glass-card p-4 mb-6 flex items-center justify-between reveal-on-scroll">
        <div>
            <p class="text-sm font-medium">{{ $optedIn ? "You're visible on the leaderboard" : "Join the leaderboard" }}</p>
            <p class="text-muted text-xs mt-1">Only your first name and weekly total show, nothing else.</p>
        </div>
        <form method="POST" action="{{ route('leaderboard.toggle') }}">
            @csrf
            <button type="submit" class="btn-primary text-sm">{{ $optedIn ? 'Leave' : 'Join' }}</button>
        </form>
    </div>

    @if($topEarners->count() >= 1)
    <div class="podium-row reveal-on-scroll">
        @if($topEarners->count() >= 2)
        <div class="podium-place second">
            <div class="podium-avatar">{{ substr($topEarners[1]->name, 0, 1) }}</div>
            <div class="podium-name">{{ explode(' ', $topEarners[1]->name)[0] }}</div>
            <div class="podium-amount">₦{{ number_format($topEarners[1]->total) }}</div>
            <div class="podium-bar">2</div>
        </div>
        @endif

        <div class="podium-place first">
            <div class="podium-avatar">{{ substr($topEarners[0]->name, 0, 1) }}</div>
            <div class="podium-name">{{ explode(' ', $topEarners[0]->name)[0] }}</div>
            <div class="podium-amount">₦{{ number_format($topEarners[0]->total) }}</div>
            <div class="podium-bar">1</div>
        </div>

        @if($topEarners->count() >= 3)
        <div class="podium-place third">
            <div class="podium-avatar">{{ substr($topEarners[2]->name, 0, 1) }}</div>
            <div class="podium-name">{{ explode(' ', $topEarners[2]->name)[0] }}</div>
            <div class="podium-amount">₦{{ number_format($topEarners[2]->total) }}</div>
            <div class="podium-bar">3</div>
        </div>
        @endif
    </div>
    @endif

    <div class="stat-card reveal-on-scroll">
        @forelse($topEarners->slice(3) as $i => $entry)
        <div class="tx-table-row">
            <div class="flex items-center gap-3">
                <div class="icon-badge" style="width:2rem;height:2rem;"><span class="text-sm font-semibold">{{ $i + 4 }}</span></div>
                <span>{{ explode(' ', $entry->name)[0] }}</span>
            </div>
            <span></span>
            <span class="text-right font-medium text-accent">₦{{ number_format($entry->total) }}</span>
        </div>
        @empty
        @if($topEarners->count() === 0)
        <p class="text-muted text-sm py-4 text-center">No earners yet this week — be the first!</p>
        @endif
        @endforelse
    </div>
</main>
@endsection
