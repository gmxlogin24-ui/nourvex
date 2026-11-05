<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Pricing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SalePayout
{
    // Fee the seller or worker pays on a payout to their bank, by plan. Free uses the admin setting.
    public static function feeRate(User $seller): float
    {
        $tier = $seller->tier ?: 'free';
        return $tier === 'free'
            ? Pricing::withdrawalFeeRate()
            : (float) config('fees.withdrawal.' . $tier, 0);
    }

    // Marketplace sale released to the seller.
    public static function pay(User $seller, float $gross, Listing $listing): void
    {
        self::run($seller, $gross, 'Sold: ' . $listing->title, 'marketplace_sale', 'marketplace_sale_bank');
    }

    // Gig completed, paid to the worker.
    public static function payGig(User $worker, float $gross, string $title): void
    {
        self::run($worker, $gross, 'Completed gig: ' . $title, 'gig_earning', 'gig_earning_bank');
    }

    // Bank account on file: a payout row goes to Paystack transfer after the sale commits.
    // No bank account, or a net payout under ₦100: the money goes to the wallet instead.
    private static function run(User $seller, float $gross, string $label, string $walletType, string $bankType): void
    {
        $gross = round($gross, 2);
        if ($gross <= 0) {
            return;
        }
        $hasBank = filled($seller->payout_account_number) && filled($seller->payout_bank_code);
        $fee = $hasBank ? round($gross * self::feeRate($seller), 2) : 0.0;
        $net = round($gross - $fee, 2);

        if (!$hasBank || $net < 100) {
            $seller->increment('wallet_balance', $gross);
            Transaction::create([
                'user_id' => $seller->id,
                'type' => $walletType,
                'amount' => $gross,
                'description' => $label,
            ]);
            return;
        }

        $ref = 'PAYOUT-' . $seller->id . '-' . time() . '-' . Str::random(4);
        $w = Withdrawal::create([
            'user_id' => $seller->id,
            'tx_ref' => $ref,
            'amount' => $net,
            'fee' => $fee,
            'bank_code' => $seller->payout_bank_code,
            'bank_name' => $seller->payout_bank_name ?: 'Bank',
            'account_number' => $seller->payout_account_number,
            'account_name' => $seller->payout_account_name,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $seller->id,
            'type' => $bankType,
            'amount' => $gross,
            'description' => $label . ' (paid to your bank)',
        ]);

        // Send only after the sale commits, so no bank call happens while rows are locked.
        $id = $w->id;
        DB::afterCommit(fn () => self::send($id));
    }

    public static function send(int $withdrawalId): void
    {
        $w = Withdrawal::find($withdrawalId);
        if (!$w || $w->status !== 'pending') {
            return;
        }
        $secret = config('services.paystack.secret');
        if (empty($secret)) {
            Log::error('Payout not sent: Paystack secret key is not set. ' . $w->tx_ref);
            return;
        }

        // Step 1: create the recipient. If this fails, nothing has left the platform, so refund the wallet.
        try {
            $rec = Http::withToken($secret)->timeout(30)->post('https://api.paystack.co/transferrecipient', [
                'type' => 'nuban',
                'name' => $w->account_name ?: 'Seller',
                'account_number' => $w->account_number,
                'bank_code' => $w->bank_code,
                'currency' => 'NGN',
            ])->json();
            $code = $rec['data']['recipient_code'] ?? null;
        } catch (\Throwable $e) {
            $code = null;
        }
        if (!$code) {
            app(WalletActionController::class)->reverseWithdrawal($w->tx_ref, 'Payout could not be set up - refunded to wallet');
            return;
        }

        // Step 2: send the transfer. A rejection means nothing moved, so refund.
        // A timeout may mean the money moved, so it stays pending for the webhook to settle.
        try {
            $tr = Http::withToken($secret)->timeout(30)->post('https://api.paystack.co/transfer', [
                'source' => 'balance',
                'amount' => (int) round((float) $w->amount * 100),
                'recipient' => $code,
                'reference' => $w->tx_ref,
                'reason' => 'NAURVEX payout',
            ])->json();
        } catch (\Throwable $e) {
            Log::warning('Payout outcome unknown, left pending: ' . $w->tx_ref . ' ' . $e->getMessage());
            return;
        }
        if (($tr['status'] ?? false) !== true) {
            app(WalletActionController::class)->reverseWithdrawal($w->tx_ref, 'Payout was rejected - refunded to wallet');
        }
    }
}
