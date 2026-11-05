<?php

// Verification levels. Separate from subscription plans (free, tier1, tier2, tier3).
// Amounts are in naira. null = no limit.
// Confirm the Level 3 daily limit with the current CBN circular before going live.
return [
    'levels' => [
        0 => 'No BVN or NIN',
        1 => 'BVN linked',
        2 => 'ID approved',
        3 => 'Full KYC approved (ID, address, selfie)',
    ],
    // Most money a user can send out in one day (transfers, withdrawals, purchases).
    'daily_out' => [
        0 => 20000,
        1 => 50000,
        2 => 200000,
        3 => 5000000,
    ],
    // Most a user can hold in the wallet.
    'wallet_cap' => [
        0 => 20000,
        1 => 300000,
        2 => 500000,
        3 => null,
    ],
];
