@extends('layouts.app-authenticated')
@section('content')

<main class="profile-shell p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;max-width:1240px;' : '' }}">
    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="profile-home-grid">

        <div class="profile-hero-card reveal-on-scroll"
            @if($user->avatar_url) style="--hero-photo: url('{{ $user->avatar_url }}')" @endif>
            @if((auth()->user()->theme_style ?? 'glass') === 'plain')
            <div class="pfl-hero-time">{{ now()->format('g:i A') }}</div>
            @endif
            <div class="profile-hero-top">
                <div class="profile-hero-avatar">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}">
                    @else
                    <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <p class="profile-hero-name">{{ $user->name }} <span class="merchant-badge">Merchant</span></p>
                @if((auth()->user()->theme_style ?? 'glass') === 'plain')
                <div class="pfl-hero-status"><span class="pfl-status-dot"></span> {{ $user->is_verified_seller ? 'Verified & Active' : 'Active on Campulsy' }}</div>
                @endif
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

            @if((auth()->user()->theme_style ?? 'glass') === 'plain')
            <div class="pfl-hero-pills">
                <a href="{{ route('wallet.index') }}" class="pfl-hero-pill"><i class="ph ph-plus-circle w-4 h-4"></i> Send Money</a>
                <a href="{{ route('referrals.index') }}" class="pfl-hero-pill"><i class="ph ph-copy w-4 h-4"></i> Copy Code</a>
            </div>
            @php
                $pflAccent = $user->accent_color ?? '#0F9D6E';
            @endphp
            <div class="pfl-hero-strip" style="background: linear-gradient(135deg, {{ $pflAccent }}, color-mix(in srgb, {{ $pflAccent }} 70%, black));">
                <i class="ph ph-lightning w-4 h-4"></i> {{ $workerLevel['label'] ?? 'Rising Hustler' }} · {{ $completedGigsCount }} jobs completed
            </div>
            @endif
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

        @if((auth()->user()->theme_style ?? 'glass') === 'plain')
        <div class="pfl-hero-stats-card">
            <div><span class="v">{{ $completedGigsCount }}</span><span class="l">Jobs</span></div>
            <div><span class="v">@if($averageRating)★ {{ $averageRating }}@else —@endif</span><span class="l">Rating</span></div>
            <div><span class="v">{{ $reviewsReceivedCount }}</span><span class="l">Reviews</span></div>
        </div>
        @endif

        @php
            $pflThemeLabels = ['glass' => 'Glass', 'neumorphic' => 'Neumorphic', 'plain' => 'Minimal', 'aurora' => 'Aurora'];
            $pflTierLabel = $user->tier === 'tier1' ? 'Plus' : ($user->tier === 'tier2' ? 'Pro' : ($user->tier === 'tier3' ? 'Premium' : 'Free'));
        @endphp

        @if((auth()->user()->theme_style ?? 'glass') === 'plain')
        <div class="pfl-flat-list">
            <div class="pfl-section-label">Wallet</div>
            <div class="pfl-inline-card">
                <div class="pfl-inline-top">
                    <span class="pfl-flat-icon pfl-flat-blue"><i class="ph ph-wallet w-4 h-4"></i></span>
                    <div class="pfl-inline-top-text">
                        <span class="pfl-flat-title">Wallet Balance</span>
                        <span class="pfl-inline-amount">₦{{ number_format($user->wallet_balance ?? 0, 2) }}</span>
                    </div>
                </div>
                @if($user->virtual_account_number)
                <div class="pfl-inline-row"><span class="l">Account</span><span class="v">•••• {{ substr($user->virtual_account_number, -4) }} · {{ $user->virtual_account_bank }}</span></div>
                @endif
                <div class="pfl-inline-actions">
                    <a href="{{ route('wallet.index') }}" class="pfl-inline-btn pfl-inline-btn-primary">Send / Withdraw</a>
                    <a href="{{ route('wallet.index') }}" class="pfl-inline-btn">Full Wallet</a>
                </div>
            </div>

            <div class="pfl-section-label">Reputation</div>
            <div class="pfl-inline-card">
                <div class="pfl-stat-row">
                    <div class="pfl-stat"><span class="v">{{ $completedGigsCount }}</span><span class="l">Jobs done</span></div>
                    <div class="pfl-stat"><span class="v">{{ $averageRating ?: '—' }}</span><span class="l">Rating</span></div>
                    <div class="pfl-stat"><span class="v">{{ $reviewsReceivedCount }}</span><span class="l">Reviews</span></div>
                </div>
                @forelse($recentReviews as $review)
                    <div class="pfl-review-row">
                        <span class="pfl-review-name">{{ $review->reviewer->name ?? 'Anonymous' }}</span>
                        <span class="pfl-review-stars">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}</span>
                        @if($review->comment)<p class="pfl-review-comment">{{ \Illuminate\Support\Str::limit($review->comment, 70) }}</p>@endif
                    </div>
                @empty
                    <p class="pfl-note">No reviews yet.</p>
                @endforelse
                <a href="{{ route('profile.reputation') }}" class="pfl-open-link">See full reputation <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
            </div>

            <div class="pfl-section-label">Plan</div>
            <div class="pfl-flat-card">
                <span class="pfl-flat-icon pfl-flat-purple"><i class="ph ph-crown w-4 h-4"></i></span>
                <span class="pfl-flat-text">
                    <span class="pfl-flat-title">{{ $user->tier === 'tier1' ? 'Plus' : ($user->tier === 'tier2' ? 'Pro' : ($user->tier === 'tier3' ? 'Premium' : 'Free')) }}</span>
                    <span class="pfl-flat-sub">@if($user->subscription_expires_at) Expires {{ $user->subscription_expires_at->format('M j, Y') }} @else No active subscription @endif</span>
                </span>
                <a href="{{ route('subscriptions.index') }}" class="pfl-flat-chevron"><i class="ph ph-caret-right w-4 h-4"></i></a>
            </div>

            <div class="pfl-section-label">Referrals</div>
            <div class="pfl-inline-card">
                <div class="pfl-inline-top">
                    <span class="pfl-flat-icon pfl-flat-orange"><i class="ph ph-users w-4 h-4"></i></span>
                    <div class="pfl-inline-top-text">
                        <span class="pfl-flat-title">Your code</span>
                        <span class="pfl-inline-amount" style="font-size:16px;">{{ $user->referral_code }}</span>
                    </div>
                    <button type="button" class="pfl-flat-copy-btn" data-pfl-copy="{{ $user->referral_code }}"><i class="ph ph-copy w-4 h-4"></i></button>
                </div>
                <div class="pfl-stat-row">
                    <div class="pfl-stat"><span class="v">{{ $allReferralsCount ?? 0 }}</span><span class="l">Total</span></div>
                    <div class="pfl-stat"><span class="v">{{ $activeReferralsCount ?? 0 }}</span><span class="l">Active</span></div>
                </div>
                @forelse(($recentReferrals ?? collect())->take(4) as $ref)
                    <div class="pfl-review-row"><span class="pfl-review-name">{{ $ref->name }}</span><span class="pfl-flat-sub">{{ $ref->tier === 'free' ? 'Free' : ucfirst($ref->tier) }}</span></div>
                @empty
                    <p class="pfl-note">No referrals yet — share your code above.</p>
                @endforelse
                <a href="{{ route('referrals.index') }}" class="pfl-open-link">Full referral details <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
            </div>
            <div class="pfl-section-label">Appearance</div>
            <div class="pfl-inline-card">
                <div class="pfl-inline-top">
                    <span class="pfl-flat-icon pfl-flat-gold"><i class="ph ph-palette w-4 h-4"></i></span>
                    <div class="pfl-inline-top-text">
                        <span class="pfl-flat-title">{{ ['glass'=>'Glass','neumorphic'=>'Neumorphic','plain'=>'Minimal','aurora'=>'Aurora'][$user->theme_style ?? 'glass'] ?? 'Glass' }} theme</span>
                        <span class="pfl-flat-sub">Accent · Nav style</span>
                    </div>
                    <span class="pfl-swatch-lg" style="background:{{ $user->accent_color ?? '#0F9D6E' }};"></span>
                </div>
                <div class="pfl-inline-row"><span class="l">Accent Color</span><span class="v">{{ $user->accent_color ?? '#0F9D6E' }}</span></div>
                <div class="pfl-inline-row"><span class="l">Nav Style</span><span class="v">{{ ['edge_dock' => 'Edge Dock', 'halo_dock' => 'Halo Dock'][$user->mobile_nav_style ?? ''] ?? 'Painted Pill' }}</span></div>
                <a href="{{ route('profile.appearance') }}" class="pfl-open-link">Customize appearance <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
            </div>

            <div class="pfl-section-label">Money Tracker</div>
            <div class="pfl-inline-card">
                <div class="pfl-stat-row">
                    <div class="pfl-stat"><span class="v" style="color:#16a34a;">₦{{ number_format($monthIn ?? 0) }}</span><span class="l">In this month</span></div>
                    <div class="pfl-stat"><span class="v" style="color:#dc2626;">₦{{ number_format($monthOut ?? 0) }}</span><span class="l">Out this month</span></div>
                </div>
                <a href="{{ route('money-tracker.index') }}" class="pfl-open-link">Full money tracker <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
            </div>

            <div class="pfl-section-label">My Posts</div>
            <div class="pfl-inline-card">
                <div class="pfl-stat-row">
                    <div class="pfl-stat"><span class="v">{{ $myGigsPosted ?? 0 }}</span><span class="l">Gigs posted</span></div>
                    <div class="pfl-stat"><span class="v">{{ $myListingsPosted ?? 0 }}</span><span class="l">Listings posted</span></div>
                </div>
                <a href="{{ route('posts.index') }}" class="pfl-open-link">View all my posts <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
            </div>

            <div class="pfl-section-label">Leaderboard</div>
            <div class="pfl-flat-card">
                <span class="pfl-flat-icon pfl-flat-blue"><i class="ph ph-trophy w-4 h-4"></i></span>
                <span class="pfl-flat-text">
                    <span class="pfl-flat-title">{{ ($profileLeaderboardRank ?? null) ? 'Ranked #' . $profileLeaderboardRank . ' this week' : 'Not ranked yet' }}</span>
                    <span class="pfl-flat-sub">{{ ($profileLeaderboardRank ?? null) ? 'Keep earning to climb higher' : 'Opt in to appear on the leaderboard' }}</span>
                </span>
                <a href="{{ route('leaderboard.index') }}" class="pfl-flat-chevron"><i class="ph ph-caret-right w-4 h-4"></i></a>
            </div>
            <div class="pfl-flat-card">
                <span class="pfl-flat-icon pfl-flat-orange"><i class="ph ph-user w-4 h-4"></i></span>
                <span class="pfl-flat-text">
                    <span class="pfl-flat-title">Account</span>
                    <span class="pfl-flat-sub">{{ $user->email }}</span>
                </span>
                <a href="{{ route('profile.account') }}" class="pfl-flat-chevron"><i class="ph ph-caret-right w-4 h-4"></i></a>
            </div>
            @if($user->is_admin ?? false)
            <div class="pfl-flat-card">
                <span class="pfl-flat-icon pfl-flat-pink"><i class="ph ph-shield w-4 h-4"></i></span>
                <span class="pfl-flat-text"><span class="pfl-flat-title">Admin</span></span>
                <a href="{{ route('admin.index') }}" class="pfl-flat-chevron"><i class="ph ph-caret-right w-4 h-4"></i></a>
            </div>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="pfl-logout-form">
                @csrf
                <button type="submit" class="pfl-logout-btn"><i class="ph ph-sign-out w-4 h-4"></i> Log Out</button>
            </form>
        </div>
        @else
        <div class="glass-card pfl-accordion reveal-on-scroll" style="overflow:hidden;">

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflReputation">
                    <span class="profile-list-row-icon"><i class="ph ph-star w-4 h-4"></i></span>
                    <span class="profile-list-row-text">
                        <span class="profile-list-row-title">Reputation</span>
                        <span class="profile-list-row-sub">Jobs completed, rating, reviews</span>
                    </span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflReputation">
                    <div class="pfl-stat-row">
                        <div class="pfl-stat"><span class="v">{{ $completedGigsCount }}</span><span class="l">Jobs done</span></div>
                        <div class="pfl-stat"><span class="v">{{ $averageRating ?: '—' }}</span><span class="l">Rating</span></div>
                        <div class="pfl-stat"><span class="v">{{ $reviewsReceivedCount }}</span><span class="l">Reviews</span></div>
                    </div>
                    <a href="{{ route('profile.reputation') }}" class="pfl-open-link">Open full reputation page <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflAccount">
                    <span class="profile-list-row-icon"><i class="ph ph-user w-4 h-4"></i></span>
                    <span class="profile-list-row-text">
                        <span class="profile-list-row-title">Account Details</span>
                        <span class="profile-list-row-sub">Name, email, password</span>
                    </span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflAccount">
                    <div class="pfl-kv"><span class="l">Name</span><span class="v">{{ $user->name }}</span></div>
                    <div class="pfl-kv"><span class="l">Email</span><span class="v">{{ $user->email }}</span></div>
                    <a href="{{ route('profile.account') }}" class="pfl-open-link">Edit account details <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflAppearance">
                    <span class="profile-list-row-icon"><i class="ph ph-palette w-4 h-4"></i></span>
                    <span class="profile-list-row-text">
                        <span class="profile-list-row-title">Appearance</span>
                        <span class="profile-list-row-sub">Theme, accent color, dock, sidebar, dark mode</span>
                    </span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflAppearance">
                    <div class="pfl-kv"><span class="l">Current theme</span><span class="v">{{ $pflThemeLabels[$user->theme_style ?? 'glass'] ?? 'Glass' }}</span></div>
                    <div class="pfl-kv"><span class="l">Accent color</span><span class="v"><span class="pfl-swatch" style="background:{{ $user->accent_color ?? '#0F9D6E' }};"></span> {{ $user->accent_color ?? '#0F9D6E' }}</span></div>
                    <a href="{{ route('profile.appearance') }}" class="pfl-open-link">Change appearance <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflWallet">
                    <span class="profile-list-row-icon"><i class="ph ph-wallet w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Wallet</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflWallet">
                    <div class="pfl-kv"><span class="l">Balance</span><span class="v">₦{{ number_format($user->wallet_balance ?? 0, 2) }}</span></div>
                    @if($user->virtual_account_number)
                    <div class="pfl-kv"><span class="l">Account</span><span class="v">•••• {{ substr($user->virtual_account_number, -4) }} · {{ $user->virtual_account_bank }}</span></div>
                    @endif
                    <a href="{{ route('wallet.index') }}" class="pfl-open-link">Open wallet <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflReferrals">
                    <span class="profile-list-row-icon"><i class="ph ph-users w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Referrals</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflReferrals">
                    <div class="pfl-kv"><span class="l">Your code</span><span class="v">{{ $user->referral_code }}</span></div>
                    <button type="button" class="pfl-quick-btn" data-pfl-copy="{{ $user->referral_code }}">
                        <i class="ph ph-copy w-3.5 h-3.5"></i> Copy referral code
                    </button>
                    <a href="{{ route('referrals.index') }}" class="pfl-open-link">View referral details <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflUpgrade">
                    <span class="profile-list-row-icon"><i class="ph-fill ph-crown"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Upgrade</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflUpgrade">
                    <div class="pfl-kv"><span class="l">Current plan</span><span class="v">{{ $pflTierLabel }}</span></div>
                    @if($user->subscription_expires_at)
                    <div class="pfl-kv"><span class="l">Renews / expires</span><span class="v">{{ $user->subscription_expires_at->format('M j, Y') }}</span></div>
                    @endif
                    <a href="{{ route('subscriptions.index') }}" class="pfl-open-link">See plans <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflMoney">
                    <span class="profile-list-row-icon"><i class="ph ph-chart-line w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Money Tracker</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflMoney">
                    <p class="pfl-note">Track your income and spending patterns over time.</p>
                    <a href="{{ route('money-tracker.index') }}" class="pfl-open-link">Open money tracker <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflPosts">
                    <span class="profile-list-row-icon"><i class="ph ph-file-text w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">My Posts</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflPosts">
                    <p class="pfl-note">Gigs and listings you've posted.</p>
                    <a href="{{ route('posts.index') }}" class="pfl-open-link">View my posts <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflLeaderboard">
                    <span class="profile-list-row-icon"><i class="ph ph-trophy w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Leaderboard</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflLeaderboard">
                    <p class="pfl-note">See how you rank against other students this week.</p>
                    <a href="{{ route('leaderboard.index') }}" class="pfl-open-link">View leaderboard <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflServices">
                    <span class="profile-list-row-icon"><i class="ph ph-lightbulb w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Services</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflServices">
                    <p class="pfl-note">Buy airtime, data, cable and electricity.</p>
                    <a href="{{ route('services.index') }}" class="pfl-open-link">Open services <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            <div class="pfl-item">
                <button type="button" class="profile-list-row pfl-trigger" data-pfl-target="pflPulse">
                    <span class="profile-list-row-icon"><i class="ph ph-lightning w-4 h-4"></i></span>
                    <span class="profile-list-row-text"><span class="profile-list-row-title">Pulse</span></span>
                    <i class="ph ph-caret-down w-4 h-4 profile-list-row-chevron pfl-chevron"></i>
                </button>
                <div class="pfl-panel" id="pflPulse">
                    <p class="pfl-note">Platform activity and performance insights.</p>
                    <a href="{{ route('pulse.index') }}" class="pfl-open-link">Open pulse <i class="ph ph-arrow-right w-3.5 h-3.5"></i></a>
                </div>
            </div>

            @if($user->is_admin ?? false)
            <a href="{{ route('admin.index') }}" class="profile-list-row">
                <span class="profile-list-row-icon"><i class="ph ph-shield w-4 h-4"></i></span>
                <span class="profile-list-row-text"><span class="profile-list-row-title">Admin</span></span>
                <i class="ph ph-caret-right w-4 h-4 profile-list-row-chevron"></i>
            </a>
            @endif
        </div>
        @endif

    </div>
