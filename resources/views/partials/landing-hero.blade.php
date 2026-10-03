{{-- ===== HERO ===== --}}
<script>
document.documentElement.classList.add('lpx-pre');
setTimeout(function () { document.documentElement.classList.remove('lpx-pre'); }, 3000);
</script>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <filter id="lpxOutD" x="-15%" y="-15%" width="130%" height="130%" color-interpolation-filters="sRGB">
            <feMorphology in="SourceAlpha" operator="dilate" radius="3" result="o"/>
            <feMorphology in="SourceAlpha" operator="erode" radius="3" result="i"/>
            <feComposite in="o" in2="i" operator="out" result="ring"/>
            <feFlood flood-color="#e3b34a"/>
            <feComposite in2="ring" operator="in"/>
        </filter>
        <filter id="lpxOutM" x="-15%" y="-15%" width="130%" height="130%" color-interpolation-filters="sRGB">
            <feMorphology in="SourceAlpha" operator="dilate" radius="1.7" result="o"/>
            <feMorphology in="SourceAlpha" operator="erode" radius="1.7" result="i"/>
            <feComposite in="o" in2="i" operator="out" result="ring"/>
            <feFlood flood-color="#e3b34a"/>
            <feComposite in2="ring" operator="in"/>
        </filter>
    </defs>
</svg>
<section class="lp-hero lpx-hero" id="lpxHero">
    <div class="lp-hero-bg"></div>
    <div class="lp-hero-scrim"></div>
    <div class="lpx-spline" id="lpxSpline" data-scene=""></div>

    <div class="lp-hero-in lpx-in">
        <span class="lp-hero-side-label" aria-hidden="true">Work &middot; Sell &middot; Earn</span>
        <div class="lpx-word" role="img" aria-label="NOURVEX">
            <span class="lpx-g lpx-l" aria-hidden="true">N</span><span class="lpx-g lpx-e" aria-hidden="true"><span class="lp-hero-o lp-ai-o"><svg viewBox="0 0 100 116" overflow="visible">
                <defs>
                    <radialGradient id="lpEyeSclera" cx="50%" cy="46%" r="62%"><stop offset="0" stop-color="#fdfdfe"/><stop offset=".6" stop-color="#f1f2f7"/><stop offset=".88" stop-color="#d6d9e5"/><stop offset="1" stop-color="#b3b8cb"/></radialGradient>
                    <radialGradient id="lpEyeCorner" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#e8a49a" stop-opacity=".55"/><stop offset="1" stop-color="#e8a49a" stop-opacity="0"/></radialGradient>
                    <radialGradient id="lpEyeIris" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#3b2506"/><stop offset=".45" stop-color="#b5780f"/><stop offset=".8" stop-color="#e3b34a"/><stop offset="1" stop-color="#6e4608"/></radialGradient>
                    <linearGradient id="lpEyeShade" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#000" stop-opacity=".45"/><stop offset="1" stop-color="#000" stop-opacity="0"/></linearGradient>
                    <filter id="lpEyeBlur" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="3.2"/></filter>
                    <filter id="lpEyeBlur2" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="1.6"/></filter>
                    <clipPath id="lpEyeClip"><ellipse cx="50" cy="58" rx="42" ry="50"/></clipPath>
                </defs>
                <circle class="lp-ai-o-pupil lp-hero-eye-pupil" cx="50" cy="61" r=".01" fill="none"/>
                <ellipse cx="50" cy="58" rx="42" ry="50" fill="url(#lpEyeSclera)"/>
                <g clip-path="url(#lpEyeClip)">
                    <ellipse cx="9" cy="62" rx="14" ry="20" fill="url(#lpEyeCorner)"/>
                    <ellipse cx="91" cy="62" rx="14" ry="20" fill="url(#lpEyeCorner)"/>
                    <g fill="none" stroke="#c75b5b" stroke-opacity=".28" stroke-width=".7" stroke-linecap="round">
                        <path d="M10 60 q9 -5 17 1"/><path d="M12 68 q8 -2 15 -6"/><path d="M90 60 q-9 -5 -17 1"/><path d="M88 68 q-8 -2 -15 -6"/>
                    </g>
                    <g class="lp-eye-iris">
                        <circle cx="50" cy="61" r="30" fill="url(#lpEyeIris)"/>
                        <circle cx="50" cy="61" r="22" fill="none" stroke="#ffe29a" stroke-opacity=".35" stroke-width="12" stroke-dasharray="1.1 2.3"/>
                        <circle cx="50" cy="61" r="29" fill="none" stroke="#2a1803" stroke-width="3.2"/>
                        <circle cx="50" cy="61" r="15" fill="#050505"/>
                    </g>
                    <ellipse cx="50" cy="58" rx="42" ry="50" fill="none" stroke="#1a1d2e" stroke-opacity=".5" stroke-width="12" filter="url(#lpEyeBlur)"/>
                    <rect x="0" y="0" width="100" height="62" fill="url(#lpEyeShade)"/>
                    <ellipse cx="38" cy="38" rx="16" ry="9" fill="#ffffff" fill-opacity=".38" filter="url(#lpEyeBlur2)" transform="rotate(-24 38 38)"/>
                    <ellipse cx="41" cy="50" rx="6.5" ry="4.5" fill="#ffffff" fill-opacity=".95" transform="rotate(-28 41 50)"/>
                    <circle cx="60" cy="71" r="2.8" fill="#ffffff" fill-opacity=".7"/>
                    <path d="M22 98 Q50 110 78 98" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="2" filter="url(#lpEyeBlur2)"/>
                </g>
                <ellipse cx="50" cy="58" rx="40.6" ry="48.6" fill="none" stroke="#fff6c9" stroke-opacity=".5" stroke-width="1"/>
                <ellipse cx="50" cy="58" rx="42" ry="50" fill="none" stroke="#e3b34a" stroke-width="3.5"/>
            </svg></span></span><span class="lpx-g lpx-l" aria-hidden="true">U</span><span class="lpx-g lpx-l" aria-hidden="true">R</span><span class="lpx-g lpx-l" aria-hidden="true">V</span><span class="lpx-g lpx-l" aria-hidden="true">E</span><span class="lpx-g lpx-l" aria-hidden="true">X</span>
        </div>
        <div class="lp-hero-social">
            <a href="#" aria-label="Instagram"><i class="ph-bold ph-instagram-logo"></i></a>
            <a href="#" aria-label="X"><i class="ph-bold ph-x-logo"></i></a>
            <a href="#" aria-label="Facebook"><i class="ph-bold ph-facebook-logo"></i></a>
        </div>
    </div>

    <div class="lpx-bottom">
        <div class="lpx-card">
            <div class="lpx-card-top"><span class="lpx-card-label">NOURVEX LTD</span><i data-lucide="shield-check"></i></div>
            <span class="lpx-cut" aria-hidden="true"></span>
            <p class="lpx-card-text">Work, sell and get paid, all in one campus app.</p>
            <p class="lpx-card-sub">Escrow-held payments and PIN-confirmed transfers keep your money safe.</p>
        </div>
        <div class="lpx-cta">
            <a href="{{ route('register') }}" class="lp-hero-cta">Get Started <i data-lucide="arrow-right"></i></a>
        </div>
    </div>

    <a href="#features" class="lpx-scroll" aria-label="Scroll down"><span>Scroll</span><i></i></a>
