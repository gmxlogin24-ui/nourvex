<?php

// Plan rules (what each plan can do) + the text shown on the Plans page.
// true/false = on/off. Number = limit. null = unlimited. 0 = not allowed.
$rules = [
    'free' => [
        'gigs_post' => false, 'gigs_claim_cap' => 2, 'gigs_commission' => 0.05,
        'market_buy' => false, 'market_sell' => false, 'market_weekly_cap' => 0,
        'referral_pct' => 0.00, 'qcoin_daily' => 0,
    ],
    'tier1' => [ // Silver
        'gigs_post' => false, 'gigs_claim_cap' => 5, 'gigs_commission' => 0.03,
        'market_buy' => true, 'market_sell' => true, 'market_weekly_cap' => 10,
        'referral_pct' => 0.10, 'qcoin_daily' => 25,
    ],
    'tier2' => [ // Gold
        'gigs_post' => true, 'gigs_claim_cap' => 5, 'gigs_commission' => 0.00,
        'market_buy' => true, 'market_sell' => true, 'market_weekly_cap' => 20,
        'referral_pct' => 0.15, 'qcoin_daily' => 40,
    ],
    'tier3' => [ // Premium
        'gigs_post' => true, 'gigs_claim_cap' => null, 'gigs_commission' => 0.00,
        'market_buy' => true, 'market_sell' => true, 'market_weekly_cap' => null,
        'referral_pct' => 0.20, 'qcoin_daily' => 55,
    ],
];

$mult = (require __DIR__ . '/ranking.php')['plan_multiplier'];
$pct = fn ($v) => (int) round($v * 100) . '%';
$cap = fn ($v) => $v === null ? 'Unlimited' : (string) $v;
$claim = fn ($v) => $v === null ? 'Unlimited' : $v . ' at a time';
$fee = fn ($v) => $v > 0 ? $pct($v) . ' platform fee on gigs' : 'No platform fee on gigs';

return $rules + [
    'tiers' => [
        'tier1' => [
            'tagline' => 'Start here',
            'intro' => 'Everything you need to get going.',
            'highlights' => [
                ['smartphone', 'Airtime, data and cable from your wallet'],
                ['briefcase', 'Take gigs and earn - up to ' . $rules['tier1']['gigs_claim_cap'] . ' at a time'],
                ['shopping-bag', 'Buy and sell on Campus Market - ' . $rules['tier1']['market_weekly_cap'] . ' listings a week'],
                ['users', $pct($rules['tier1']['referral_pct']) . ' referral bonus'],
                ['coins', $rules['tier1']['qcoin_daily'] . ' Q Coins every day, more with streaks'],
            ],
            'note' => 'Posting gigs starts on Gold.',
        ],
        'tier2' => [
            'tagline' => 'Most popular',
            'intro' => 'Everything in Silver, plus posting and lower fees.',
            'highlights' => [
                ['briefcase', 'Post your own gigs'],
                ['percent', $fee($rules['tier2']['gigs_commission'])],
                ['shopping-bag', $cap($rules['tier2']['market_weekly_cap']) . ' listings a week'],
                ['users', $pct($rules['tier2']['referral_pct']) . ' referral bonus'],
                ['coins', $rules['tier2']['qcoin_daily'] . ' Q Coins every day, more with streaks'],
                ['trending-up', 'Money Tracker'],
                ['badge-check', 'Verified seller badge'],
            ],
            'note' => null,
        ],
        'tier3' => [
            'tagline' => 'Everything unlocked',
            'intro' => 'Everything in Gold, with no limits.',
            'highlights' => [
                ['infinity', 'Unlimited gig claims and listings'],
                ['users', $pct($rules['tier3']['referral_pct']) . ' referral bonus'],
                ['coins', $rules['tier3']['qcoin_daily'] . ' Q Coins every day, more with streaks'],
                ['trophy', 'Top ranking multiplier (x' . $mult['tier3'] . ')'],
                ['headphones', 'Priority support'],
            ],
            'note' => null,
        ],
    ],

    // Each row: [label, Silver, Gold, Premium]. true = tick, false = dash, text = shown as is.
    'compare' => [
        ['Airtime, data and cable', true, true, true],
        ['Take gigs', $claim($rules['tier1']['gigs_claim_cap']), $claim($rules['tier2']['gigs_claim_cap']), $claim($rules['tier3']['gigs_claim_cap'])],
        ['Post gigs', $rules['tier1']['gigs_post'], $rules['tier2']['gigs_post'], $rules['tier3']['gigs_post']],
        ['Gig platform fee', $pct($rules['tier1']['gigs_commission']), $pct($rules['tier2']['gigs_commission']), $pct($rules['tier3']['gigs_commission'])],
        ['Buy and sell on Campus Market', true, true, true],
        ['Listings per week', $cap($rules['tier1']['market_weekly_cap']), $cap($rules['tier2']['market_weekly_cap']), $cap($rules['tier3']['market_weekly_cap'])],
        ['Referral bonus', $pct($rules['tier1']['referral_pct']), $pct($rules['tier2']['referral_pct']), $pct($rules['tier3']['referral_pct'])],
        ['Daily Q Coins', (string) $rules['tier1']['qcoin_daily'], (string) $rules['tier2']['qcoin_daily'], (string) $rules['tier3']['qcoin_daily']],
        ['Ranking points', 'x' . $mult['tier1'], 'x' . $mult['tier2'], 'x' . $mult['tier3']],
        ['Money Tracker', false, true, true],
        ['Verified seller badge', false, true, true],
        ['Post ads on Buzz', false, true, true],
    ],
];
