@extends('layouts.app-authenticated')
@section('content')

<main class="profile-shell p-6">
    <a href="{{ route('profile.index') }}" class="profile-back-link"><i data-lucide="arrow-left" class="w-4 h-4"></i> Profile</a>

    <div class="profile-detail-grid">
        <div class="profile-detail-main">
            <h1 class="text-2xl font-semibold mb-1 hoverfade-text">Reputation</h1>
            <p class="text-muted text-sm mb-6">How you look to other users</p>

            <div class="glass-card p-6 mb-6 reveal-on-scroll">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold">Rank</h2>
                    <span class="trust-pill" style="gap:0.4rem;">
                        <i data-lucide="{{ $workerLevel['icon'] }}" class="w-4 h-4" style="color:var(--color-accent);"></i>
                        <span class="font-semibold text-sm">{{ $workerLevel['label'] }}</span>
                        @if($user->badge_tier === 'bronze')
                        <span class="member-badge member-badge-bronze"><i data-lucide="medal"></i> Bronze Member</span>
                        @endif
                        @if($user->tier === 'tier3')
                        <span class="member-badge member-badge-gold"><i data-lucide="crown"></i> Premium</span>
                        @endif
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div>
                        <p class="text-xl font-bold">{{ $completedGigsCount }}</p>
                        <p class="text-muted text-xs">Jobs Completed</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold flex items-center justify-center gap-1">
                            @if($averageRating)<i data-lucide="star" class="w-4 h-4" style="color:#fbbf24;fill:#fbbf24;"></i>{{ $averageRating }}@else —@endif
                        </p>
                        <p class="text-muted text-xs">Avg Rating</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold">{{ $reviewsReceivedCount }}</p>
                        <p class="text-muted text-xs">Reviews</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-detail-aside profile-shape-rank">
            <div class="profile-shape-inner">
                <i data-lucide="{{ $workerLevel['icon'] }}" class="profile-shape-icon"></i>
                <p class="profile-shape-title">{{ $workerLevel['label'] }}</p>
                <p class="profile-shape-sub">{{ $completedGigsCount }} jobs · {{ $averageRating ?? '—' }}★</p>
            </div>
        </div>
    </div>
</main>
@endsection
