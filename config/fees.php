<?php

// Withdrawal fee by plan (as a fraction: 0.01 = 1%).
// Free uses the admin setting (Pricing::withdrawalFeeRate, 1.5% by default).
return [
    'withdrawal' => [
        'tier1' => 0.01,   // Silver
        'tier2' => 0.005,  // Gold
        'tier3' => 0.0,    // Premium: no withdrawal fee
    ],
];
