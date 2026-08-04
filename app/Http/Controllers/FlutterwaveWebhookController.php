<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WalletTopup;
use App\Models\Subscription;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('verif-hash');

        if (!$signature || $signature !== env('FLW_WEBHOOK_SECRET')) {
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

        // Safety net: reconcile subscription payments in case the browser
        // redirect callback never fired (tab closed, connection lost, etc.)
        $txRef = $data['tx_ref'] ?? null;
        if ($txRef && str_starts_with($txRef, 'SUBSCRIBE-')) {
            $sub = Subscription::where('flutterwave_tx_ref', $txRef)->first();
            if ($sub && $sub->status === 'pending_payment' && (float) ($data['amount'] ?? 0) >= (float) $sub->amount) {
                $sub->activate();
            }
            return response('OK', 200);
        }

        $accountNumber = $data['meta']['originator_account_number']
            ?? $data['account_number']
            ?? null;

        $flwId = $data['id'] ?? null;
        $amount = $data['amount'] ?? 0;

        if (!$flwId || $amount <= 0) {
            return response('Missing data', 200);
        }

        // Idempotency: never credit the same Flutterwave transaction twice
        $existing = WalletTopup::where('tx_ref', 'WEBHOOK-' . $flwId)->first();
        if ($existing) {
            return response('Already processed', 200);
        }

        $user = User::where('virtual_account_number', $data['account_id'] ?? $accountNumber)->first();
        if (!$user) {
            $user = User::where('email', $data['customer']['email'] ?? '__none__')->first();
        }

        if (!$user) {
            return response('User not found', 200);
        }

        WalletTopup::create([
            'user_id' => $user->id,
            'tx_ref' => 'WEBHOOK-' . $flwId,
            'amount' => $amount,
            'status' => 'successful',
        ]);

        $user->increment('wallet_balance', $amount);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'wallet_topup',
            'amount' => $amount,
            'description' => 'Wallet funded via virtual account transfer',
        ]);

        return response('OK', 200);
    }
}
