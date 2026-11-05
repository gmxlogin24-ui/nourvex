<?php

// Marketplace commission taken from the seller on each sale, by the seller's plan.
// Change the numbers here. 0.05 = 5%. Unknown plans use 'default'.
return [
    'commission' => [
        'free'  => 0.05,
        'tier1' => 0.03,
        'tier2' => 0.01,
        'tier3' => 0.00,
        'default' => 0.05,
    ],
];
