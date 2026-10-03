{{-- ===== PLANS ===== --}}
@php
// DRAFT plan content. Edit here: name, tag, blurb, price, note, perks, cta. Empty price = price hidden.
$r = config('plans');
$pc = fn ($k) => (int) round($r[$k]['referral_pct'] * 100) . '%';
$plans = [
    ['key' => 'free', 'plan' => 'free', 'name' => 'Free', 'tag' => 'Start free', 'blurb' => 'Join free and start earning on gigs.', 'price' => '', 'note' => '',
     'perks' => [
        'Airtime, data and cable from your wallet',
        'Browse hustles and Campus Market',
        'Take up to ' . $r['free']['gigs_claim_cap'] . ' gigs at a time (' . (int) round($r['free']['gigs_commission'] * 100) . '% platform fee)',
        'Join the leaderboard',
        'Upgrade any time',
     ],
     'cta' => 'Join free'],
    ['key' => 'silver', 'plan' => 'tier1', 'name' => 'Silver', 'tag' => 'Start here', 'blurb' => 'Everything you need to get going.', 'price' => '', 'note' => '',
     'perks' => [
        'Airtime, data and cable from your wallet',
        'Take gigs and earn - up to ' . $r['tier1']['gigs_claim_cap'] . ' at a time',
        'Buy and sell on Campus Market - ' . $r['tier1']['market_weekly_cap'] . ' listings a week',
        $pc('tier1') . ' referral bonus',
        $r['tier1']['qcoin_daily'] . ' Q Coins daily, more with streaks',
        'Escrow-protected payments',
     ],
     'cta' => 'Get Silver'],
    ['key' => 'gold', 'plan' => 'tier2', 'name' => 'Gold', 'tag' => 'Most popular', 'blurb' => 'For people who hustle and sell regularly.', 'price' => '', 'note' => '', 'featured' => true,
     'perks' => [
        'Everything in Silver',
        'Post your own gigs',
        'No platform fee on gigs',
        $r['tier2']['market_weekly_cap'] . ' listings a week',
        $pc('tier2') . ' referral bonus',
        $r['tier2']['qcoin_daily'] . ' Q Coins daily, more with streaks',
        'Money Tracker and verified seller badge',
     ],
     'cta' => 'Get Gold'],
    ['key' => 'platinum', 'plan' => 'tier3', 'name' => 'Premium', 'tag' => 'Everything unlocked', 'blurb' => 'For power users and top sellers.', 'price' => '', 'note' => '',
     'perks' => [
        'Everything in Gold',
        'Unlimited gig claims and listings',
        $pc('tier3') . ' referral bonus',
        $r['tier3']['qcoin_daily'] . ' Q Coins daily, more with streaks',
        'Top ranking multiplier (x' . config('ranking.plan_multiplier.tier3') . ')',
        'Priority support',
     ],
     'cta' => 'Get Premium'],
];
@endphp
<section class="lp-pl" id="lpPlans">
    <div class="lp-pl-head">
        <p class="lp-eyebrow" style="margin-bottom:12px">Plans</p>
        <h2 class="lp-h2" style="margin:0">Pick the plan that <span class="lp-grad">fits you.</span></h2>
    </div>
    <div class="lp-pl-row" id="lpPlRow">
        @foreach ($plans as $p)
        <article class="lp-pl-card lp-pl-{{ $p['key'] }} {{ !empty($p['featured']) ? 'is-featured' : '' }}">
            <div class="lp-pl-face">
                <span class="lp-pl-tag">{{ $p['tag'] }}</span>
                <span class="lp-pl-chip" aria-hidden="true"></span>
                <div class="lp-pl-foot">
                    <div class="lp-pl-name">{{ $p['name'] }}</div>
                    <div class="lp-pl-brand">NOURVEX</div>
                </div>
            </div>
            <div class="lp-pl-body">
                <p class="lp-pl-blurb">{{ $p['blurb'] }}</p>
                @if (!empty($p['price']))
                <div class="lp-pl-price">{{ $p['price'] }}<small>{{ $p['note'] ?? '' }}</small></div>
                @endif
                <div class="lp-pl-label">What's included</div>
                <ul class="lp-pl-list">
                    @foreach ($p['perks'] as $perk)
                    <li>{{ $perk }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register', ['plan' => $p['plan'] ?? null]) }}" class="lp-pl-btn">{{ $p['cta'] }}</a>
            </div>
        </article>
        @endforeach
    </div>
</section>

<script>
(function () {
    var row = document.getElementById('lpPlRow');
    if (!row) return;
    function go() {
        if (window.matchMedia('(max-width: 900px)').matches) {
            var g = row.querySelector('.is-featured');
            if (g) row.scrollLeft = g.offsetLeft - (row.clientWidth - g.offsetWidth) / 2;
        }
        if (!window.gsap || !window.ScrollTrigger) return;
        gsap.registerPlugin(ScrollTrigger);
        gsap.fromTo(row.querySelectorAll('.lp-pl-card'), { y: 70, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.9, ease: 'power3.out', stagger: 0.15, clearProps: 'transform,opacity',
              scrollTrigger: { trigger: row, start: 'top 85%', once: true } });
    }
    if (document.readyState === 'complete') go(); else window.addEventListener('load', go);
})();
</script>
