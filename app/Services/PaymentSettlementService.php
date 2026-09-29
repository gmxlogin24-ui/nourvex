<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTopup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single place where a verified Paystack payment turns into money/state changes.
 * Used by BOTH the browser callback and the webhook, so whichever arrives first
 * does the work and the other one is a safe no-op (row-locked + idempotent).
 * Each method refuses a reference with the wrong prefix, so a payment made for
 * one purpose can never be replayed against another.
 */
class PaymentSettlementService
{
    private function lockTopup(WalletTopup $topup, string $prefix, float $paidAmount): ?WalletTopup
    {
        $locked = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
        if (!$locked) return null;
        if (!str_starts_with((string) $locked->tx_ref, $prefix)) return null;
        if ($locked->status === 'successful') return null;
        if (round($paidAmount, 2) < round((float) $locked->amount, 2)) return null;
        return $locked;
    }

    public function settleWalletTopup(WalletTopup $topup, float $paidAmount): ?User
    {
        $notify = null;

        DB::transaction(function () use ($topup, $paidAmount, &$notify) {
            $locked = $this->lockTopup($topup, 'QELVO-', $paidAmount);
            if (!$locked) return;

            $user = User::where('id', $locked->user_id)->lockForUpdate()->first();
            if (!$user) return;

            $locked->update(['status' => 'successful']);
            $user->increment('wallet_balance', $locked->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'wallet_topup',
                'amount' => $locked->amount,
                'description' => 'Wallet top up via Paystack',
            ]);
            $notify = $user;
        });

        if ($notify) {
            try {
                $notify->notify(new \App\Notifications\WalletTopupCredited((float) $topup->amount));
            } catch (\Throwable $e) {
                Log::warning('WalletTopupCredited notify failed: ' . $e->getMessage());
            }
        }

        return $notify;
    }

    /** Returns true only when this call newly activated the account. */
    public function settleSignupFee(WalletTopup $topup, float $paidAmount, array $paystackData = []): bool
    {
        $activated = false;

        DB::transaction(function () use ($topup, $paidAmount, $paystackData, &$activated) {
            $locked = $this->lockTopup($topup, 'SIGNUPFEE-', $paidAmount);
            if (!$locked) return;

            $user = User::where('id', $locked->user_id)->lockForUpdate()->first();
            if (!$user) return;

            $locked->update(['status' => 'successful']);
            if ($user->signup_fee_paid) return;

            $update = ['signup_fee_paid' => true, 'badge_tier' => 'bronze'];
            if ($user->tier === 'free' || $user->tier === null) {
                $update['tier'] = 'tier1';
                $update['tier_upgraded_at'] = now();
            }
            $auth = $paystackData['authorization'] ?? null;
            if ($auth && !empty($auth['authorization_code'])) {
                $update['card_token'] = $auth['authorization_code'];
                $update['card_last4'] = $auth['last4'] ?? null;
                $update['card_type'] = $auth['card_type'] ?? null;
            }
            $user->update($update);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'signup_fee',
                'amount' => -((float) $locked->amount),
                'description' => 'Account activation via Paystack',
            ]);
            $activated = true;
        });

        return $activated;
    }

    public function settleVendor(WalletTopup $topup, float $paidAmount): bool
    {
        $activated = false;

        DB::transaction(function () use ($topup, $paidAmount, &$activated) {
            $locked = $this->lockTopup($topup, 'VENDORREG-', $paidAmount);
            if (!$locked) return;

            $user = User::where('id', $locked->user_id)->lockForUpdate()->first();
            if (!$user) return;

            $fee = (float) $locked->amount;
            $locked->update(['status' => 'successful']);

            $update = ['is_vendor_active' => true];
            if ($user->tier === 'free' || $user->tier === null) {
                $update['tier'] = 'tier1';
                $update['tier_upgraded_at'] = now();
            }
            $user->update($update);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'vendor_registration',
                'amount' => -$fee,
                'description' => 'Vendor registration fee',
            ]);

            if ($user->referred_by) {
                $referrer = User::where('id', $user->referred_by)->lockForUpdate()->first();
                if ($referrer && !$referrer->wallet_restricted) {
                    $pct = $referrer->tier === 'tier2' ? 0.15 : 0;
                    $bonus = $fee * $pct;
                    if ($bonus > 0) {
                        $referrer->increment('wallet_balance', $bonus);
                        Transaction::create([
                            'user_id' => $referrer->id,
                            'type' => 'referral_bonus',
                            'amount' => $bonus,
                            'description' => 'Referral bonus from ' . $user->name . ' vendor registration',
                        ]);
                    }
                }
            }
            $activated = true;
        });

        return $activated;
    }
}
