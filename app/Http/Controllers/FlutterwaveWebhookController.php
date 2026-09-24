<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WalletTopup;
use App\Models\Withdrawal;
use App\Models\Subscription;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('verif-hash') ?? '';
        $expected = config('services.flutterwave.webhook_secret') ?? '';

        if ($expected === '' || !hash_equals($expected, $signature)) {
            return response('Unauthorized', 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? '';

        if ($event === 'transfer.completed') {
            $this->handleTransferCompleted($payload['data'] ?? []);
            return response('OK', 200);
        }

        if ($event !== 'charge.completed') {
            return response('Ignored', 200);
        }

        $data = $payload['data'] ?? [];

        if (($data['status'] ?? '') !== 'successful') {
            return response('Not successful', 200);
        }

        if (($data['currency'] ?? null) !== 'NGN') {
            return response('Currency mismatch', 200);
        }

        $flwId = $data['id'] ?? null;
        $amount = (float) ($data['amount'] ?? 0);

        if (!$flwId || $amount <= 0) {
            return response('Missing data', 200);
        }

        $txRef = $data['tx_ref'] ?? null;

        // Safety net: reconcile subscription payments in case the browser
        // redirect callback never fired (tab closed, connection lost, etc.)
        if ($txRef && str_starts_with($txRef, 'SUBSCRIBE-')) {
            DB::transaction(function () use ($txRef, $amount) {
                $sub = Subscription::where('flutterwave_tx_ref', $txRef)->lockForUpdate()->first();
                if ($sub && $sub->status === 'pending_payment' && $amount >= (float) $sub->amount) {
                    $sub->activate();
                }
            });
            return response('OK', 200);
        }

        // Wallet top-up initiated from FlutterwaveController::initiate() -
        // safety net in case the browser redirect back to callback() never
        // fired. Reuses the SAME WalletTopup row created at initiate() time
        // (looked up by tx_ref) instead of creating a new one, so whichever
        // of callback()/this webhook lands first is the one that credits -
        // the other sees status already 'successful' and no-ops.
        if ($txRef && str_starts_with($txRef, 'QELVO-')) {
            $this->settleTopupByRef($txRef, $amount);
            return response('OK', 200);
        }

        // Signup fee (account activation) - mark user paid, do NOT credit wallet
        if ($txRef && str_starts_with($txRef, 'SIGNUPFEE-')) {
            $email = $data['customer']['email'] ?? null;
            if ($email) {
                DB::transaction(function () use ($email, $flwId, $amount) {
                    $user = User::where('email', $email)->lockForUpdate()->first();
                    if ($user && !$user->signup_fee_paid) {
                        $user->signup_fee_paid = 1;
                        $user->save();
                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'signup_fee',
                            'amount' => -$amount,
                        ]);
                    }
                });
            }
            return response('OK', 200);
        }

        // Fallback: direct bank transfer into a user's dedicated virtual account
        // IMPORTANT: must match the RECEIVING account (the one we issued the
        // customer), not the originator/sender account — those are different
        // fields in Flutterwave's payload.
        $accountNumber = $data['meta']['virtualaccountnumber']
            ?? $data['meta']['virtual_account_number']
            ?? null;

        $this->creditWallet($flwId, $amount, $data['account_id'] ?? $accountNumber, $data['customer']['email'] ?? null);

        return response('OK', 200);
    }

    // Settles a withdrawal initiated by WalletActionController::withdraw().
    // Wallet was already debited at request time; a SUCCESSFUL transfer
    // needs no further action, a FAILED (or otherwise non-successful)
    // transfer refunds the user.
    private function handleTransferCompleted(array $data): void
    {
        $reference = $data['reference'] ?? null;
        $status = strtoupper($data['status'] ?? '');
        if (!$reference) {
            return;
        }

        if ($status === 'SUCCESSFUL') {
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

    // Settles the WalletTopup row created by FlutterwaveController::initiate().
    // Mirrors FlutterwaveController::callback()'s crediting logic exactly, so
    // whichever path (redirect callback or this webhook) processes the
    // payment first "wins" and the other is a safe no-op.
    private function settleTopupByRef(string $txRef, float $amount): void
    {
        $topup = WalletTopup::where('tx_ref', $txRef)->first();
        if (!$topup) {
            return;
        }

        $notifyUser = null;
        $notifyAmount = null;

        DB::transaction(function () use ($topup, $amount, &$notifyUser, &$notifyAmount) {
            $lockedTopup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($lockedTopup->status === 'successful') {
                return; // already credited by the redirect callback
            }
            if ($amount < (float) $lockedTopup->amount) {
                return; // amount paid doesn't cover what was requested - don't credit
            }

            $user = User::where('id', $lockedTopup->user_id)->lockForUpdate()->first();

            $lockedTopup->update(['status' => 'successful']);
            $user->increment('wallet_balance', $lockedTopup->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'wallet_topup',
                'amount' => $lockedTopup->amount,
                'description' => 'Wallet top up via Flutterwave',
            ]);

            $notifyUser = $user;
            $notifyAmount = $lockedTopup->amount;
        });

        if ($notifyUser) {
            try {
                $notifyUser->notify(new \App\Notifications\WalletTopupCredited($notifyAmount));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('WalletTopupCredited notify failed: ' . $e->getMessage());
            }
        }
    }

    // Idempotency relies on a UNIQUE index on wallet_topups.tx_ref -
    // check that migration exists; the try/catch below is the real
    // guarantee against double-crediting if two webhook deliveries race.
    private function creditWallet(string $flwId, float $amount, ?string $accountNumber, ?string $email = null): void
    {
        $ref = 'WEBHOOK-' . $flwId;

        $user = $accountNumber ? User::where('virtual_account_number', $accountNumber)->first() : null;
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }
        if (!$user) {
            return;
        }

        DB::transaction(function () use ($ref, $amount, $user, &$notifyUser) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            try {
                WalletTopup::create([
                    'user_id' => $lockedUser->id,
                    'tx_ref' => $ref,
                    'amount' => $amount,
                    'status' => 'successful',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // tx_ref already processed - Flutterwave retried delivery, ignore
                return;
            }

            $lockedUser->increment('wallet_balance', $amount);

            Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'wallet_topup',
                'amount' => $amount,
                'description' => 'Wallet funded via Flutterwave',
            ]);

            $notifyUser = $lockedUser;
        });

        if (isset($notifyUser)) {
            try {
                $notifyUser->notify(new \App\Notifications\WalletTopupCredited($amount));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('WalletTopupCredited notify failed: ' . $e->getMessage());
            }
        }
    }
}
