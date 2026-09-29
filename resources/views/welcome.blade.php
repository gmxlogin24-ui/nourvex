@extends('layouts.landing')
@section('content')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) }}">
<script>document.documentElement.classList.add('lp-js');</script>

@php
    $feed = collect($adsFeed ?? [])->map(fn($a) => $a + ['tag' => 'Sponsored'])->concat(collect($newsFeed ?? []))->take(6);
@endphp

<main class="lp">

    {{-- ===== HERO ===== --}}
    <section class="lp-hero">
        <div class="lp-hero-bg"></div>
        <div class="lp-hero-scrim"></div>
        <div class="lp-hero-in">
            <span class="lp-hero-side-label" aria-hidden="true">Work &middot; Sell &middot; Earn</span>
            <div class="lp-hero-word-row lp-rv">
                <span class="lp-hero-word">QELV<span class="lp-hero-o lp-ai-o"><svg viewBox="0 0 100 116" overflow="visible">
                    <ellipse cx="50" cy="58" rx="40" ry="49" fill="#ffffff" stroke="#e3b34a" stroke-width="4"/>
                    <circle class="lp-ai-o-pupil lp-hero-eye-pupil" cx="50" cy="61" r="15" fill="#0b0a08"/>
                </svg></span></span>
                <span class="lp-hero-line" aria-hidden="true"></span>
            </div>
            <div class="lp-hero-social lp-rv">
                <a href="#" aria-label="Instagram"><i class="ph-bold ph-instagram-logo"></i></a>
                <a href="#" aria-label="X"><i class="ph-bold ph-x-logo"></i></a>
                <a href="#" aria-label="Facebook"><i class="ph-bold ph-facebook-logo"></i></a>
            </div>
            <div class="lp-hero-bottom">
                <div class="lp-hero-tag lp-rv">
                    <b>About Qelvo</b>
                    Work, sell and get paid — all in one campus app.
                </div>
                <a href="{{ route('register') }}" class="lp-hero-cta lp-rv">Get Started <i data-lucide="arrow-right"></i></a>
            </div>
        </div>
    </section>

    {{-- ===== TRUST STRIP ===== --}}
    <section class="lp-trust">
        <div class="lp-wrap lp-trust-in">
            <div class="lp-trust-item"><i data-lucide="lock"></i><span><b>Escrow-held</b> hustle payments</span></div>
            <div class="lp-trust-item"><i data-lucide="badge-check"></i><span><b>Verified</b> sellers &amp; badges</span></div>
            <div class="lp-trust-item"><i data-lucide="wallet"></i><span><b>Wallet</b> built for students</span></div>
            <div class="lp-trust-item"><i data-lucide="life-buoy"></i><span><b>Customer care</b> &amp; dispute help</span></div>
        </div>
    </section>

