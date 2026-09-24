<?php

namespace App\Services;

use App\Models\Gig;
use App\Models\Transaction;
use App\Models\User;

class GigPayoutService
{
    /**
     * Pays a worker (and their referrer's bonus, if any) for a gig.
     * Must always be called from inside a DB::transaction() with $worker
     * already locked (lockForUpdate) by the caller — this method does not
     * open its own transaction or lock.
     *
     * $overrideAmount: if given, pays out based on this amount instead of
     * the gig's full price — used for partial dispute resolutions where
     * the worker is only paid for a percentage of the job. Null (default)
     * keeps the normal full-payout behavior.
     */
    public function payout(Gig $gig, User $worker, ?float $overrideAmount = null): void
    {
        $baseAmount = $overrideAmount ?? $gig->amount;

        $rate = match ($worker->tier) {
            'tier2', 'tier3' => 0,
            'tier1' => 0.03,
            default => 0.05,
        };
        $commission = round($baseAmount * $rate, 2);
        $payout = $baseAmount - $commission;

        $worker->increment('wallet_balance', $payout);
        Transaction::create([
            'user_id' => $worker->id,
            'type' => 'gig_earning',
            'amount' => $payout,
            'description' => 'Completed gig: ' . $gig->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : '') . ($overrideAmount !== null ? ' (partial payout)' : ''),
        ]);
        \App\Models\PlatformRevenue::record('gigs', $commission, 'Commission: ' . $gig->title, 'gig', $gig->id);

        if ($worker->referred_by) {
            $referrer = User::where('id', $worker->referred_by)->lockForUpdate()->first();
            if ($referrer && !$referrer->wallet_restricted) {
                $pct = in_array($referrer->tier, ['tier2', 'tier3'], true) ? 0.15 : 0;
                $bonus = $payout * $pct;
                if ($bonus > 0) {
                    $referrer->increment('wallet_balance', $bonus);
                    Transaction::create([
                        'user_id' => $referrer->id,
                        'type' => 'referral_bonus',
                        'amount' => $bonus,
                        'description' => 'Referral bonus from ' . $worker->name . ' completing a gig',
                    ]);
                }
            }
        }
    }
}
