@extends('layouts.landing')
@section('content')

{{-- ===== HERO with roll-up reveal ===== --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

<section class="w-full px-0 pt-0">
    <div class="relative w-full h-[520px] sm:h-[600px] lg:h-[760px] xl:h-[860px] overflow-hidden bg-white" id="heroReveal">

        <video autoplay muted loop playsinline
            class="absolute inset-0 w-full h-full object-cover"
            src="{{ asset('videos/hero.mp4') }}">
        </video>

        <div class="absolute inset-x-0 top-6 flex items-center justify-between px-6 md:px-12 z-40">
            <span class="text-white text-sm font-semibold tracking-wide font-hero">CAMPULSY</span>
            <a href="{{ route('login') }}" class="text-white/80 text-xs px-4 py-2 rounded-full border border-white/30">Login / Sign up</a>
        </div>

        <div id="heroCenterCard" class="hero-anim-card absolute z-30 bg-black/35 backdrop-blur-md border border-white/15 rounded-2xl p-5 text-white" style="top:50%; left:50%; width:min(90%,420px); transform:translate(-50%,-50%) translateY(20px);">
            <p class="text-sm font-semibold mb-3 font-hero">Campus Snapshot</p>
            <div class="grid grid-cols-2 gap-2 mb-2">
                <div class="bg-white/10 rounded-xl p-3">
                    <p class="text-[11px] text-white/60 mb-1">Active Gigs</p>
                    <p class="text-2xl font-bold font-hero">128+</p>
                </div>
                <div class="bg-white/10 rounded-xl p-3">
                    <p class="text-[11px] text-white/60 mb-1">Listings</p>
                    <p class="text-2xl font-bold font-hero">340+</p>
                </div>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <p class="text-[11px] text-white/60 mb-1">Wallet Payout</p>
                <p class="text-lg font-bold font-hero">₦20,000.50 <span class="text-xs text-white/50 font-normal">available now</span></p>
            </div>
        </div>

        <div id="heroSideLeft" class="hero-anim-card absolute z-30 text-white/50 text-sm max-w-[160px] leading-snug" style="top:38%; left:6%; transform:translateY(20px);">
            Built for students hustling on campus, not idle browsing.
        </div>

        <div id="heroSideRight" class="hero-anim-card absolute z-30 text-white/50 text-sm max-w-[160px] leading-snug text-right" style="top:58%; right:6%; transform:translateY(20px);">
            Every gig, sale, and payout tracked in real time.
        </div>

        <div id="heroBottomText" class="hero-anim-card absolute z-30 left-6 md:left-12 bottom-10 md:bottom-14 text-white" style="transform:translateY(20px);">
            <h2 class="text-2xl md:text-4xl font-bold leading-tight font-hero">Real gigs. Real money.<br>All in one place.</h2>
        </div>

        <div id="heroCover" class="absolute inset-x-0 top-0 bg-white z-50 flex items-center justify-center text-center px-6" style="height:100%;">
            <div id="heroHeadlineWrap" style="opacity:0;">
                <p class="text-gray-400 text-xs tracking-widest mb-2 font-hero">CAMPULSY</p>
                <h1 class="text-gray-900 text-2xl md:text-4xl font-bold leading-tight max-w-lg font-hero">Work, sell, get paid.<br>All in one place.</h1>
            </div>
        </div>

    </div>
</section>

<style>
.font-hero { font-family: 'Sora', sans-serif; }

#heroCover {
    transition: height 1.6s cubic-bezier(0.65, 0, 0.35, 1);
}
#heroHeadlineWrap {
    transition: opacity 0.8s ease;
}
.hero-anim-card {
    opacity: 0;
    transition: opacity 0.9s ease, transform 0.9s ease;
}
.hero-anim-card.show {
    opacity: 1;
    transform: translate(-50%,-50%) translateY(0) !important;
}
#heroSideLeft.show, #heroSideRight.show, #heroBottomText.show {
    opacity: 0.85;
    transform: translateY(0) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cover = document.getElementById('heroCover');
    const headlineWrap = document.getElementById('heroHeadlineWrap');
    const centerCard = document.getElementById('heroCenterCard');
    const sideLeft = document.getElementById('heroSideLeft');
    const sideRight = document.getElementById('heroSideRight');
    const bottomText = document.getElementById('heroBottomText');
    const heroReveal = document.getElementById('heroReveal');

    let scrollTakeover = false;

    setTimeout(function () {
        cover.style.height = '20%';
    }, 500);

    cover.addEventListener('transitionend', function (e) {
        if (e.propertyName === 'height' && !scrollTakeover) {
            headlineWrap.style.opacity = '1';
            setTimeout(function () { centerCard.classList.add('show'); }, 500);
            setTimeout(function () { sideLeft.classList.add('show'); }, 1300);
            setTimeout(function () { sideRight.classList.add('show'); }, 2000);
            setTimeout(function () { bottomText.classList.add('show'); }, 2700);
        }
    });

    window.addEventListener('scroll', function () {
        const rect = heroReveal.getBoundingClientRect();
        const scrolled = Math.min(Math.max(-rect.top, 0), 300);
        if (scrolled > 0) {
            scrollTakeover = true;
            const pct = 20 - (scrolled / 300) * 20;
            cover.style.transition = 'none';
            cover.style.height = Math.max(pct, 0) + '%';
        }
    });
});
</script>

