<?php

return [
    'daily_cap' => 30,
    // chance = % to hole out; payout is the total Q returned on a win (stake is already paid)
    'clubs' => [
        'wedge'  => ['name' => 'Wedge',  'stake' => 5,  'chance' => 45, 'payout' => 10,  'note' => 'Short and steady'],
        'iron'   => ['name' => 'Iron',   'stake' => 10, 'chance' => 30, 'payout' => 30,  'note' => 'Balanced'],
        'driver' => ['name' => 'Driver', 'stake' => 20, 'chance' => 12, 'payout' => 150, 'note' => 'Long shot, big reward'],
    ],
    'lies' => ['bunker', 'water', 'rough', 'short'],
];
