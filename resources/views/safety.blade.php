@extends('layouts.landing')
@section('content')
<div class="max-w-6xl mx-auto px-3 pt-3">
<div class="hero-poster reveal-on-scroll" style="min-height:auto;">
    <div class="deco-blobs">
        <div class="deco-blob deco-blob-1"></div>
    </div>
    <nav class="flex justify-between items-center px-2 md:px-4 py-2 relative" style="z-index:5;">
        <a href="{{ route('welcome') }}" class="flex items-center space-x-3" style="text-decoration:none;color:inherit;">
            <div class="logo-badge-ring"><div class="logo-badge"></div></div>
            <span class="text-lg font-semibold">Qelvo</span>
        </a>
        <div class="hidden md:flex space-x-8 text-sm hero-poster-muted">
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('news.index') }}">News</a>
            <a href="{{ route('features') }}">Features</a>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="toggleTheme()" class="theme-toggle"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
            <a href="{{ route('login') }}" class="hero-poster-muted text-sm hidden md:inline">Log In</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm px-5 py-2">Get Started</a>
        </div>
    </nav>

    <section class="px-2 md:px-4 py-16 text-center relative" style="z-index:5;">
        <div class="icon-badge mx-auto mb-4"><i data-lucide="shield-check" class="w-6 h-6"></i></div>
        <h1 class="text-2xl md:text-3xl font-semibold">Built to keep your money and data safe</h1>
        <p class="hero-poster-muted mt-4 max-w-2xl mx-auto">
            Every transaction runs through licensed payment partners. We verify identities to keep fraud and scam accounts out of the community. Registration is completely free — you only earn or spend when you choose to.
        </p>
        <div class="flex flex-wrap justify-center gap-4 mt-8">
            <div class="trust-pill"><i data-lucide="lock" class="w-4 h-4"></i> Encrypted payments</div>
            <div class="trust-pill"><i data-lucide="badge-check" class="w-4 h-4"></i> Verified users</div>
            <div class="trust-pill"><i data-lucide="headset" class="w-4 h-4"></i> 24/7 support</div>
        </div>
        <a href="{{ route('register') }}" class="btn-primary mt-10 inline-flex">Get Started <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
    </section>
</div>
</div>
@endsection
