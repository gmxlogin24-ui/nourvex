<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\QCoinClaimReminder;
use Illuminate\Console\Command;

class SendQCoinClaimReminders extends Command
{
    protected $signature = 'qcoin:remind-unclaimed';

    protected $description = 'Reminds users who have not claimed their daily Q Coin bonus yet today';

    public function handle(): void
    {
        $today = now()->toDateString();

        $users = User::where(function ($q) use ($today) {
                $q->whereNull('q_coin_last_claim_date')
                  ->orWhere('q_coin_last_claim_date', '!=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('q_coin_reminder_sent_date')
                  ->orWhere('q_coin_reminder_sent_date', '!=', $today);
            })
            ->get();

        foreach ($users as $user) {
            $user->notify(new QCoinClaimReminder($user->q_coin_streak_days ?? 0));
            $user->update(['q_coin_reminder_sent_date' => $today]);
        }

        $this->info($users->count() . ' Q Coin reminder(s) sent.');
    }
}
