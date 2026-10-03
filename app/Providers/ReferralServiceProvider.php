<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class ReferralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    // A paid signup now buys a plan. Whenever a signup_fee transaction is recorded
    // (registration, Google signup, webhook), turn it into an active subscription so
    // the plan, expiry, verified badge and referral bonus are handled in one place.
    public function boot(): void
    {
        Transaction::created(function (Transaction $t) {
            if ($t->type !== 'signup_fee') {
                return;
            }

            $paid = abs((float) $t->amount);
            $plan = null;
            foreach (Subscription::PLANS as $key => $p) {
                if (abs($paid - (float) $p['price']) < 0.01) {
                    $plan = $key;
                    break;
                }
            }
            if (! $plan) {
                return;
            }

            $user = User::find($t->user_id);
            if (! $user) {
                return;
            }

            $recent = Subscription::where('user_id', $user->id)
                ->where('plan', $plan)
                ->where('status', 'active')
                ->where('starts_at', '>', now()->subMinutes(10))
                ->exists();
            if ($recent) {
                return;
            }

            $sub = Subscription::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'amount' => $paid,
                'payment_method' => 'paystack',
                'status' => 'pending_payment',
                'starts_at' => now(),
                'expires_at' => now(),
            ]);
            $sub->activate();

            $dup = Transaction::where('user_id', $user->id)->where('type', 'tier_upgrade')->orderByDesc('id')->first();
            if ($dup) {
                $dup->delete();
            }

            $t->description = 'Subscribed to ' . Subscription::PLANS[$plan]['label'] . ' (1 year)';
            $t->saveQuietly();
        });
    }
}
