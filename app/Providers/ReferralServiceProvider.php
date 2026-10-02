<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class ReferralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // Runs whenever a signup fee is recorded, on any payment path.
        Transaction::created(function (Transaction $t) {
            if ($t->type !== 'signup_fee') {
                return;
            }

            $fee = abs((float) $t->amount);
            $fee = min($fee, (float) config('qelvo.signup_fee', $fee));
            if ($fee <= 0) {
                return;
            }

            $user = User::find($t->user_id);
            if (! $user || ! $user->referred_by || (int) $user->referred_by === (int) $user->id) {
                return;
            }

            $tag = '[REG-' . $user->id . ']';

            DB::transaction(function () use ($user, $fee, $tag) {
                $referrer = User::where('id', $user->referred_by)->lockForUpdate()->first();
                if (! $referrer || $referrer->wallet_restricted) {
                    return;
                }

                $pct = (float) config('referral.signup_fee_bonus.' . ($referrer->tier ?: 'free'), 0);
                $bonus = round($fee * $pct, 2);
                if ($bonus <= 0) {
                    return;
                }

                $already = Transaction::where('user_id', $referrer->id)
                    ->where('type', 'referral_bonus')
                    ->where('description', 'like', '%' . $tag . '%')
                    ->exists();
                if ($already) {
                    return;
                }

                $referrer->increment('wallet_balance', $bonus);
                Transaction::create([
                    'user_id' => $referrer->id,
                    'type' => 'referral_bonus',
                    'amount' => $bonus,
                    'description' => 'Referral bonus from ' . $user->name . ' registering ' . $tag,
                ]);
            });
        });
    }
}