@include('partials.landing-features-hscroll')

    {{-- ===== HOW IT WORKS ===== --}}
    <section class="lp-sec" style="padding-top:0">
        <div class="lp-wrap">
            <div class="lp-head lp-rv">
                <p class="lp-eyebrow">How it works</p>
                <h2 class="lp-h2">From sign-up to paid in three steps.</h2>
            </div>
            <div class="lp-steps">
                <div class="lp-step lp-rv"><div class="lp-num">01</div><h3>Create your account</h3><p>Join free in minutes and set up your wallet.</p></div>
                <div class="lp-step lp-rv"><div class="lp-num">02</div><h3>Hustle or sell</h3><p>Post a task, pick one up, or list something on Campus Market.</p></div>
                <div class="lp-step lp-rv"><div class="lp-num">03</div><h3>Get paid safely</h3><p>Escrow releases to your wallet once the work is delivered.</p></div>
            </div>
        </div>
    </section>

    {{-- ===== LIVE FEED ===== --}}
    @if($feed->count())
    <section class="lp-sec" style="padding-top:0">
        <div class="lp-wrap">
            <div class="lp-head lp-rv">
                <p class="lp-eyebrow">Happening now</p>
                <h2 class="lp-h2">Fresh from the campus feed.</h2>
            </div>
            <div class="lp-feed">
                @foreach($feed as $item)
                <article class="lp-post lp-rv">
                    @if(!empty($item['image_url']))
                        <div class="lp-post-img" style="background-image:url('{{ e($item['image_url']) }}')"></div>
                    @else
                        <div class="lp-post-img"></div>
                    @endif
                    <div class="lp-post-b">
                        <span class="lp-chip lp-chip-v" style="align-self:flex-start">{{ $item['tag'] ?? 'Campus Buzz' }}</span>
                        <h4>{{ \Illuminate\Support\Str::limit($item['title'] ?? '', 70) }}</h4>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($item['body'] ?? ''), 110) }}</p>
                        @if(!empty($item['time']))
                            <span class="lp-post-t">{{ \Carbon\Carbon::parse($item['time'])->diffForHumans() }}</span>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== PRICING ===== --}}
    <section class="lp-sec" style="padding-top:0">
        <div class="lp-wrap">
            <div class="lp-head lp-rv">
                <p class="lp-eyebrow">Pricing</p>
                <h2 class="lp-h2">Pick your plan.</h2>
                <p class="lp-lead">Start free. Upgrade when you're ready to sell more.</p>
            </div>
            <div class="lp-price-grid">
                <div class="lp-plan lp-plan-silver lp-plan-photo lp-rv" style="--card-photo:url('https://images.unsplash.com/photo-1759185301790-e079e2970809?auto=format&fit=crop&w=1200&q=65');--tier-gradient:linear-gradient(160deg,rgba(15,17,20,.82) 0%,rgba(150,158,168,.5) 55%,rgba(10,11,13,.88) 100%)">
                    <span class="lp-k">Active sellers</span>
                    <h3>Silver</h3>
                    <div class="lp-amt">&#8358;5,000 <small>/ year</small></div>
                    <ul>
                        <li><i data-lucide="check"></i> Everything in Free</li>
                        <li><i data-lucide="check"></i> Post hustles &amp; listings</li>
                        <li><i data-lucide="check"></i> 10% referral bonus</li>
                    </ul>
                    <a href="{{ route('register') }}" class="lp-btn lp-btn-ghost">Get Started</a>
                </div>

                <div class="lp-plan lp-plan-gold lp-plan-hot lp-plan-photo lp-rv" style="--card-photo:url('https://images.unsplash.com/photo-1517196084897-498e0abd7c2d?auto=format&fit=crop&w=1200&q=65');--tier-gradient:linear-gradient(160deg,rgba(12,9,3,.84) 0%,rgba(180,130,30,.55) 55%,rgba(10,7,3,.9) 100%)">
                    <span class="lp-badge">BEST VALUE</span>
                    <span class="lp-k">Power sellers</span>
                    <h3>Gold</h3>
                    <div class="lp-amt">&#8358;10,000 <small>/ year</small></div>
                    <ul>
                        <li><i data-lucide="check"></i> Everything in Silver</li>
                        <li><i data-lucide="check"></i> Verified badge</li>
                        <li><i data-lucide="check"></i> Featured placement</li>
                        <li><i data-lucide="check"></i> 15% referral bonus</li>
                    </ul>
                    <a href="{{ route('register') }}" class="lp-btn lp-btn-primary">Get Started</a>
                </div>

                <div class="lp-plan lp-plan-platinum lp-plan-photo lp-rv" style="--card-photo:url('https://images.unsplash.com/photo-1759185301790-e079e2970809?auto=format&fit=crop&w=1200&q=65');--tier-gradient:linear-gradient(160deg,rgba(20,21,24,.7) 0%,rgba(235,238,243,.55) 55%,rgba(15,16,18,.8) 100%)">
                    <span class="lp-k">Top tier</span>
                    <h3>Platinum</h3>
                    <div class="lp-amt">&#8358;15,000 <small>/ year</small></div>
                    <ul>
                        <li><i data-lucide="check"></i> Everything in Gold</li>
                        <li><i data-lucide="check"></i> Unlimited active posts</li>
                        <li><i data-lucide="check"></i> Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="lp-btn lp-btn-ghost">Get Started</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FINAL CTA ===== --}}
    <section class="lp-wrap">
        <div class="lp-final lp-rv">
            <h2>Your campus hustle <span class="lp-grad">starts here.</span></h2>
            <p>Join Qelvo and put your skills, your stuff and your money to work.</p>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary">Create your free account <i data-lucide="arrow-right"></i></a>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="lp-foot">
        <div class="lp-wrap">
            <div class="lp-foot-grid">
                <div>
                    <div class="lp-foot-brand">Qelvo</div>
                    <p class="lp-foot-note">Work, sell and get paid, all in one campus app.</p>
                </div>
                <div>
                    <h5>Explore</h5>
                    <ul>
                        <li><a href="{{ route('gigs.index') }}">Hustles</a></li>
                        <li><a href="{{ route('marketplace.index') }}">Campus Market</a></li>
                        <li><a href="{{ route('pulse.index') }}">Pulse</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Company</h5>
                    <ul>
                        <li><a href="{{ route('features') }}">Features</a></li>
                        <li><a href="{{ route('safety') }}">Safety</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Account</h5>
                    <ul>
                        <li><a href="{{ route('login') }}">Log In</a></li>
                        <li><a href="{{ route('register') }}">Get Started</a></li>
                    </ul>
                </div>
            </div>
            <div class="lp-copy">&copy; {{ date('Y') }} Qelvo. All rights reserved.</div>
        </div>
    </footer>
</main>

<script>
(function () {
    var els = document.querySelectorAll('.lp-rv');
    if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (e, i) { e.style.transitionDelay = ((i % 4) * 70) + 'ms'; io.observe(e); });
})();
</script>

<script>
(function () {
    var eyes = document.querySelectorAll('.lp-ai-o');
    if (!eyes.length) return;
    function look(x, y) {
        eyes.forEach(function (o) {
            var pupil = o.querySelector('.lp-ai-o-pupil');
            if (!pupil) return;
            var r = o.getBoundingClientRect();
            var cx = r.left + r.width / 2, cy = r.top + r.height / 2;
            var dx = x - cx, dy = y - cy;
            var ang = Math.atan2(dy, dx);
            var rad = Math.min(r.width, r.height) * 0.16;
            pupil.style.transform = 'translate(' + (Math.cos(ang) * rad) + 'px,' + (Math.sin(ang) * rad) + 'px)';
        });
        document.documentElement.classList.add('lp-tracking');
    }
    window.addEventListener('pointermove', function (e) { look(e.clientX, e.clientY); }, { passive: true });
    window.addEventListener('touchmove', function (e) { if (e.touches[0]) look(e.touches[0].clientX, e.touches[0].clientY); }, { passive: true });
})();
</script>
@endsection
