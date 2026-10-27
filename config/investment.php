<?php

return [
    // 20 Q = ₦1. Everything is stored in Q. Naira is display only.
    'qcoin_per_naira' => 20,

    'types' => [
        'business' => 'Business funding',
        'land' => 'Land pool',
    ],

    // Per user, per project: total across that user's active positions.
    'min_invest_q' => 500,
    'cap_unverified_q' => 100000,
    'cap_verified_q' => 2000000,

    // One investor can hold at most this share of a project's goal.
    'max_share_of_goal_percent' => 20,

    'min_goal_q' => 20000,
    'max_goal_q' => 50000000,

    'min_duration_days' => 7,
    'max_duration_days' => 180,

    'max_active_projects_per_owner' => 3,

    // Taken from reported returns before investors are paid.
    'platform_fee_percent' => 5,
];
