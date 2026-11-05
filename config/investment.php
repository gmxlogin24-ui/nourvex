<?php

return [
    // Amounts are whole naira (1 unit = ₦1). The _q names are left over from the old Q Coin version.
    'qcoin_per_naira' => 1,

    'types' => [
        'business' => 'Business funding',
        'land' => 'Land pool',
        'energy' => 'Energy project',
    ],

    // Per user, per project: total across that user's active positions.
    'min_invest_q' => 1000,
    'cap_unverified_q' => 5000,
    'cap_verified_q' => 100000,

    // One investor can hold at most this share of a project's goal.
    'max_share_of_goal_percent' => 20,

    'min_goal_q' => 100000,
    'max_goal_q' => 50000000,

    'min_duration_days' => 7,
    'max_duration_days' => 180,

    'max_active_projects_per_owner' => 3,

    // Taken from reported returns before investors are paid.
    'platform_fee_percent' => 5,
    // Who may invest: country list, yearly limit as a percent of declared income (0 = off), cancel window, risk text version.
    'allowed_countries' => ['Nigeria'],
    'income_limit_percent' => 10,
    'cooling_off_hours' => 48,
    'risk_ack_version' => '2026-10-29-1',
];
