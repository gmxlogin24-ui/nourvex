<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WalletTopup;
use App\Models\Withdrawal;
use App\Models\Subscription;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('x-paystack-signature') ?? '';
        $secret = config('services.paystack.secret') ?? '';
        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        if ($secret === '' || !hash_equals($expected, $signature)) {
            return response('Unauthorized', 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? '';

        if ($event === 'transfer.success' || $event === 'transfer.failed') {
            $this->handleTransferCompleted($payload['data'] ?? [], $event === 'transfer.success');
            return response('OK', 200);
        }

        if ($event !== 'charge.success') {
            return response('Ignored', 200);
        }

        $data = $payload['data'] ?? [];

        if (($data['status'] ?? '') !== 'success') {
            return response('Not successful', 200);
        }
        if (($data['currency'] ?? null) !== 'NGN') {
            return response('Currency mismatch', 200);
        }

        $paystackId = $data['id'] ?? null;
        $amount = (float) ($data['amount'] ?? 0) / 100;

        if (!$paystackId || $amount <= 0) {
            return response('Missing data', 200);
        }

        $txRef = $data['reference'] ?? null;

        if ($txRef && str_starts_with($txRef, 'SUBSCRIBE-')) {
            DB::transaction(function () use ($txRef, $amount) {
                $sub = Subscription::where('paystack_tx_ref', $txRef)->lockForUpdate()->first();
                if ($sub && $sub->status === 'pending_payment' && $amount >= (float) $sub->amount) {
                    $sub->activate();
                }
            });
            return response('OK', 200);
        }

        if ($txRef && str_starts_with($txRef, 'ORDER-')) {
            \App\Http\Controllers\OrderCheckoutController::settleByRef($txRef, $amount);
            return response('OK', 200);
        }

        if ($txRef && str_starts_with($txRef, 'QELVO-')) {
            $this->settleTopupByRef($txRef, $amount);
            return response('OK', 200);
        }

        if ($txRef && str_starts_with($txRef, 'VENDORREG-')) {
            // Vendor registration is settled by PaystackController@vendorCallback - never a wallet top-up.
            return response('OK', 200);
        }

        if ($txRef && str_starts_with($txRef, 'SIGNUPFEE-')) {
            $feeTopup = WalletTopup::where('tx_ref', $txRef)->first();
            if ($feeTopup && $amount >= (float) $feeTopup->amount) {
                DB::transaction(function () use ($feeTopup, $amount, $data) {
                    $lockedTopup = WalletTopup::where('id', $feeTopup->id)->lockForUpdate()->first();
                    if ($lockedTopup->status === 'successful') return;

                    $user = User::where('id', $lockedTopup->user_id)->lockForUpdate()->first();
                    if (!$user) return;

                    $lockedTopup->update(['status' => 'successful']);
                    if ($user->signup_fee_paid) return;

                    $update = ['signup_fee_paid' => true, 'badge_tier' => 'bronze'];
                    if ($user->tier === 'free') {
                        $update['tier'] = 'tier1';
                        $update['tier_upgraded_at'] = now();
                    }
                    $auth = $data['authorization'] ?? null;
                    if ($auth && !empty($auth['authorization_code'])) {
                        $update['card_token'] = $auth['authorization_code'];
                        $update['card_last4'] = $auth['last4'] ?? null;
                        $update['card_type'] = $auth['card_type'] ?? null;
                    }
                    $user->update($update);

                    Transaction::create(['user_id' => $user->id, 'type' => 'signup_fee', 'amount' => -$amount, 'description' => 'Account activation via Paystack']);
                });
            }
            return response('OK', 200);
        }

        // Dedicated Virtual Account credit
        $accountNumber = $data['authorization']['receiver_bank_account_number']
            ?? $data['metadata']['receiver_account_number']
            ?? null;

        $this->creditWallet((string) $paystackId, $amount, $accountNumber, $data['customer']['email'] ?? null);

        return response('OK', 200);
    }

    private function handleTransferCompleted(array $data, bool $successful): void
    {
        $reference = $data['reference'] ?? null;
        if (!$reference) return;

        if ($successful) {
            DB::transaction(function () use ($reference) {
                $withdrawal = Withdrawal::where('tx_ref', $reference)->lockForUpdate()->first();
                if ($withdrawal && $withdrawal->status === 'pending') {
                    $withdrawal->update(['status' => 'successful']);
                }
            });
            return;
        }

        app(WalletActionController::class)->reverseWithdrawal($reference, 'Withdrawal failed - refunded to wallet');
    }

    private function settleTopupByRef(string $txRef, float $amount): void
    {
        $topup = WalletTopup::where('tx_ref', $txRef)->first();
        if (!$topup) return;

        $notifyUser = null;

        DB::transaction(function () use ($topup, $amount, &$notifyUser) {
            $lockedTopup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($lockedTopup->status === 'successful') return;
            if ($amount < (float) $lockedTopup->amount) return;

            $user = User::where('id', $lockedTopup->user_id)->lockForUpdate()->first();
            $lockedTopup->update(['status' => 'successful']);
            $user->increment('wallet_balance', $lockedTopup->amount);

            Transaction::create(['user_id' => $user->id, 'type' => 'wallet_topup', 'amount' => $lockedTopup->amount, 'description' => 'Wallet top up via Paystack', 'reference' => $txRef, 'status' => 'successful']);
            $notifyUser = $user;
        });

        if ($notifyUser) {
            try {
                $notifyUser->notify(new \App\Notifications\WalletTopupCredited($amount));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('WalletTopupCredited notify failed: ' . $e->getMessage());
            }
        }
    }

    private function creditWallet(string $paystackId, float $amount, ?string $accountNumber, ?string $email = null): void
    {
        $ref = 'WEBHOOK-' . $paystackId;

        $user = $accountNumber ? User::where('virtual_account_number', $accountNumber)->first() : null;
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }
        if (!$user) return;

        $notifyUser = null;

        DB::transaction(function () use ($ref, $amount, $user, &$notifyUser) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            try {
                WalletTopup::create(['user_id' => $lockedUser->id, 'tx_ref' => $ref, 'amount' => $amount, 'status' => 'successful']);
            } catch (\Illuminate\Database\QueryException $e) {
                return;
            }
            $lockedUser->increment('wallet_balance', $amount);
            Transaction::create(['user_id' => $lockedUser->id, 'type' => 'wallet_topup', 'amount' => $amount, 'description' => 'Wallet funded via Paystack', 'reference' => $ref, 'status' => 'successful']);
            $notifyUser = $lockedUser;
        });

        if ($notifyUser) {
            try {
                $notifyUser->notify(new \App\Notifications\WalletTopupCredited($amount));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('WalletTopupCredited notify failed: ' . $e->getMessage());
            }
        }
    }
}
