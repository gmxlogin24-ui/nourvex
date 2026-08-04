<nav class="sn-bar" id="snBar">
    <div class="max-w-6xl mx-auto flex justify-between items-center px-4 py-3">
        <a href="{{ route('welcome') }}" class="sn-logo">
            <div class="logo-badge" style="width:2rem;height:2rem;"></div>
            <span>Campulsy</span>
        </a>

        <div class="sn-links-desktop">
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('gigs.index') }}">Gigs</a>
            <a href="{{ route('marketplace.index') }}">Marketplace</a>
            <a href="{{ route('pulse.index') }}">Pulse</a>
            <a href="{{ route('features') }}">Features</a>
            <a href="{{ route('login') }}">Log In</a>
            <a href="{{ route('register') }}" class="sn-cta-pill">Get Started</a>
        </div>

        <button class="sn-burger" id="snBurger" aria-label="Menu" type="button">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="sn-backdrop" id="snBackdrop"></div>
<aside class="sn-drawer" id="snDrawer">
    <div class="sn-drawer-brand">
        <div class="logo-badge"></div>
        <span>Campulsy</span>
    </div>
    <div class="sn-reveal-heading">Menu</div>
    <nav class="sn-item-list">
        <a href="{{ route('welcome') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="home" class="w-4 h-4"></i></span><span>Home</span></a>
        <a href="{{ route('gigs.index') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span><span>Gigs</span></a>
        <a href="{{ route('marketplace.index') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span><span>Marketplace</span></a>
        <a href="{{ route('pulse.index') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="activity" class="w-4 h-4"></i></span><span>Pulse</span></a>
        <a href="{{ route('features') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="sparkles" class="w-4 h-4"></i></span><span>Features</span></a>
    </nav>
    <div class="sn-divider"></div>
    <a href="{{ route('login') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="log-in" class="w-4 h-4"></i></span><span>Log In</span></a>
    <a href="{{ route('register') }}" class="sn-cta"><i data-lucide="user-plus" class="w-4 h-4"></i> Get Started</a>
    <button type="button" class="sn-item" id="snThemeBtn" style="margin-top:auto;"><span class="sn-item-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span><span>Theme</span></button>
</aside>

<script src="{{ asset('js/site-nav.js') }}?v={{ time() }}"></script>
