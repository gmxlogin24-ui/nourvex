<nav class="sn-bar" id="snBar">
    <div class="sn-bar-inner">
        <a href="{{ route('welcome') }}" class="sn-logo">
            <div class="logo-badge" style="width:2rem;height:2rem;"></div>
            <span>NAURVEX</span>
        </a>

        <div class="sn-links-center">
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('pulse.index') }}">Pulse</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('faq') }}">FAQ</a>
        </div>

        <div class="sn-actions">
            <a href="{{ route('login') }}" class="sn-login-btn">Log In</a>
            <a href="{{ route('register') }}" class="sn-cta-pill">Get Started</a>
            <button class="sn-burger" id="snBurger" aria-label="Menu" type="button">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<div class="sn-backdrop" id="snBackdrop"></div>
<aside class="sn-drawer" id="snDrawer">
    <div class="sn-drawer-brand">
        <div class="logo-badge"></div>
        <span>NAURVEX</span>
    </div>
    <div class="sn-reveal-heading">Menu</div>
    <nav class="sn-item-list">
        <a href="{{ route('welcome') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="home" class="w-4 h-4"></i></span><span>Home</span></a>
        <a href="{{ route('pulse.index') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="activity" class="w-4 h-4"></i></span><span>Pulse</span></a>
        <a href="{{ route('about') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="info" class="w-4 h-4"></i></span><span>About</span></a>
        <a href="{{ route('faq') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="message-circle" class="w-4 h-4"></i></span><span>FAQ</span></a>
    </nav>
    <div class="sn-divider"></div>
    <a href="{{ route('login') }}" class="sn-item"><span class="sn-item-icon"><i data-lucide="log-in" class="w-4 h-4"></i></span><span>Log In</span></a>
    <a href="{{ route('register') }}" class="sn-cta"><i data-lucide="user-plus" class="w-4 h-4"></i> Get Started</a>
    <button type="button" class="sn-item" id="snThemeBtn" style="margin-top:auto;"><span class="sn-item-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span><span>Theme</span></button>
</aside>

<script src="{{ asset('js/site-nav.js') }}?v={{ time() }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bar = document.getElementById('snBar');
    function onScroll() {
        if (window.scrollY > 80) {
            bar.classList.add('sn-scrolled');
        } else {
            bar.classList.remove('sn-scrolled');
        }
    }
    window.addEventListener('scroll', onScroll);
    onScroll();
});
</script>
