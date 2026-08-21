<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WalletTopup;
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

        if (($payload['event'] ?? '') !== 'charge.completed') {
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

        // Safety net: reconcile subscription payments in case the browser
        // redirect callback never fired (tab closed, connection lost, etc.)
        $txRef = $data['tx_ref'] ?? null;
        if ($txRef && str_starts_with($txRef, 'SUBSCRIBE-')) {
            DB::transaction(function () use ($txRef, $amount) {
                $sub = Subscription::where('flutterwave_tx_ref', $txRef)->lockForUpdate()->first();
                if ($sub && $sub->status === 'pending_payment' && $amount >= (float) $sub->amount) {
                    $sub->activate();
                }
            });
            return response('OK', 200);
        }

        // Wallet top-up initiated from WalletActionController::initiateTopup()
        if ($txRef && str_starts_with($txRef, 'TOPUP-')) {
            $this->creditWallet($flwId, $amount, null, $data['customer']['email'] ?? null);
            return response('OK', 200);
        }

        // Fallback: direct bank transfer into a user's dedicated virtual account
        $accountNumber = $data['meta']['originator_account_number']
            ?? $data['account_number']
            ?? null;

        $this->creditWallet($flwId, $amount, $data['account_id'] ?? $accountNumber, $data['customer']['email'] ?? null);

        return response('OK', 200);
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

        DB::transaction(function () use ($ref, $amount, $user) {
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
        });
    }
}
