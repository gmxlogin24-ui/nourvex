<?php

// Dojah identity checks (BVN, NIN, selfie match). Keys are set in .env.
return [
    'base_url' => env('DOJAH_BASE_URL', 'https://api.dojah.io'),
    'app_id' => env('DOJAH_APP_ID'),
    'secret' => env('DOJAH_SECRET_KEY'),
    // Safer default: a selfie must score at least this to count as a match (0 to 100).
    'match_threshold' => 90,
];
