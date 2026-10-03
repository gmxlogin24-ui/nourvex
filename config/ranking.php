<?php

return [
    // Base points per action. Plan multiplier is applied on top.
    'points' => [
        'daily_login' => 2,
        'streak_7' => 20,
        'streak_30' => 100,
        'qcoin_claim' => 3,
        'referral' => 25,
        'gig_completed' => 15,
        'task' => 5,
        'service_purchase' => 3,
    ],
    // Airtime/data/cable: base points above + this many extra per 100 naira spent, capped.
    'service_per_100' => 1,
    'service_cap' => 25,
    'plan_multiplier' => [
        'free' => 1.0,
        'tier1' => 1.25,
        'tier2' => 1.5,
        'tier3' => 2.0,
    ],
];
