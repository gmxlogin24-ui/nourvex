@extends('layouts.app-authenticated')

@section('title', 'Dashboard 2')

@section('content')
<main class="max-w-6xl mx-auto p-6">
    <div class="d2-page">
        <div class="d2-layout">
            @php
                $d2Avatar = auth()->user()?->avatar_url;
                $d2Name = auth()->user()?->name ?? 'Guest';
                $d2Initial = strtoupper(substr($d2Name, 0, 1));
                $d2IsAdmin = auth()->user()?->is_admin ?? false;
                $d2IsCC = auth()->user()?->role === 'customer_care';
                $d2RouteName = request()->route() ? request()->route()->getName() : '';
                $d2Active = $d2RouteName ? explode('.', $d2RouteName)[0] : '';
            @endphp
            <div class="d2-left-union" id="d2UnionShape">
                <svg class="d2-union-svg" aria-hidden="true"></svg>
                <div class="d2-union-top-content">
                    @php
                        $d2Hour = now()->hour;
                        $d2Greeting = $d2Hour < 12 ? 'Good morning' : ($d2Hour < 17 ? 'Good afternoon' : 'Good evening');
                        $d2Milestone = $nextStreakMilestone ?? 7;
                        $d2MiniCircumference = 2 * pi() * 26;
                        $d2MiniPct = $d2Milestone > 0 ? min(100, round((($streakDays ?? 0) / $d2Milestone) * 100)) : 0;
                        $d2MiniOffset = $d2MiniCircumference * (1 - $d2MiniPct / 100);
                        $d2TopGig = ($topGigs ?? collect())->first();
                    @endphp
                    <div class="d2-spotlight">
                        @if($d2TopGig)
                            <span class="d2-spotlight-eyebrow">Top Hustle Right Now</span>
                            <div class="d2-spotlight-orb"><i data-lucide="briefcase" class="w-8 h-8"></i></div>
                            @if($d2TopGig->category)
                                <span class="d2-spotlight-category">{{ ucfirst($d2TopGig->category) }}</span>
                            @endif
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

            <div class="d2-right-col">
                <div class="d2-right-header d2-card"></div>
                <div class="d2-right-stack">
                    <div class="d2-right-pill">
                        <div class="d2-pill-label">Referral Earnings</div>
                        <div class="d2-pill-value">₦{{ number_format($referralEarnings) }}</div>
                    </div>
                    <div class="d2-right-pill">
                        <div class="d2-pill-label">Hustle Income</div>
                        <div class="d2-pill-value">₦{{ number_format($earnings) }}</div>
                    </div>
                    <div class="d2-right-pill">
                        <div class="d2-pill-label">Total Balance</div>
                        <div class="d2-pill-value">₦{{ number_format($balance) }}</div>
                    </div>
                </div>
            </div>

            <div class="d2-fan-card d2-card">
                <div class="d2-fan-header">
                    <div class="d2-fan-title-bar"></div>
                    <div class="d2-fan-avatar"></div>
                </div>
                <div class="d2-fan-stack">
                    <div class="d2-card"></div>
                    <div class="d2-card"></div>
                    <div class="d2-card"></div>
                </div>
            </div>
        </div>

        <div class="d2-flow-card">
            <div class="d2-flow-header">
                <span class="d2-flow-title">Money Flow</span>
                <span class="d2-flow-value">₦{{ number_format($netFlow ?? 0) }} net</span>
            </div>
            <div class="d2-liquid-track">
                <div class="d2-liquid-fill" style="width: {{ ($totalIn ?? 0) > 0 ? min(100, round((($netFlow ?? 0) / $totalIn) * 100)) : 0 }}%;">
                    <span class="d2-liquid-shimmer"></span>
                </div>
            </div>
            <div class="d2-flow-meta">
                <span>In: ₦{{ number_format($totalIn ?? 0) }}</span>
                <span>Out: ₦{{ number_format($totalOut ?? 0) }}</span>
            </div>
        </div>
    </div>
</main>

<script src="{{ asset('js/dashboard2-shape.js') }}"></script>
@endsection
