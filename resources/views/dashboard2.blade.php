@extends('layouts.app-authenticated')

@section('title', 'Dashboard 2')

@section('content')
<main class="w-full p-6">
@if((auth()->user()->theme_style ?? 'glass') === 'plain')
    @include('partials.dashboard-minimal')
@elseif((auth()->user()->theme_style ?? 'glass') === 'aurora')
    @include('partials.dashboard-aurora')
@else
    @php
        $d2Avatar = auth()->user()?->avatar_url;
        $d2Name = auth()->user()?->name ?? 'Guest';
        $d2Initial = strtoupper(substr($d2Name, 0, 1));
        $d2IsAdmin = auth()->user()?->is_admin ?? false;
        $d2IsCC = auth()->user()?->role === 'customer_care';
        $d2RouteName = request()->route() ? request()->route()->getName() : '';
        $d2Active = $d2RouteName ? explode('.', $d2RouteName)[0] : '';
        $d2Hour = now()->hour;
        $d2Greeting = $d2Hour < 12 ? 'Good morning' : ($d2Hour < 17 ? 'Good afternoon' : 'Good evening');
        $d2Milestone = $nextStreakMilestone ?? 7;
        $d2MiniCircumference = 2 * pi() * 26;
        $d2MiniPct = $d2Milestone > 0 ? min(100, round((($streakDays ?? 0) / $d2Milestone) * 100)) : 0;
        $d2MiniOffset = $d2MiniCircumference * (1 - $d2MiniPct / 100);
        $d2TopGig = ($topGigs ?? collect())->first();
    @endphp

    <div class="d2-page">

        <div class="d2-topbar">
            <div class="d2-search">
                <i data-lucide="search" class="w-4 h-4"></i>
                <input type="text" placeholder="Search for gigs, items, people...">
            </div>
            <div class="d2-topbar-icons">
                <a href="{{ Route::has('chat.index') ? route('chat.index') : '#' }}" class="d2-topbar-icon-btn" title="Chat">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                    @if(($navUnreadChats ?? 0) > 0)<span class="d2-topbar-badge">{{ $navUnreadChats }}</span>@endif
                </a>
                <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="d2-topbar-icon-btn" title="Notifications">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    @if(($unreadNotifications ?? 0) > 0)<span class="d2-topbar-badge">{{ $unreadNotifications }}</span>@endif
                </a>
                <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="d2-topbar-wallet">
                    <i data-lucide="gem" class="w-4 h-4"></i>
                    <span>{{ number_format($qCoinBalance) }} Q</span>
                </a>
                <a href="{{ route('profile.index') }}" class="d2-topbar-avatar">
                    @if($d2Avatar)<img src="{{ $d2Avatar }}" alt="">@else<span>{{ $d2Initial }}</span>@endif
                </a>
            </div>
        </div>

        <div class="d2-body-grid">
            <div class="d2-main-col">

                <div class="d2-qcoin-row">
                    <div class="d2-stat-card d2-stat-purple d2-qcoin-full">
                        <div class="d2-stat-icon"><i data-lucide="gem" class="w-5 h-5"></i></div>
                        <div class="d2-stat-body">
                            <span class="d2-stat-label">Q Coin Balance</span>
                            <span class="d2-stat-value">{{ number_format($qCoinBalance) }}</span>
                            <div class="d2-stat-liquid-track"><div class="d2-stat-liquid-fill d2-stat-liq-purple" style="width: {{ $qCoinBalance > 0 ? min(100, round($qCoinBalance / 10)) : 0 }}%;"><span class="d2-liquid-shimmer"></span></div></div>
                        </div>
                    </div>
                </div>

                <div class="d2-stat-row">
                    <div class="d2-stat-card d2-stat-green">
                        <div class="d2-stat-icon"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                        <div class="d2-stat-body">
                            <span class="d2-stat-label">Active Gigs</span>
                            <span class="d2-stat-value">{{ $myOpenGigs }}</span>
                            @include('partials.d2-sparkline', ['seed' => $myOpenGigs + 2, 'color' => '#22c55e'])
                        </div>
                    </div>
                    <div class="d2-stat-card d2-stat-blue">
                        <div class="d2-stat-icon"><i data-lucide="wallet" class="w-5 h-5"></i></div>
                        <div class="d2-stat-body">
                            <span class="d2-stat-label">Total Earnings</span>
                            <span class="d2-stat-value">₦{{ number_format($earnings) }}</span>
                            @include('partials.d2-sparkline', ['seed' => (int) ($earnings / 1000) + 5, 'color' => '#3b82f6'])
    @if(!($isVerified ?? true))
    <div class="glass-card p-4 mb-4" style="border:1px solid var(--color-accent);">
        <p class="text-sm font-medium">Unverified wallet limit</p>
        <p class="text-muted text-xs mt-1">You can hold up to ₦{{ number_format($walletCapRemaining ?? 0) }} more before you need to verify your BVN. <a href="{{ route('wallet.virtual-account') }}" class="text-accent underline">Verify now</a></p>
    </div>
    @endif
                        </div>
                    </div>
                    <div class="d2-stat-card d2-stat-orange">
                        <div class="d2-stat-icon"><i data-lucide="award" class="w-5 h-5"></i></div>
                        <div class="d2-stat-body">
                            <span class="d2-stat-label">Success Rate</span>
                            <span class="d2-stat-value">Coming soon</span>
                            @include('partials.d2-sparkline', ['seed' => 7, 'color' => '#fb923c'])
                        </div>
                    </div>
                    <div class="d2-stat-card d2-stat-purple">
                        <div class="d2-stat-icon"><i data-lucide="star" class="w-5 h-5"></i></div>
                        <div class="d2-stat-body">
                            <span class="d2-stat-label">Rating</span>
                            <span class="d2-stat-value">{{ $avgRating ? number_format($avgRating,1) : "—" }}</span>
                            @include('partials.d2-sparkline', ['seed' => (int) (($avgRating ?? 1) * 10) + 1, 'color' => '#8b5cf6'])
                        </div>
                    </div>
                </div>

                <div class="d2-quick-actions">
                    <a href="{{ route('gigs.create') }}" class="d2-qa-item d2-qa-green">
                        <span class="d2-qa-icon"><i data-lucide="briefcase" class="w-5 h-5"></i></span>
                        <span class="d2-qa-text">
                            <span class="d2-qa-title">Post a Gig</span>
                            <span class="d2-qa-desc">Offer your skills</span>
                        </span>
                        <i data-lucide="arrow-right" class="w-4 h-4 d2-qa-arrow"></i>
                    </a>
                    <a href="{{ route('marketplace.create') }}" class="d2-qa-item d2-qa-orange">
                        <span class="d2-qa-icon"><i data-lucide="tag" class="w-5 h-5"></i></span>
                        <span class="d2-qa-text">
                            <span class="d2-qa-title">List an Item</span>
                            <span class="d2-qa-desc">Sell in marketplace</span>
                        </span>
                        <i data-lucide="arrow-right" class="w-4 h-4 d2-qa-arrow"></i>
                    </a>
                    <a href="{{ route('services.index') }}" class="d2-qa-item d2-qa-blue">
                        <span class="d2-qa-icon"><i data-lucide="smartphone" class="w-5 h-5"></i></span>
                        <span class="d2-qa-text">
                            <span class="d2-qa-title">Buy Airtime/Data</span>
                            <span class="d2-qa-desc">Top up instantly</span>
                        </span>
                        <i data-lucide="arrow-right" class="w-4 h-4 d2-qa-arrow"></i>
                    </a>
                </div>

                <div class="d2-layout">
                    <div class="d2-left-union" id="d2UnionShape">
                        <svg class="d2-union-svg" aria-hidden="true"></svg>
                        <div class="d2-union-top-content">
                            <div class="d2-spotlight">
                                @include("partials.levi-inline")
                            @if($d2TopGig)
                                    <h3 class="d2-spotlight-title">{{ $d2TopGig->title }}</h3>
                                    @if($d2TopGig->description)
                                        <p class="d2-spotlight-desc">{{ \Illuminate\Support\Str::limit($d2TopGig->description, 70) }}</p>
                                    @endif
                                    <p class="d2-spotlight-amount">₦{{ number_format($d2TopGig->amount) }}</p>
                                    @if($d2TopGig->poster)
                                        <div class="d2-spotlight-poster">
                                            <div class="d2-spotlight-poster-avatar">
                                                @if($d2TopGig->poster->avatar_url)<img src="{{ $d2TopGig->poster->avatar_url }}" alt="">@else<span>{{ strtoupper(substr($d2TopGig->poster->name, 0, 1)) }}</span>@endif
                                            </div>
                                            <span>by {{ $d2TopGig->poster->name }}</span>
                                        </div>
                                    @endif
                                    <a href="{{ route('gigs.show', $d2TopGig->id) }}" class="d2-spotlight-cta">View Hustle</a>
                                    <a href="{{ route('gigs.index') }}" class="d2-spotlight-browse-all">{{ $gigCount ?? 0 }} hustles open · Browse all</a>
                                @else
                                    <span class="d2-spotlight-eyebrow">No hustles posted yet</span>
                                    <div class="d2-spotlight-orb"><i data-lucide="briefcase" class="w-8 h-8"></i></div>
                                    <p class="d2-spotlight-amount">Check back soon</p>
                                    <a href="{{ route('gigs.create') }}" class="d2-spotlight-cta">Post the First One</a>
                                @endif
                            </div>
                        </div>
                        <div class="d2-union-bottom-row">
                            <div class="d2-union-leg-content">
                                <div class="d2-mini-hero">
                                    <p class="d2-mini-greeting">{{ $d2Greeting }}, {{ explode(' ', $d2Name)[0] }} 👋</p>
                                    <div class="d2-mini-row">
                                        <div class="d2-mini-ring-wrap">
                                            <svg class="d2-mini-ring" viewBox="0 0 60 60">
                                                <circle class="d2-mini-ring-track" cx="30" cy="30" r="26"></circle>
                                                <circle class="d2-mini-ring-fill" cx="30" cy="30" r="26"
                                                    stroke-dasharray="{{ $d2MiniCircumference }}"
                                                    stroke-dashoffset="{{ $d2MiniOffset }}"></circle>
                                            </svg>
                                            <div class="d2-mini-ring-center">
                                                <span class="d2-mini-streak-num">{{ $streakDays ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('gigs.create') }}" class="d2-mini-cta">Post a Hustle</a>
                                    </div>
                                </div>
                            </div>
                            <div class="d2-small-card d2-card">
                                <div class="d2-quick-grid">
                                    <a href="{{ route('services.index') }}" class="d2-quick-icon" title="Airtime"><i data-lucide="zap" class="w-4 h-4"></i></a>
                                    <a href="{{ route('services.index') }}" class="d2-quick-icon" title="Data"><i data-lucide="wifi" class="w-4 h-4"></i></a>
                                    <a href="{{ route('services.index') }}" class="d2-quick-icon" title="Cable TV"><i data-lucide="tv" class="w-4 h-4"></i></a>
                                    <a href="{{ route('services.index') }}" class="d2-quick-icon" title="Electricity"><i data-lucide="plug-zap" class="w-4 h-4"></i></a>
                                    <a href="{{ route('gigs.index') }}" class="d2-quick-icon" title="Hustles"><i data-lucide="briefcase" class="w-4 h-4"></i></a>
                                    <a href="{{ route('marketplace.index') }}" class="d2-quick-icon" title="Market"><i data-lucide="shopping-bag" class="w-4 h-4"></i></a>
                                    <a href="{{ route('referrals.index') }}" class="d2-quick-icon" title="Referral"><i data-lucide="users" class="w-4 h-4"></i></a>
                                    <a href="{{ route('subscriptions.index') }}" class="d2-quick-icon" title="Upgrade"><i data-lucide="crown" class="w-4 h-4"></i></a>
                                    <a href="{{ route('news.index') }}" class="d2-quick-icon" title="News"><i data-lucide="newspaper" class="w-4 h-4"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d2-secondary-row">

                    <div class="d2-marketplace-overview-card d2-card">
                        <div class="d2-card-header"><span>Marketplace Overview</span></div>
                        <div class="d2-mp-grid">
                            <div class="d2-mp-stat"><span class="val">{{ $myListings }}</span><span class="lbl">Active Listings</span></div>
                            <div class="d2-mp-stat d2-mp-placeholder"><span class="val">Coming soon</span><span class="lbl">Total Sales</span></div>
                            <div class="d2-mp-stat d2-mp-placeholder"><span class="val">Coming soon</span><span class="lbl">Orders</span></div>
                            <div class="d2-mp-stat d2-mp-placeholder"><span class="val">Coming soon</span><span class="lbl">Store Views</span></div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="d2-side-col">
                <div class="d2-profile-card-v2">
                    @if($d2Avatar)
                        <img src="{{ $d2Avatar }}" alt="" class="d2-pcv2-photo">
                    @else
                        <div class="d2-pcv2-photo d2-pcv2-photo-fallback">{{ $d2Initial }}</div>
                    @endif
                    <div class="d2-pcv2-scrim"></div>
                    <div class="d2-pcv2-content">
                        <div class="d2-pcv2-name">{{ $d2Name }} <i data-lucide="badge-check"></i></div>
                        <div class="d2-pcv2-desc">{{ auth()->user()->workerLevel()['label'] }}</div>
                        <div class="d2-pcv2-stats">
                            <div class="d2-pcv2-counts">
                                <span>{{ $myCompletedGigs }} Gigs</span>
                                <span>{{ $avgRating ? number_format($avgRating, 1) : '—' }} ({{ $reviewCount }})</span>
                            </div>
                            <a href="{{ route('profile.index') }}" class="d2-pcv2-btn">Edit</a>
                        </div>
                    </div>
                </div>

                <div class="d2-activity-card d2-card">
                    <div class="d2-card-header"><span>Your Activity</span></div>
                    @forelse($todayFeed->take(5) as $item)
                        <a href="{{ $item['url'] }}" class="d2-activity-row">
                            <span class="d2-activity-dot d2-activity-{{ $item['type'] }}"></span>
                            <span class="d2-activity-title">{{ $item['title'] }}</span>
                            <span class="d2-activity-time">{{ \Carbon\Carbon::parse($item['time'])->diffForHumans() }}</span>
                        </a>
                    @empty
                        <p class="d2-empty-note">Nothing new yet.</p>
                    @endforelse
                </div>

                <div class="d2-qcoin-card">
                    <div class="d2-qcoin-icon"><i data-lucide="gem" class="w-6 h-6"></i></div>
                    <div class="d2-qcoin-body">
                        <span class="d2-qcoin-title">Q Coins</span>
                        <span class="d2-qcoin-desc">Complete tasks and claim rewards daily.</span>
                        <span class="d2-qcoin-balance">{{ number_format($qCoinBalance) }} Q</span>
                    </div>
                    @if($qCoinClaimedToday)
                        <span class="d2-qcoin-claimed">Claimed today</span>
                    @else
                        <form method="POST" action="{{ route('qcoin.claim') }}">
                            @csrf
                            <button type="submit" class="d2-qcoin-claim-btn">Claim Now</button>
                        </form>
                    @endif
                </div>

                <div class="d2-recent-gigs-card d2-card">
                        <div class="d2-card-header">
                            <span>Recent Gigs</span>
                            <a href="{{ route('gigs.index') }}">View all</a>
                        </div>
                        @forelse($topGigs as $gig)
                            <a href="{{ route('gigs.show', $gig->id) }}" class="d2-gig-row">
                                <span class="d2-gig-title">{{ $gig->title }}</span>
                                <span class="d2-gig-status">{{ ucfirst($gig->status ?? '') }}</span>
                                <span class="d2-gig-amount">₦{{ number_format($gig->amount) }}</span>
                            </a>
                        @empty
                            <p class="d2-empty-note">No gigs yet.</p>
                        @endforelse
                    </div>

                <div class="d2-flow-card d2-flow-card-wave">
                    <div class="d2-flow-top">
                        <span class="d2-flow-pill d2-flow-pill-in"><i data-lucide="arrow-down" class="w-3 h-3"></i> In ₦{{ number_format($totalIn ?? 0) }}</span>
                        <span class="d2-flow-pill d2-flow-pill-out"><i data-lucide="arrow-up" class="w-3 h-3"></i> Out ₦{{ number_format($totalOut ?? 0) }}</span>
                    </div>
                    <div class="d2-flow-header">
                        <span class="d2-flow-title">Money Flow</span>
                        <span class="d2-flow-value">₦{{ number_format($netFlow ?? 0) }} <small>net</small></span>
                    </div>
                    <div class="d2-wave-wrap">
                        @php
                            $d2Seed = max(1, (int) (($netFlow ?? 0) + ($totalIn ?? 0) + ($totalOut ?? 0)));
                            $d2Pts = [];
                            for ($i = 0; $i <= 6; $i++) {
                                $n = sin($i * 1.3 + ($d2Seed % 7)) * 20 + cos($i * 0.7) * 12;
                                $d2Pts[] = round(50 - $n, 1);
                            }
                            $d2LinePath = '';
                            foreach ($d2Pts as $i => $y) {
                                $x = $i * 50;
                                $d2LinePath .= ($i === 0 ? "M{$x},{$y} " : "L{$x},{$y} ");
                            }
                            $d2FillPath = "M0,100 L0,{$d2Pts[0]} ";
                            foreach ($d2Pts as $i => $y) {
                                if ($i === 0) continue;
                                $x = $i * 50;
                                $d2FillPath .= "L{$x},{$y} ";
                            }
                            $d2FillPath .= "L300,100 Z";
                        @endphp
                        <svg class="d2-wave-svg" viewBox="0 0 300 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="d2WaveFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#fb923c" stop-opacity="0.45"/>
                                    <stop offset="100%" stop-color="#fb923c" stop-opacity="0"/>
                                </linearGradient>
                                <linearGradient id="d2WaveStroke" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#fb923c"/>
                                    <stop offset="100%" stop-color="#8b5cf6"/>
                                </linearGradient>
                            </defs>
                            <path d="{{ $d2FillPath }}" fill="url(#d2WaveFill)"></path>
                            <path d="{{ $d2LinePath }}" fill="none" stroke="url(#d2WaveStroke)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <circle cx="{{ 3 * 50 }}" cy="{{ $d2Pts[3] }}" r="4" fill="#fff"></circle>
                        </svg>
                    </div>
                    <div class="d2-flow-meta">
                        <span>In: ₦{{ number_format($totalIn ?? 0) }}</span>
                        <span>Out: ₦{{ number_format($totalOut ?? 0) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endif
</main>

<style>
.d2-flow-card-wave{padding:18px 18px 14px}
.d2-flow-top{display:flex;gap:8px;margin-bottom:10px}
.d2-flow-pill{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.75)}
.d2-flow-pill-in{color:#4ade80}
.d2-flow-pill-out{color:#fb923c}
.d2-flow-value small{font-size:11px;font-weight:500;opacity:.6;margin-left:4px}
.d2-wave-wrap{width:100%;height:90px;margin:8px 0 4px}
.d2-wave-svg{width:100%;height:100%;display:block}
.d2-sparkline{width:70px;height:24px;display:block;margin-top:6px;opacity:.9}
</style>
<script src="{{ asset('js/dashboard2-shape.js') }}"></script>
@endsection
