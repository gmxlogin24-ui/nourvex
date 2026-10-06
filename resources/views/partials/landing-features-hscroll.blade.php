{{-- ===== FEATURES: GSAP horizontal scroll ===== --}}
<section class="lp-h" id="features">
    <div class="lp-h-top">
        <div>
            <p class="lp-eyebrow" style="margin-bottom:12px">The platform</p>
            <h2 class="lp-h2" style="margin:0">Everything campus life <span class="lp-grad">runs on.</span></h2>
        </div>
        <p class="lp-lead lp-h-lead">Buy, sell, get paid and stay in the loop, without juggling five different apps.</p>
    </div>

    <div class="lp-h-track" id="lpHTrack">

        <article class="lp-h-card lp-h-video">
            <div class="lp-h-video-wrap" aria-hidden="true"><video autoplay muted loop playsinline preload="none"><source src="https://videos.pexels.com/video-files/30592298/13100006_1920_1080_24fps.mp4" type="video/mp4"></video></div>
            <span class="lp-h-num">01</span>
            <div class="lp-visual" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none">
                <rect class="lp-draw" x="10" y="22" width="44" height="28" rx="6" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M24 22v-6a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v6" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M22 36l7 7 13-15" stroke="currentColor" stroke-width="3"/>
            </svg></div>
            <h3>Hustles</h3>
            <p>Post a task or pick one up. Pay is held safely until the work is done.</p>
            
            <a href="{{ route('gigs.index') }}" class="lp-link">See open hustles <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card lp-c-silver lp-h-photo" style="--card-photo:url('https://images.unsplash.com/photo-1681825984459-47ee999da245?auto=format&fit=crop&w=1400&q=65')">
            <span class="lp-h-num">02</span>
            <div class="lp-visual" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none">
                <rect class="lp-draw" x="8" y="16" width="48" height="34" rx="8" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M8 26h48" stroke="currentColor" stroke-width="3"/>
                <circle class="lp-draw" cx="44" cy="38" r="4" stroke="currentColor" stroke-width="3"/>
            </svg></div>
            <h3>Wallet</h3>
            <p>One balance for everything. Top up, spend and track it all in one place.</p>
            
            <a href="{{ route('register') }}" class="lp-link">Open a wallet <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card lp-c-silver lp-h-video">
            <div class="lp-h-video-wrap" aria-hidden="true"><video autoplay muted loop playsinline preload="none"><source src="https://videos.pexels.com/video-files/5924986/5924986-uhd_1440_2560_24fps.mp4" type="video/mp4"></video></div>
            <span class="lp-h-num">03</span>
            <div class="lp-visual" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none">
                <path class="lp-draw" d="M14 22h36l-3 30a4 4 0 0 1-4 4H21a4 4 0 0 1-4-4l-3-30z" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M22 22v-4a10 10 0 0 1 20 0v4" stroke="currentColor" stroke-width="3"/>
            </svg></div>
            <h3>Campus Market</h3>
            <p>Buy and sell textbooks, gadgets and services with other students.</p>
            
            <a href="{{ route('marketplace.index') }}" class="lp-link">Browse listings <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card lp-h-video">
            <div class="lp-h-video-wrap" aria-hidden="true"><video autoplay muted loop playsinline preload="none"><source src="https://videos.pexels.com/video-files/5385436/5385436-uhd_2732_1440_30fps.mp4" type="video/mp4"></video></div>
            <span class="lp-h-num">04</span>
            <div class="lp-visual" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none">
                <path class="lp-draw" d="M10 16h44a4 4 0 0 1 4 4v20a4 4 0 0 1-4 4H30l-10 10V44H10a4 4 0 0 1-4-4V20a4 4 0 0 1 4-4z" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M12 32h8l4-9 6 16 4-11 4 4h10" stroke="currentColor" stroke-width="2.6"/>
            </svg></div>
            <h3>Pulse</h3>
            <p>Live campus news, updates and buzz in one feed.</p>
            
            <a href="{{ route('pulse.index') }}" class="lp-link">Check the feed <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card lp-c-silver">
            <span class="lp-h-num">05</span>
            <div class="lp-visual" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none">
                <rect class="lp-draw" x="20" y="8" width="24" height="48" rx="5" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M28 48h8" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M14 26a26 26 0 0 1 8-8" stroke="currentColor" stroke-width="2.6"/>
                <path class="lp-draw" d="M10 20a34 34 0 0 1 10-11" stroke="currentColor" stroke-width="2.2"/>
            </svg></div>
            <h3>VTU services</h3>
            <p>Buy airtime and data for any network, straight from your wallet.</p>
            
            <a href="{{ route('register') }}" class="lp-link">Get started <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card">
            <span class="lp-h-num">06</span>
            <div class="lp-visual" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none">
                <path class="lp-draw" d="M20 12h24v14a12 12 0 0 1-24 0V12z" stroke="currentColor" stroke-width="3"/>
                <path class="lp-draw" d="M20 16h-6a6 6 0 0 0 6 10" stroke="currentColor" stroke-width="2.6"/>
                <path class="lp-draw" d="M44 16h6a6 6 0 0 1-6 10" stroke="currentColor" stroke-width="2.6"/>
                <path class="lp-draw" d="M32 38v8m-8 6h16l-2-6H26l-2 6z" stroke="currentColor" stroke-width="3"/>
            </svg></div>
            <h3>Levels &amp; badges</h3>
            <p>Finish hustles, earn good reviews and unlock badges people can see.</p>
            
            <a href="{{ route('faq') }}" class="lp-link">Read the FAQ <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card lp-c-silver lp-h-video">
            <div class="lp-h-video-wrap" aria-hidden="true"><video autoplay muted loop playsinline preload="none"><source src="https://videos.pexels.com/video-files/3129671/3129671-uhd_2560_1440_30fps.mp4" type="video/mp4"></video></div>
            <span class="lp-h-num">07</span>
            <div class="lp-visual lp-visual-ai" aria-hidden="true"><span class="lp-ai-o lp-ai-o--sm"><span class="lp-ai-o-ring"></span><span class="lp-ai-o-core"></span><span class="lp-ai-o-pupil"></span></span></div>
            <h3>Levi, your AI concierge</h3>
            <p>Ask Levi, your AI concierge, anything, any time.</p>
            
            <a href="{{ route('register') }}" class="lp-link">Meet Levi <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
        </article>

        <article class="lp-h-card lp-h-end">
            <h3>Ready to <span class="lp-grad">start?</span></h3>
            <p>Join free and put your skills, your stuff and your money to work.</p>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary">Create your account <i data-lucide="arrow-right"></i></a>
        </article>
    </div>

    <div class="lp-h-bar" aria-hidden="true"><i></i></div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