</section>

<script>
(function () {
    function init() {
        var root = document.documentElement;
        var hero = document.getElementById('lpxHero');
        if (!hero) { root.classList.remove('lpx-pre'); return; }

        var sp = document.getElementById('lpxSpline');
        var scene = sp && sp.getAttribute('data-scene');
        if (scene && window.matchMedia('(min-width: 1024px)').matches) {
            var s = document.createElement('script');
            s.type = 'module';
            s.src = 'https://unpkg.com/@splinetool/viewer/build/spline-viewer.js';
            document.head.appendChild(s);
            var v = document.createElement('spline-viewer');
            v.setAttribute('url', scene);
            sp.appendChild(v);
            sp.classList.add('on');
        }

        if (!window.gsap) { root.classList.remove('lpx-pre'); return; }

        var letters = hero.querySelectorAll('.lpx-g');
        var card = hero.querySelector('.lpx-card');
        var cta = hero.querySelector('.lpx-cta');
        var soc = Array.prototype.slice.call(hero.querySelectorAll('.lp-hero-social a'));

        gsap.fromTo(letters, { y: 60, opacity: 0 }, { y: 0, opacity: 1, duration: 1, ease: 'power4.out', stagger: 0.07, clearProps: 'transform' });
        gsap.fromTo(card, { xPercent: -100, opacity: 0 }, { xPercent: 0, opacity: 1, duration: 0.9, ease: 'power3.out', delay: 0.5, clearProps: 'transform' });
        gsap.fromTo([cta].concat(soc), { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out', stagger: 0.08, delay: 0.8, clearProps: 'transform' });
        root.classList.remove('lpx-pre');

        var iris = hero.querySelector('.lp-eye-iris');
        var eyeEl = hero.querySelector('.lp-hero-o');
        if (!iris || !eyeEl) return;
        var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var MAX = 9, idle = null, back = null;

        function look(x, y, d) {
            gsap.to(iris, { x: x, y: y, duration: d || 0.35, ease: 'power2.out', overwrite: true });
        }
        function wander() {
            if (calm) return;
            idle = gsap.delayedCall(2.2 + Math.random() * 2.2, function () {
                look((Math.random() * 2 - 1) * MAX * 0.8, (Math.random() * 2 - 1) * MAX * 0.5, 0.9);
                wander();
            });
        }
        function track(cx, cy) {
            if (idle) { idle.kill(); idle = null; }
            var r = eyeEl.getBoundingClientRect();
            var dx = cx - (r.left + r.width / 2), dy = cy - (r.top + r.height / 2);
            var d = Math.hypot(dx, dy) || 1, k = Math.min(1, d / 220);
            look(dx / d * MAX * k, dy / d * MAX * k * 0.8, 0.3);
            clearTimeout(back);
            back = setTimeout(wander, 3000);
        }
        window.addEventListener('pointermove', function (e) { track(e.clientX, e.clientY); }, { passive: true });
        function touch(e) { var t = e.touches && e.touches[0]; if (t) track(t.clientX, t.clientY); }
        window.addEventListener('touchstart', touch, { passive: true });
        window.addEventListener('touchmove', touch, { passive: true });
        wander();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