</main>

<style>
.pfl-item{border-bottom:1px solid var(--border-color, rgba(0,0,0,0.06));}
.pfl-item:last-child{border-bottom:none;}
.pfl-trigger{width:100%;background:none;border:none;cursor:pointer;text-align:left;}
.pfl-chevron{transition:transform 0.25s ease;}
.pfl-item.pfl-open .pfl-chevron{transform:rotate(180deg);}
.pfl-panel{max-height:0;overflow:hidden;transition:max-height 0.3s ease, padding 0.3s ease;padding:0 1rem;}
.pfl-item.pfl-open .pfl-panel{max-height:260px;padding:0 1rem 1rem;}
.pfl-stat-row{display:flex;gap:0.75rem;margin-bottom:0.75rem;}
.pfl-stat{flex:1;background:var(--color-bg, rgba(0,0,0,0.03));border-radius:0.75rem;padding:0.6rem;text-align:center;}
.pfl-stat .v{display:block;font-size:1rem;font-weight:700;}
.pfl-stat .l{display:block;font-size:0.7rem;color:var(--text-muted, #8a8a8a);margin-top:2px;}
.pfl-kv{display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;padding:0.4rem 0;}
.pfl-kv .l{color:var(--text-muted, #8a8a8a);}
.pfl-kv .v{font-weight:600;display:flex;align-items:center;gap:6px;}
.pfl-swatch{width:14px;height:14px;border-radius:50%;display:inline-block;}
.pfl-note{font-size:0.85rem;color:var(--text-muted, #8a8a8a);margin:0.5rem 0;}
.pfl-quick-btn{display:flex;align-items:center;gap:6px;background:var(--color-accent, #0F9D6E);color:#fff;border:none;border-radius:999px;padding:0.5rem 0.9rem;font-size:0.8rem;font-weight:600;margin:0.4rem 0 0.6rem;cursor:pointer;}
.pfl-quick-btn.pfl-copied{background:#16a34a;}
.pfl-open-link{display:flex;align-items:center;gap:6px;font-size:0.8rem;font-weight:600;color:var(--color-accent, #0F9D6E);text-decoration:none;margin-top:0.5rem;}
</style>

<script>
(function(){
    document.querySelectorAll('.pfl-trigger').forEach(function(btn){
        btn.addEventListener('click', function(){
            var item = btn.closest('.pfl-item');
            var wasOpen = item.classList.contains('pfl-open');
            document.querySelectorAll('.pfl-item.pfl-open').forEach(function(open){ open.classList.remove('pfl-open'); });
            if (!wasOpen) item.classList.add('pfl-open');
        });
    });

    document.querySelectorAll('[data-pfl-copy]').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var text = btn.dataset.pflCopy;
            navigator.clipboard.writeText(text).then(function(){
                var original = btn.innerHTML;
                btn.classList.add('pfl-copied');
                btn.innerHTML = '<i class="ph ph-check w-3.5 h-3.5"></i> Copied!';
                setTimeout(function(){
                    btn.classList.remove('pfl-copied');
                    btn.innerHTML = original;
                }, 1500);
            });
        });
    });

    document.querySelectorAll('.pfl-open-link').forEach(function(link){
        link.addEventListener('click', function(e){ e.stopPropagation(); });
    });
})();
</script>
@endsection