(function () {
    if (!window.gsap || !window.ScrollTrigger) return;
    gsap.registerPlugin(ScrollTrigger);
    ScrollTrigger.config({ ignoreMobileResize: true });

    var section = document.getElementById('features');
    var track = document.getElementById('lpHTrack');
    var bar = section.querySelector('.lp-h-bar i');
    var mm = gsap.matchMedia();

    mm.add('(min-width: 1px)', function () {
        section.classList.add('lp-h-on');

        function dist() { return Math.max(0, track.scrollWidth - window.innerWidth); }

        var tween = gsap.to(track, {
            x: function () { return -dist(); },
            ease: 'none',
            scrollTrigger: {
                trigger: section,
                start: 'top top',
                end: function () { return '+=' + dist(); },
                pin: true,
                scrub: 0.8,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                onUpdate: function (self) { bar.style.transform = 'scaleX(' + self.progress + ')'; }
            }
        });

        section.querySelectorAll('.lp-h-card').forEach(function (card) {
            var num = card.querySelector('.lp-h-num');
            if (num) {
                gsap.fromTo(num, { x: 90 }, {
                    x: -90, ease: 'none',
                    scrollTrigger: { trigger: card, containerAnimation: tween, start: 'left right', end: 'right left', scrub: true }
                });
            }
            gsap.fromTo(card, { opacity: 0.35, scale: 0.94 }, {
                opacity: 1, scale: 1, ease: 'none',
                scrollTrigger: { trigger: card, containerAnimation: tween, start: 'left 92%', end: 'left 55%', scrub: true }
            });
        });

        return function () {
            section.classList.remove('lp-h-on');
            bar.style.transform = '';
        };
    });

    window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
</script>

<script>
(function () {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.lp-h-card').forEach(function (c) { c.classList.add('lp-h-card-in'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) { en.target.classList.add('lp-h-card-in'); io.unobserve(en.target); }
        });
    }, { threshold: 0.35 });
    document.querySelectorAll('.lp-h-card').forEach(function (c) { io.observe(c); });
})();
</script>
