@extends('layouts.app-authenticated')
@section('content')

<main class="profile-shell p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;max-width:1240px;' : '' }}">
    <h1 class="text-2xl font-semibold mb-1 hoverfade-text">Profile</h1>
    <p class="text-muted text-sm mb-6">Manage your account details</p>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="profile-home-grid">

        <div class="profile-hero-card reveal-on-scroll"
            @if($user->avatar_url) style="--hero-photo: url('{{ $user->avatar_url }}')" @endif>
            <div class="profile-hero-top">
                <div class="profile-hero-avatar">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}">
                    @else
                    <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <p class="profile-hero-name">{{ $user->name }} <span class="merchant-badge">Merchant</span></p>
                <a href="{{ route('levels-badges.show') }}" class="block mt-2 mb-1 p-3 rounded-lg bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-badge-strip' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold flex items-center gap-1.5">
                            <i class="ph ph-medal w-4 h-4 text-blue-500"></i>
                            {{ $currentLevel?->name ?? 'Unranked' }}
                        </span>
                        <span class="text-xs text-muted flex items-center gap-1">
                            {{ $totalBadgesEarned }} badges <i class="ph ph-caret-right w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    @if($earnedBadges->count())
                    <div class="flex gap-2">
                        @foreach($earnedBadges as $badge)
                        <span class="w-7 h-7 rounded-full bg-blue-500/10 flex items-center justify-center" title="{{ $badge->name }}">
                            <i class="ph ph-{{ ph_icon($badge->icon ?? 'award') }} w-3.5 h-3.5 text-blue-600"></i>
                        </span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-muted">Complete activities to earn your first badge</p>
                    @endif
                </a>
                <p class="profile-hero-tier">
                    {{ $user->tier === 'tier1' ? 'Plus' : ($user->tier === 'tier2' ? 'Pro' : strtoupper($user->tier)) }}
                    @if($user->is_verified_seller) · Verified @endif
                </p>
                <p class="profile-hero-ref">Referral code: {{ $user->referral_code }}</p>
            </div>

            <div class="profile-hero-stats">
                <div>
                    <p class="profile-hero-stat-val">{{ $completedGigsCount }}</p>
                    <p class="profile-hero-stat-lbl"><i class="ph ph-briefcase w-3.5 h-3.5"></i> Jobs</p>
                </div>
                <div>
                    <p class="profile-hero-stat-val">
                        @if($averageRating)<i class="ph ph-fill ph-star w-3.5 h-3.5" style="color:#fbbf24;"></i> {{ $averageRating }}@else —@endif
                    </p>
                    <p class="profile-hero-stat-lbl">Rating</p>
                </div>
                <div>
                    <p class="profile-hero-stat-val">{{ $reviewsReceivedCount }}</p>
                    <p class="profile-hero-stat-lbl"><i class="ph ph-medal w-3.5 h-3.5"></i> Reviews</p>
                </div>
            </div>

            <div class="profile-hero-curve" aria-hidden="true">
                <svg viewBox="0 0 400 60" preserveAspectRatio="none"><path d="M0,60 C100,0 300,0 400,60 L400,60 L0,60 Z"></path></svg>
            </div>
        </div>

<div class="glass-card profile-tile-grid reveal-on-scroll {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}" style="overflow:hidden;">
            <a href="{{ route('profile.reputation') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-star w-4 h-4"></i></span>
                <span class="profile-list-row-text">
                    <span class="profile-list-row-title">Reputation</span>
                    <span class="profile-list-row-sub">Jobs completed, rating, reviews</span>
                </span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('profile.account') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-user w-4 h-4"></i></span>
                <span class="profile-list-row-text">
                    <span class="profile-list-row-title">Account Details</span>
                    <span class="profile-list-row-sub">Name, email, password</span>
                </span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('profile.appearance') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-palette w-4 h-4"></i></span>
                <span class="profile-list-row-text">
                    <span class="profile-list-row-title">Appearance</span>
                    <span class="profile-list-row-sub">Theme, accent color, dock, sidebar, dark mode</span>
                </span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('wallet.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-wallet w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Wallet</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('referrals.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-users w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Referrals</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('subscriptions.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph-fill ph-crown"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Upgrade</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('money-tracker.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-chart-line w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Money Tracker</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('posts.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-file-text w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">My Posts</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('leaderboard.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-trophy w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Leaderboard</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('services.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-lightbulb w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Services</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            <a href="{{ route('pulse.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-lightning w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Pulse</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            @if($user->is_admin ?? false)
            <a href="{{ route('admin.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-shield w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Admin</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            @endif
        </div>

    </div>
</main>
@endsection
