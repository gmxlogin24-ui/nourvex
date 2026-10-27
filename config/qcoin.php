<?php

return [
    // test = preview only, nothing credited, Q not redeemable. live = credits real Q.
    'mode' => env('QCOIN_MODE', 'test'),

    // Display rate: how many Q equal ₦1. Naira stays the stored balance.
    'q_per_naira_display' => 10,

    // Daily streak bonus: +step_bonus every step_days consecutive days, capped at max_bonus.
    'streak_step_days' => 7,
    'streak_step_bonus' => 1,
    'streak_max_bonus' => 5,

    'profit_share' => [
        'pool_share' => 0.40,   // 40% of (revenue - costs) goes into the pool
        'q_per_naira' => 10,    // 10 Q per ₦1 of pool
    ],

    // Q Coin price per ad duration (days => coins). Edit here.
    'ad_prices' => [3 => 10000, 7 => 20000, 14 => 30000],
];
