{{-- ===== HOW IT WORKS: stacked slanted cards ===== --}}
<section class="lp-st" id="lpSteps" aria-label="How it works">
    <div class="lp-st-in">
        <div class="lp-st-intro">
            <p class="lp-eyebrow" style="margin-bottom:12px">How it works</p>
            <h2 class="lp-h2" style="margin:0">From sign-up to <span class="lp-grad">paid</span> in five steps.</h2>
            <p class="lp-lead lp-st-lead" style="margin-top:16px">No long forms and no juggling apps. This is exactly how NAURVEX works, start to finish.</p>
            <div class="lp-st-count"><span id="lpStNum">01</span><small>/ 05</small></div>
            <div class="lp-st-bar" aria-hidden="true"><i></i></div>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary lp-st-introbtn" style="margin-top:28px">Create your free account <i data-lucide="arrow-right"></i></a>
        </div>

        <div class="lp-st-stack">
            <article class="lp-st-card" style="--t:99,102,241">
                <div class="lp-st-head"><span class="lp-st-n">01</span><h3>Join in minutes</h3><i data-lucide="user-plus"></i></div>
                <div class="lp-st-body">
                    <p>Create a free account, verify your details and set a PIN. That PIN guards everything that moves money.</p>
                    <ul class="lp-st-pts"><li>Free to join</li><li>Your own secure PIN</li><li>One account for everything</li></ul>
                </div>
            </article>

            <article class="lp-st-card" style="--t:20,184,166">
                <div class="lp-st-head"><span class="lp-st-n">02</span><h3>Fund your wallet</h3><i data-lucide="wallet"></i></div>
                <div class="lp-st-body">
                    <p>Top up by card or transfer and see it land instantly. One balance runs hustles, the market, airtime and data.</p>
                    <ul class="lp-st-pts"><li>Card or bank transfer</li><li>Live credit and debit tracking</li><li>PIN-confirmed spending</li></ul>
                </div>
            </article>

            <article class="lp-st-card" style="--t:227,179,74">
                <div class="lp-st-head"><span class="lp-st-n">03</span><h3>Find or post a hustle</h3><i data-lucide="briefcase"></i></div>
                <div class="lp-st-body">
                    <p>Post a task with your budget, or apply to open ones. Once it's accepted, the money goes into escrow and waits.</p>
                    <ul class="lp-st-pts"><li>You set the budget</li><li>Browse and apply in a tap</li><li>Escrow protects both sides</li></ul>
                </div>
            </article>

            <article class="lp-st-card" style="--t:236,72,153">
                <div class="lp-st-head"><span class="lp-st-n">04</span><h3>Deliver and get paid</h3><i data-lucide="shield-check"></i></div>
                <div class="lp-st-body">
                    <p>When the poster confirms delivery, escrow releases to your wallet. Campus Market buyers confirm the same way.</p>
                    <ul class="lp-st-pts"><li>No chasing payments</li><li>Support is one tap away</li><li>Same protection on the market</li></ul>
                </div>
            </article>

            <article class="lp-st-card" style="--t:168,85,247">
                <div class="lp-st-head"><span class="lp-st-n">05</span><h3>Level up</h3><i data-lucide="trophy"></i></div>
                <div class="lp-st-body">
                    <p>Every finished hustle and good review raises your level and earns a badge people can see. Ask Levi, your AI concierge, for help anytime.</p>
                    <ul class="lp-st-pts"><li>Badges buyers can see</li><li>Airtime and data from your wallet</li><li>Levi answers in plain chat</li></ul>
                    <a href="{{ route('register') }}" class="lp-st-cta">Create your free account <i data-lucide="arrow-right"></i></a>
                </div>
            </article>
        </div>
    </div>
</section>

<script>
(function () {
    var sec = document.getElementById('lpSteps');
    if (!sec || !window.gsap || !window.ScrollTrigger) return;
    gsap.registerPlugin(ScrollTrigger);

    var cards = gsap.utils.toArray('#lpSteps .lp-st-card');
    var heads = cards.map(function (c) { return c.querySelector('.lp-st-head'); });
    var bodies = cards.map(function (c) { return c.querySelector('.lp-st-body'); });
    var intro = sec.querySelector('.lp-st-intro');
    var base = [-3.2, 2.6, -2.2, 3, -2.6];
    var num = document.getElementById('lpStNum');
    var bar = sec.querySelector('.lp-st-bar i');
    var OV = 12;

    gsap.matchMedia().add({
        desk: '(min-width: 901px) and (min-height: 640px)',
        mob: '(max-width: 900px), (max-height: 639px)'
    }, function (ctx) {
        var mob = !!ctx.conditions.mob;
        var CLOSED = mob ? 58 : 60;
        var rots = base.map(function (r) { return mob ? r * 0.5 : r; });

        function openH() {
            if (!mob) return Math.round(Math.min(340, Math.max(270, window.innerHeight * 0.38)));
            var avail = window.innerHeight - intro.offsetHeight - 100;
            return Math.round(Math.max(250, avail - (cards.length - 1) * (CLOSED - OV)));
        }

        sec.classList.add('lp-st-on');
        heads.forEach(function (h) { h.style.height = CLOSED + 'px'; });
        cards.forEach(function (c, i) {
            gsap.set(c, { height: i === 0 ? openH() : CLOSED, rotation: i === 0 ? 0 : rots[i], zIndex: i + 1 });
            gsap.set(bodies[i], { opacity: i === 0 ? 1 : 0 });
        });

        var tl = gsap.timeline({
            defaults: { ease: 'none' },
            scrollTrigger: {
                trigger: sec,
                start: 'top top',
                end: function () { return '+=' + Math.round((cards.length - 1) * window.innerHeight * (mob ? 0.7 : 0.85)); },
                pin: true,
                scrub: 0.6,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                onUpdate: function (self) {
                    var best = 0, max = 0;
                    cards.forEach(function (c, i) {
                        var h = gsap.getProperty(c, 'height');
                        if (h > max) { max = h; best = i; }
                    });
                    num.textContent = '0' + (best + 1);
                    bar.style.transform = 'scaleX(' + (0.2 + 0.8 * self.progress) + ')';
                }
            }
        });

        for (var i = 0; i < cards.length - 1; i++) {
            var p = i * 1.5;
            tl.to(cards[i], { height: CLOSED, rotation: rots[i], duration: 1 }, p);
            tl.to(bodies[i], { opacity: 0, duration: 0.5 }, p);
            tl.to(cards[i + 1], { height: openH, rotation: 0, duration: 1 }, p);
            tl.to(bodies[i + 1], { opacity: 1, duration: 0.5 }, p + 0.5);
        }
        tl.to({}, { duration: 0.5 });

        return function () {
            sec.classList.remove('lp-st-on');
            heads.forEach(function (h) { h.style.height = ''; });
            gsap.set(cards, { clearProps: 'height,transform,zIndex' });
            gsap.set(bodies, { clearProps: 'opacity' });
        };
    });

    window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
</script>
