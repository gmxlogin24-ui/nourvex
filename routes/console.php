<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:expire')->daily();
Schedule::command('gigs:remind-pending-payments')->daily();
Schedule::command('qcoin:remind-unclaimed')->dailyAt('20:00');
Schedule::command('marketplace:release-escrow')->daily();


// Monthly Q Coin profit share: recalculates on the 1st. Test mode previews only; live mode credits.
Schedule::command('qcoin:profit-share --apply')->monthlyOn(1, '06:00');

Schedule::command('investments:expire')->hourly();
