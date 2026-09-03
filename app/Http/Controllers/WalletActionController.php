<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WalletActionController extends Controller
{
    // Was: topup() directly credited wallet_balance from a user-submitted
    // amount with no payment behind it - anyone could give themselves
    // unlimited money. Replaced with a real Flutterwave charge; the wallet
    // is only credited once FlutterwaveWebhookController verifies the
    // 'TOPUP-' tx_ref via webhook.
    public function initiateTopup(Request $request)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:100']);
        $user = Auth::user();

        if ($user->wouldExceedNoBvnCap($validated['amount'])) {
            $remaining = max(0, \App\Models\User::NO_BVN_WALLET_CAP - $user->wallet_balance);
            return back()->withErrors(['amount' => 'Unverified wallets are capped at ₦' . number_format(\App\Models\User::NO_BVN_WALLET_CAP) . '. You can add up to ₦' . number_format($remaining) . ' more, or verify your BVN to remove this limit.']);
        }

        $txRef = 'TOPUP-' . $user->id . '-' . time();

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref' => $txRef,
                'amount' => $validated['amount'],
                'currency' => 'NGN',
                'redirect_url' => route('wallet.index'),
                'customer' => ['email' => $user->email, 'name' => $user->name],
                'customizations' => ['title' => 'Qelvo Wallet Top-up'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            return redirect($data['data']['link']);
        }

        return back()->withErrors(['amount' => 'Could not start payment. Please try again.']);
    }

    // No-BVN accounts pay a 1.5% withdrawal fee; verified (virtual account / BVN)
    // accounts withdraw free. This is the only friction non-BVN users face -
    // withdrawals are never blocked, just costlier, matching the deposit cap logic.
    const NO_BVN_WITHDRAWAL_FEE_RATE = 0.015;

    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'pin' => 'required|string',
        ]);
        $user = Auth::user();

        if (!$user->hasTransactionPin()) {
            return back()->withErrors(['pin' => 'Set a transaction PIN before withdrawing.']);
        }
        if (!$user->verifyTransactionPin($validated['pin'])) {
            return back()->withErrors(['pin' => 'Incorrect transaction PIN.']);
        }

        $userId = Auth::id();
        $hasVirtualAccount = (bool) $user->virtual_account_number;
        $fee = $hasVirtualAccount ? 0 : round($validated['amount'] * self::NO_BVN_WITHDRAWAL_FEE_RATE, 2);
        $totalDebit = $validated['amount'] + $fee;

        $result = DB::transaction(function () use ($userId, $validated, $fee, $totalDebit) {
            $lockedUser = User::where('id', $userId)->lockForUpdate()->first();

            if ($lockedUser->wallet_balance < $totalDebit) {
                return ['ok' => false];
            }

            $lockedUser->decrement('wallet_balance', $totalDebit);
            Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'withdrawal',
                'amount' => -$validated['amount'],
                'description' => 'Withdrawal',
            ]);

            if ($fee > 0) {
                Transaction::create([
                    'user_id' => $lockedUser->id,
                    'type' => 'withdrawal_fee',
                    'amount' => -$fee,
                    'description' => 'No-BVN withdrawal fee (1.5%)',
                ]);
            }

            return ['ok' => true];
        });

        if (!$result['ok']) {
            $msg = $fee > 0
                ? 'Insufficient balance. You need ₦' . number_format($totalDebit, 2) . ' (amount + 1.5% no-BVN fee).'
                : 'Insufficient balance.';
            return back()->withErrors(['amount' => $msg]);
        }

        $statusMsg = $fee > 0
            ? 'Withdrawal requested. A 1.5% no-BVN fee (₦' . number_format($fee, 2) . ') was applied - verify your BVN to withdraw free.'
            : 'Withdrawal requested.';

        return redirect()->route('wallet.index')->with('status', $statusMsg);
    }
}