{{-- ===== Pillars ===== --}}
<section class="max-w-6xl mx-auto px-4 py-20">
    <div class="text-center mb-12">
        <p class="text-indigo-600 text-sm font-semibold mb-2">The platform</p>
        <h2 class="text-3xl font-bold mb-3">Everything campus life runs on, in one app.</h2>
        <p class="text-gray-500">Buy, sell, get paid, and stay in the loop — no juggling five different apps.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="border rounded-2xl p-6">
            <i data-lucide="shopping-bag" class="w-6 h-6 text-indigo-600 mb-3"></i>
            <p class="text-2xl font-bold">{{ $listingCount ?? 340 }}+</p>
            <p class="font-semibold mb-1">Marketplace</p>
            <p class="text-sm text-gray-500 mb-3">Sell items, services, and rentals directly to other students.</p>
            <a href="{{ route('marketplace.index') }}" class="text-indigo-600 text-sm font-medium">Browse listings →</a>
        </div>
        <div class="border rounded-2xl p-6">
            <i data-lucide="briefcase" class="w-6 h-6 text-indigo-600 mb-3"></i>
            <p class="text-2xl font-bold">{{ $gigCount ?? 128 }}+</p>
            <p class="font-semibold mb-1">Gigs</p>
            <p class="text-sm text-gray-500 mb-3">Post a task or pick one up — funds are held safely until it's done.</p>
            <a href="{{ route('gigs.index') }}" class="text-indigo-600 text-sm font-medium">See open gigs →</a>
        </div>
        <div class="border rounded-2xl p-6">
            <i data-lucide="activity" class="w-6 h-6 text-indigo-600 mb-3"></i>
            <p class="text-2xl font-bold">12+</p>
            <p class="font-semibold mb-1">Pulse</p>
            <p class="text-sm text-gray-500 mb-3">Campus news, buzz, and community updates in one feed.</p>
            <a href="{{ route('pulse.index') }}" class="text-indigo-600 text-sm font-medium">Check the feed →</a>
        </div>
        <div class="border rounded-2xl p-6">
            <i data-lucide="wallet" class="w-6 h-6 text-indigo-600 mb-3"></i>
            <p class="text-2xl font-bold">2 min</p>
            <p class="font-semibold mb-1">Wallet</p>
            <p class="text-sm text-gray-500 mb-3">Top up, get paid, and track every transaction in real time.</p>
            <a href="{{ route('register') }}" class="text-indigo-600 text-sm font-medium">Get started →</a>
        </div>
    </div>
</section>

{{-- ===== Pricing ===== --}}
<section class="max-w-6xl mx-auto px-4 py-20">
    <div class="text-center mb-12">
        <p class="text-indigo-600 text-sm font-semibold mb-2">Pricing</p>
        <h2 class="text-3xl font-bold mb-3">Pick your plan.</h2>
        <p class="text-gray-500">Start free. Upgrade when you're ready to sell more.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="border rounded-2xl p-8">
            <p class="text-sm text-gray-500 mb-1">Active sellers</p>
            <p class="text-xl font-bold mb-2">Pro</p>
            <p class="text-3xl font-bold mb-4">&#8358;5,000 <span class="text-sm font-normal text-gray-500">/ year</span></p>
            <ul class="text-sm space-y-2 mb-6 text-gray-600">
                <li>✓ Everything in Free</li>
                <li>✓ Post gigs & listings</li>
                <li>✓ 10% referral bonus</li>
            </ul>
            <a href="{{ route('register') }}" class="block text-center py-3 rounded-full border font-semibold">Get Started</a>
        </div>
        <div class="border-2 border-indigo-600 rounded-2xl p-8 relative">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs px-3 py-1 rounded-full">Best Value</span>
            <p class="text-sm text-gray-500 mb-1">Power sellers</p>
            <p class="text-xl font-bold mb-2">Gold</p>
            <p class="text-3xl font-bold mb-4">&#8358;10,000 <span class="text-sm font-normal text-gray-500">/ year</span></p>
            <ul class="text-sm space-y-2 mb-6 text-gray-600">
                <li>✓ Everything in Pro</li>
                <li>✓ Verified badge</li>
                <li>✓ Featured placement</li>
                <li>✓ 15% referral bonus</li>
            </ul>
            <a href="{{ route('register') }}" class="block text-center py-3 rounded-full bg-indigo-600 text-white font-semibold">Get Started</a>
        </div>
        <div class="border rounded-2xl p-8">
            <p class="text-sm text-gray-500 mb-1">Top tier</p>
            <p class="text-xl font-bold mb-2">Premium</p>
            <p class="text-3xl font-bold mb-4">&#8358;15,000 <span class="text-sm font-normal text-gray-500">/ year</span></p>
            <ul class="text-sm space-y-2 mb-6 text-gray-600">
                <li>✓ Everything in Gold</li>
                <li>✓ Unlimited active posts</li>
                <li>✓ Priority support</li>
            </ul>
            <a href="{{ route('register') }}" class="block text-center py-3 rounded-full border font-semibold">Get Started</a>
        </div>
    </div>
</section>

@endsection
