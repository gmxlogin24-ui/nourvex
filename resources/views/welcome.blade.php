@extends('layouts.landing')
@section('content')

{{-- ===== HERO with roll-up reveal ===== --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

{{-- NOTE: pt-16 assumes a ~64px top navbar. Adjust this value if your nav is taller/shorter or not fixed. --}}
<section class="w-full">
    <div class="relative w-full h-screen sm:h-[600px] lg:h-[760px] xl:h-[860px] overflow-hidden bg-white" id="heroReveal">

        <video autoplay muted loop playsinline
            class="absolute inset-0 w-full h-full object-cover"
            src="{{ asset('videos/hero.mp4') }}">
        </video>

        <div class="absolute inset-x-0 top-6 flex items-center justify-between px-6 md:px-12 z-40">
            <span class="text-white text-sm font-semibold tracking-wide font-hero">CAMPULSY</span>
            <a href="{{ route('login') }}" class="text-white/80 text-xs px-4 py-2 rounded-full border border-white/30">Login / Sign up</a>
        </div>

        <div id="heroSlot" class="absolute z-30 left-1/2 bottom-16 md:bottom-20 -translate-x-1/2 w-[88%] max-w-sm text-center" style="opacity:0; transition: opacity 0.7s ease;">
        </div>

        <div id="heroCover" class="absolute inset-x-0 top-0 bg-white z-50 flex items-center justify-center text-center px-6" style="height:100%;">
            <div id="heroHeadlineWrap" style="opacity:0; transition: opacity 0.8s ease;">
                <p class="text-gray-400 text-xs tracking-widest mb-2 font-hero">CAMPULSY</p>
                <h1 class="text-gray-900 text-2xl md:text-4xl font-bold leading-tight max-w-lg font-hero">Work, sell, get paid.<br>All in one place.</h1>
            </div>
        </div>

    </div>
</section>

<style>
.font-hero { font-family: 'Sora', sans-serif; }
#heroCover { transition: height 1.6s cubic-bezier(0.65, 0, 0.35, 1); }
#heroSlot .slot-card {
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 1rem;
    padding: 1.1rem;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cover = document.getElementById('heroCover');
    const headlineWrap = document.getElementById('heroHeadlineWrap');
    const heroReveal = document.getElementById('heroReveal');
    const slot = document.getElementById('heroSlot');

    const COVER_STOP = 30; // percent
    let coverPercent = 100;
    let locked = false;
    let unlocked = false;
    let touchStartY = null;

    const slides = [
        '<div class="slot-card font-hero"><p class="text-[11px] text-white/60 mb-2">Campus Snapshot</p><div class="grid grid-cols-2 gap-2"><div class="bg-white/10 rounded-xl p-2"><p class="text-[10px] text-white/60">Active Gigs</p><p class="text-xl font-bold">128+</p></div><div class="bg-white/10 rounded-xl p-2"><p class="text-[10px] text-white/60">Listings</p><p class="text-xl font-bold">340+</p></div></div></div>',
        '<div class="font-hero"><h2 class="text-2xl md:text-3xl font-bold text-white leading-tight">Real gigs.<br>Real money.</h2></div>',
        '<div class="slot-card font-hero"><p class="text-[11px] text-white/60 mb-1">Wallet Payout</p><p class="text-lg font-bold">₦20,000.50 <span class="text-xs text-white/50 font-normal">available now</span></p></div>',
        '<div class="font-hero"><h2 class="text-2xl md:text-3xl font-bold text-white leading-tight">All in one place.</h2></div>'
    ];
    let slideIndex = 0;

    function showSlide(i) {
        slot.style.opacity = '0';
        setTimeout(function () {
            slot.innerHTML = slides[i];
            slot.style.opacity = '1';
        }, 400);
    }

    function startCycle() {
        showSlide(slideIndex);
        setInterval(function () {
            slideIndex = (slideIndex + 1) % slides.length;
            showSlide(slideIndex);
        }, 3200);
    }

    function setCover(pct) {
        coverPercent = Math.max(0, Math.min(100, pct));
        cover.style.transition = 'none';
        cover.style.height = coverPercent + '%';
    }

    function finishInitialRollUp() {
        headlineWrap.style.opacity = '1';
        setTimeout(startCycle, 800);
        locked = true;
        document.body.style.overflow = 'hidden';
    }

    setTimeout(function () {
        cover.style.transition = 'height 1.6s cubic-bezier(0.65,0,0.35,1)';
        cover.style.height = COVER_STOP + '%';
        coverPercent = COVER_STOP;
        setTimeout(finishInitialRollUp, 1650);
    }, 500);

    function releaseLock() {
        if (!unlocked) {
            unlocked = true;
            locked = false;
            document.body.style.overflow = '';
            document.body.classList.add('sn-over-video');
        }
    }

    function shrinkFromLock(delta) {
        if (!locked) return;
        const pct = coverPercent - delta;
        setCover(pct);
        if (coverPercent <= 0) {
            releaseLock();
        }
    }

    window.addEventListener('wheel', function (e) {
        if (locked) {
            e.preventDefault();
            shrinkFromLock(e.deltaY * 0.08);
        }
    }, { passive: false });

    window.addEventListener('touchstart', function (e) {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    window.addEventListener('touchmove', function (e) {
        if (locked) {
            e.preventDefault();
            const currentY = e.touches[0].clientY;
            const delta = (touchStartY - currentY) * 0.3;
            touchStartY = currentY;
            shrinkFromLock(delta);
        }
    }, { passive: false });
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
