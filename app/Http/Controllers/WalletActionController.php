<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WalletActionController extends Controller
{
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
        if (!$user->payout_account_number) {
            return back()->withErrors(['amount' => 'Add a payout bank account before withdrawing.']);
        }

        $hasVirtualAccount = (bool) $user->virtual_account_number;
        $fee = $hasVirtualAccount ? 0 : round($validated['amount'] * self::NO_BVN_WITHDRAWAL_FEE_RATE, 2);
        $totalDebit = $validated['amount'] + $fee;
        $txRef = 'WD-' . $user->id . '-' . time();

        // Debit immediately so the funds can't be double-spent while the
        // transfer is in flight. If the Paystack call fails to even
        // initiate, we reverse this below. If it initiates but the bank
        // payout later fails, the transfer.completed webhook reverses it.
        $debited = DB::transaction(function () use ($user, $validated, $fee, $totalDebit, $txRef) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser->wallet_restricted) {
                return false;
            }

            if ($lockedUser->wallet_balance < $totalDebit) {
                return false;
            }

            $lockedUser->decrement('wallet_balance', $totalDebit);

            Withdrawal::create([
                'user_id' => $lockedUser->id,
                'tx_ref' => $txRef,
                'amount' => $validated['amount'],
                'fee' => $fee,
                'bank_code' => $lockedUser->payout_bank_code,
                'bank_name' => $lockedUser->payout_bank_name,
                'account_number' => $lockedUser->payout_account_number,
                'account_name' => $lockedUser->payout_account_name,
                'status' => 'pending',
            ]);

            Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'withdrawal',
                'amount' => -$validated['amount'],
                'description' => 'Withdrawal to ' . $lockedUser->payout_bank_name . ' ****' . substr($lockedUser->payout_account_number, -4),
            ]);

            if ($fee > 0) {
                Transaction::create([
                    'user_id' => $lockedUser->id,
                    'type' => 'withdrawal_fee',
                    'amount' => -$fee,
                    'description' => 'No-BVN withdrawal fee (1.5%)',
                ]);
            }

            return true;
        });

        if (!$debited) {
            $restricted = User::where('id', $user->id)->value('wallet_restricted');
            if ($restricted) {
                return back()->withErrors(['amount' => 'Your wallet is restricted due to an unresolved payment issue. Clear your balance to make withdrawals.']);
            }
            $msg = $fee > 0
                ? 'Insufficient balance. You need ₦' . number_format($totalDebit, 2) . ' (amount + 1.5% no-BVN fee).'
                : 'Insufficient balance.';
            return back()->withErrors(['amount' => $msg]);
        }

        try {
            $secret = config('services.paystack.secret');

            // Paystack requires a transfer recipient to exist before you can pay it.
            $recipientRes = Http::withToken($secret)->post('https://api.paystack.co/transferrecipient', [
                'type' => 'nuban',
                'name' => $user->payout_account_name,
                'account_number' => $user->payout_account_number,
                'bank_code' => $user->payout_bank_code,
                'currency' => 'NGN',
            ]);
            $recipientData = $recipientRes->json();
            $recipientCode = $recipientData['data']['recipient_code'] ?? null;

            if (!$recipientCode) {
                $initiated = false;
            } else {
                $response = Http::withToken($secret)->post('https://api.paystack.co/transfer', [
                    'source' => 'balance',
                    'amount' => (int) round($validated['amount'] * 100),
                    'recipient' => $recipientCode,
                    'reference' => $txRef,
                    'reason' => 'NOURVEX withdrawal',
                ]);
                $data = $response->json();
                $initiated = ($data['status'] ?? false) === true && !in_array($data['data']['status'] ?? '', ['otp', 'failed', 'reversed'], true);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Withdrawal transfer init failed: ' . $e->getMessage());
            $initiated = false;
        }

        if (!$initiated) {
            $this->reverseWithdrawal($txRef, 'Withdrawal could not be started - refunded to wallet');
            return back()->withErrors(['amount' => 'Could not start the transfer. Your wallet has not been charged.']);
        }

        return redirect()->route('wallet.index')->with('status', 'Withdrawal of ₦' . number_format($validated['amount'], 2) . ' is processing.');
    }

    public function reverseWithdrawal(string $txRef, string $reason): void
    {
        DB::transaction(function () use ($txRef, $reason) {
            $withdrawal = Withdrawal::where('tx_ref', $txRef)->lockForUpdate()->first();
            if (!$withdrawal || $withdrawal->status !== 'pending') {
                return;
            }

            $withdrawal->update(['status' => 'failed']);
            $user = User::where('id', $withdrawal->user_id)->lockForUpdate()->first();
            $refundAmount = (float) $withdrawal->amount + (float) $withdrawal->fee;
            $user->increment('wallet_balance', $refundAmount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_reversal',
                'amount' => $refundAmount,
                'description' => $reason,
            ]);
        });
    }
}
