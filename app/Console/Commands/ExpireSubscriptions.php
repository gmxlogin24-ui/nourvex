<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Expire subscriptions past their renewal date and downgrade users who did not renew';

    public function handle(): void
    {
        $expired = Subscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);

            $user = $sub->user;
            if (!$user) continue;

            $stillActive = Subscription::currentFor($user);
            if (!$stillActive) {
                $user->update([
                    'tier' => 'free',
                    'subscription_expires_at' => null,
                    'is_verified_seller' => false,
                ]);
            }
        }

        $this->info($expired->count() . ' subscription(s) expired.');
    }
}
