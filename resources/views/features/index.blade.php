@extends('layouts.landing')
@section('content')

<link rel="stylesheet" href="{{ asset('css/features.css') }}?v={{ time() }}">

<div class="ft-page">

<section class="ft-hero reveal-on-scroll">
    <span class="ft-eyebrow">What NAURVEX is</span>
    <h1>Everything your campus hustle needs, <span class="text-accent">in one app.</span></h1>
    <p>A live feed, a job board, a campus market, and a wallet — built for students, not adapted from something else.</p>
</section>

<section class="ft-feature reveal-on-scroll">
    <div class="ft-feature-text">
        <span class="ft-tag ft-tag-blue">Buzz &amp; Pulse</span>
        <h2>See what's happening, the second it happens.</h2>
        <p>Buzz is your scrolling feed of campus news, announcements, and sponsored posts. Pulse is the fast, visual layer on top — short posts, photos, and clips from students and the platform, TikTok-style.</p>
        <ul class="ft-list">
            <li>Open to everyone, no account needed to browse</li>
            <li>Comment and react once you're signed in</li>
            <li>Verified news mixed with real campus voices</li>
        </ul>
        <a href="{{ route('pulse.index') }}" class="btn-primary">Open Pulse</a>
    </div>
    <div class="ft-mock ft-mock-pulse">
        <div class="ft-mock-bar"></div>
        <div class="ft-mock-card">
            <div class="ft-mock-media"></div>
            <div class="ft-mock-eyebrow"><span></span>Campus News</div>
            <div class="ft-mock-line ft-mock-line-title"></div>
            <div class="ft-mock-line"></div>
            <div class="ft-mock-line short"></div>
        </div>
    </div>
</section>

<section class="ft-feature ft-feature-rev reveal-on-scroll">
    <div class="ft-feature-text">
        <span class="ft-tag ft-tag-purple">Hustles</span>
        <h2>Work you can actually get paid for.</h2>
        <p>Post a task or offer a service, and other students apply — tutoring, design, errands, deliveries, whatever your campus needs. Accept, message, complete, and get reviewed, all inside the app.</p>
        <ul class="ft-list">
            <li>Browse and read gig details as a guest</li>
            <li>Applying and posting need a Tier 1+ account</li>
            <li>Payment lands straight in your wallet</li>
        </ul>
        <a href="{{ route('gigs.index') }}" class="btn-primary">Browse Hustles</a>
    </div>
    <div class="ft-mock ft-mock-gigs">
        <div class="ft-mock-gig-row"><span class="ft-mock-badge">Open</span><div class="ft-mock-line-sm"></div></div>
        <div class="ft-mock-gig-row"><span class="ft-mock-badge">Open</span><div class="ft-mock-line-sm"></div></div>
        <div class="ft-mock-gig-row featured"><span class="ft-mock-badge">Open</span><div class="ft-mock-line-sm"></div></div>
        <div class="ft-mock-price">₦8,500</div>
    </div>
</section>

<section class="ft-feature reveal-on-scroll">
    <div class="ft-feature-text">
        <span class="ft-tag ft-tag-orange">Campus Market</span>
        <h2>Buy and sell, campus to campus.</h2>
        <p>Textbooks, furniture, tech, anything — list it in minutes and sell directly to other students. No shipping games, no strangers off-campus.</p>
        <ul class="ft-list">
            <li>Browse listings freely, even signed out</li>
            <li>Selling needs a Tier 1+ account</li>
            <li>Reviews build trust between buyers and sellers</li>
        </ul>
        <a href="{{ route('marketplace.index') }}" class="btn-primary">Browse Campus Market</a>
    </div>
    <div class="ft-mock ft-mock-market">
        <div class="ft-mock-grid">
            <div class="ft-mock-tile"></div>
            <div class="ft-mock-tile"></div>
            <div class="ft-mock-tile"></div>
            <div class="ft-mock-tile"></div>
        </div>
    </div>
</section>

<section class="ft-feature ft-feature-rev reveal-on-scroll">
    <div class="ft-feature-text">
        <span class="ft-tag ft-tag-green">Wallet &amp; Referrals</span>
        <h2>Every naira, tracked in one place.</h2>
        <p>Hustles, sales, and referrals all pay into your wallet. Withdraw whenever you want, or connect a virtual account for instant transfers — no waiting around for payouts.</p>
        <ul class="ft-list">
            <li>One wallet for every way you earn</li>
            <li>Premium members earn 15% on every referral</li>
            <li>Instant virtual account transfers</li>
        </ul>
        <a href="{{ route('register') }}" class="btn-primary">Create Free Account</a>
    </div>
    <div class="ft-mock ft-mock-wallet">
        <div class="ft-mock-wallet-amt">₦42,300.00</div>
        <div class="ft-mock-wallet-label">Available balance</div>
        <div class="ft-mock-wallet-row"><span>Gig payout</span><span class="up">+₦8,500</span></div>
        <div class="ft-mock-wallet-row"><span>Referral bonus</span><span class="up">+₦1,200</span></div>
        <div class="ft-mock-wallet-row"><span>Withdrawal</span><span class="down">-₦5,000</span></div>
    </div>
</section>

@guest
<section class="ft-cta reveal-on-scroll">
    <h2>Free to join. Pay only for what you unlock.</h2>
    <p>Browsing Buzz, Pulse, Hustles, and Campus Market is free for everyone. Posting, selling, and earning need an account.</p>
    <a href="{{ route('register') }}" class="btn-primary">Create Free Account</a>
</section>
@endguest

@if($feed->count())
<section class="ft-live reveal-on-scroll">
    <div class="ft-live-head"><h3>Live on NAURVEX right now</h3></div>
    <div class="ft-live-track" id="ftLiveTrack">
        @foreach($feed->take(12) as $item)
        <a href="{{ $item['link'] }}" class="ft-live-card">
            <span class="ft-live-tag">{{ $item['tag'] }}</span>
            <p class="ft-live-title">{{ Str::limit($item['title'], 60) }}</p>
            @if($item['kind'] === 'gig')
            <p class="ft-live-price">₦{{ number_format($item['amount']) }}</p>
            @endif
        </a>
        @endforeach
    </div>
</section>
@endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const track = document.getElementById('ftLiveTrack');
    if (track) track.innerHTML += track.innerHTML;
});
</script>
@endsection
