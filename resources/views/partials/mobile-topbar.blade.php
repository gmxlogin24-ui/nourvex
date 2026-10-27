{{-- ===== MOBILE TOP BAR (phones only, every page on layouts.landing). Burger opens the OLD drawer. ===== --}}
<header class="lpxb-bar" id="lpxbBar">
    <a href="{{ route('welcome') }}" class="lpxb-logo">
        <div class="logo-badge" style="width:1.9rem;height:1.9rem;"></div>
        <span>NAURVEX</span>
    </a>
    <button type="button" class="lpxb-burger" id="lpxbBurger" aria-label="Menu"><i></i><i></i></button>
</header>
<script>
(function () {
    var bar = document.getElementById('lpxbBar');
    var burger = document.getElementById('lpxbBurger');
    if (!bar || !burger) return;
    function onScroll() { bar.classList.toggle('scrolled', window.scrollY > 40); }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    burger.addEventListener('click', function () {
        var old = document.getElementById('snBurger');
        if (old) old.click();
    });
})();
</script>
