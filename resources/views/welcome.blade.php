@extends('layouts.landing')
@section('content')

{{-- ===== HERO with roll-up reveal ===== --}}
<section class="max-w-6xl mx-auto px-4 pt-10 pb-4">
    <div class="relative w-full h-[420px] md:h-[600px] rounded-3xl overflow-hidden bg-white shadow-xl" id="heroReveal">

        <video autoplay muted loop playsinline
            class="absolute inset-0 w-full h-full object-cover"
            src="{{ asset('videos/hero.mp4') }}">
        </video>

        <div id="heroCover" class="absolute inset-x-0 top-0 bg-white z-30 flex flex-col items-center justify-center text-center px-6" style="height:100%;">
            <p class="text-gray-400 text-xs tracking-widest mb-2" id="heroEyebrow">CAMPULSY</p>
            <h1 class="text-gray-900 text-2xl md:text-4xl font-bold leading-tight max-w-lg" id="heroHeadline">Work, sell, get paid.<br>All in one place.</h1>
        </div>

        <div id="heroCard1" class="hero-anim-card absolute bg-white/95 backdrop-blur shadow-xl rounded-2xl p-4 text-sm z-20" style="top:22%; left:8%;">
            <p class="text-gray-400 text-xs">Active gigs</p>
            <p class="font-semibold text-indigo-600 text-lg">128+</p>
        </div>
        <div id="heroCard2" class="hero-anim-card absolute bg-white/95 backdrop-blur shadow-xl rounded-2xl p-4 text-sm z-20" style="top:55%; right:8%;">
            <p class="text-gray-400 text-xs">This month</p>
            <p class="font-semibold text-lg">+₦8,400</p>
        </div>
        <div id="heroCard3" class="hero-anim-card absolute bg-white/95 backdrop-blur shadow-xl rounded-2xl p-4 text-sm z-20" style="bottom:10%; left:10%;">
            <p class="text-gray-400 text-xs">Wallet</p>
            <a href="{{ route('register') }}" class="font-semibold text-gray-900">Get Started →</a>
        </div>

    </div>
</section>

<style>
#heroCover {
    transition: height 1.3s cubic-bezier(0.65, 0, 0.35, 1);
}
#heroHeadline {
    transition: font-size 1.3s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.6s ease;
}
.hero-anim-card {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}
.hero-anim-card.show {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cover = document.getElementById('heroCover');
    const headline = document.getElementById('heroHeadline');
    const card1 = document.getElementById('heroCard1');
    const card2 = document.getElementById('heroCard2');
    const card3 = document.getElementById('heroCard3');

    setTimeout(function () {
        cover.style.height = '20%';
        headline.style.fontSize = '';
    }, 400);

    setTimeout(function () { card1.classList.add('show'); }, 1600);
    setTimeout(function () { card2.classList.add('show'); }, 1900);
    setTimeout(function () { card3.classList.add('show'); }, 2200);
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
