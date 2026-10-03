{{-- ===== MOBILE TOP BAR + HERO (phones only) ===== --}}
<header class="lpxm-bar" id="lpxmBar">
    <a href="{{ url('/') }}" class="lpxm-logo">NOURVEX</a>
    <button type="button" class="lpxm-burger" id="lpxmBurger" aria-label="Menu" aria-expanded="false"><i></i><i></i></button>
</header>

<div class="lpxm-menu" id="lpxmMenu" aria-hidden="true">
    <nav>
        <a href="#features">Features</a>
        <a href="#lpSteps">How it works</a>
        <a href="#lpPlans">Plans</a>
        <a href="{{ \Route::has('login') ? route('login') : url('/login') }}">Log in</a>
    </nav>
    <a href="{{ route('register') }}" class="lpxm-menu-cta">Get Started</a>
</div>

<section class="lpxm-hero" id="lpxmHero">
    <div class="lpxm-bg"></div>
    <div class="lpxm-scrim"></div>
    <div class="lpxm-copy">
        <div class="lpxm-meta"><span>NOURVEX LTD</span><i></i><span>Made for campus</span></div>
        <h1 class="lpxm-h">Work. Sell.<br>Get paid.</h1>
        <p class="lpxm-sub"><b>All in one campus app.</b>Escrow-held payments and PIN-confirmed transfers keep your money safe.</p>
        <div class="lpxm-actions">
            <a href="{{ route('register') }}" class="lpxm-btn">Get Started</a>
            <a href="#lpSteps" class="lpxm-link"><i></i>See how it works</a>
        </div>
    </div>
    <a href="#features" class="lpxm-scroll" aria-label="Scroll down"><span>Scroll</span><i></i></a>
</section>

<script>
(function () {
    var root = document.documentElement;
    var bar = document.getElementById('lpxmBar');
    var burger = document.getElementById('lpxmBurger');
    var menu = document.getElementById('lpxmMenu');
    if (!bar || !burger || !menu) return;

    function setOpen(o) {
        root.classList.toggle('lpxm-open', o);
        burger.setAttribute('aria-expanded', o ? 'true' : 'false');
        menu.setAttribute('aria-hidden', o ? 'false' : 'true');
    }
    burger.addEventListener('click', function () { setOpen(!root.classList.contains('lpxm-open')); });
    menu.addEventListener('click', function (e) { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

    function onScroll() { bar.classList.toggle('scrolled', window.scrollY > 40); }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    function hideOld() {
        var vw = window.innerWidth, sy = window.scrollY;
        var list = document.body.querySelectorAll('*');
        for (var k = 0; k < list.length; k++) {
            var el = list[k];
            var tag = el.tagName;
            if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'A' || tag === 'MAIN' || tag === 'SECTION' || tag === 'svg' || tag === 'path') continue;
            if (el.closest('#lpxmBar, #lpxmMenu, #lpxmHero, #lpxHero, .lpxm-oldnav')) continue;
            var cs = getComputedStyle(el);
            var pos = cs.position;
            if (cs.display === 'none') continue;
            if (pos === 'static' && tag !== 'HEADER' && tag !== 'NAV') continue;
            var r = el.getBoundingClientRect();
            var top = (pos === 'fixed' || pos === 'sticky') ? r.top : r.top + sy;
            if (top > -2 && top < 48 && r.width >= vw * 0.5 && r.height >= 28 && r.height <= 150 && el.querySelectorAll('a, button').length >= 2) {
                el.classList.add('lpxm-oldnav');
            }
        }
    }
    hideOld();
    window.addEventListener('load', hideOld);
    setTimeout(hideOld, 400);
    setTimeout(hideOld, 1200);
    setTimeout(hideOld, 2500);
})();
</script>
