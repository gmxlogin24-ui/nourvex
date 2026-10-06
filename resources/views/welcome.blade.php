@extends('layouts.landing')
@section('content')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) }}">
<script>document.documentElement.classList.add('lp-js');</script>

@php
    $feed = collect($adsFeed ?? [])->map(fn($a) => $a + ['tag' => 'Sponsored'])->concat(collect($newsFeed ?? []))->take(6);
@endphp

<main class="lp lp-light">
<script>try{if(localStorage.getItem("nvTheme")==="dark")document.querySelector("main.lp").classList.remove("lp-light")}catch(e){}</script>

    {{-- ===== HERO ===== --}}
@include('partials.landing-hero')
@include('partials.landing-hero-mobile')
@include('partials.landing-trust-marquee')
@include('partials.landing-theme')

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
@include('partials.landing-steps-stack')

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

@include('partials.landing-plans')
    {{-- ===== FINAL CTA ===== --}}
    <section class="lp-wrap">
        <div class="lp-final lp-rv">
            <h2>Your campus hustle <span class="lp-grad">starts here.</span></h2>
            <p>Join NOURVEX and put your skills, your stuff and your money to work.</p>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-primary">Create your free account <i data-lucide="arrow-right"></i></a>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="lp-foot">
        <div class="lp-wrap">
            <div class="lp-foot-grid">
                <div>
                    <div class="lp-foot-brand">NOURVEX</div>
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
            <div class="lp-copy">&copy; {{ date('Y') }} NOURVEX. All rights reserved.</div>
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
