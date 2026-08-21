<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\WalletTopup;
use App\Models\Transaction;
use App\Models\User;

class FlutterwaveController extends Controller
{
    private function flwSecret(): string
    {
        return config('services.flutterwave.secret');
    }

    private function verifyTransaction(string $transactionId): array
    {
        return Http::withToken($this->flwSecret())
            ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify")
            ->json();
    }

    public function initiateVendor(Request $request)
    {
        $user = Auth::user();
        $amount = 5000;
        $txRef = 'VENDORREG-' . $user->id . '-' . time();

        WalletTopup::create([
            'user_id' => $user->id,
            'tx_ref' => $txRef,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $response = Http::withToken($this->flwSecret())
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => 'NGN',
                'redirect_url' => route('vendor.callback'),
                'customer' => ['email' => $user->email, 'name' => $user->name],
                'customizations' => ['title' => 'Qelvo Vendor Registration'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            return redirect($data['data']['link']);
        }

        return back()->withErrors(['payment' => 'Could not start payment. Please try again.']);
    }

    public function vendorCallback(Request $request)
    {
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        $topup = WalletTopup::where('tx_ref', $txRef)->first();

        if (!$topup || $topup->status === 'successful') {
            return redirect()->route('dashboard');
        }

        if ($status !== 'successful') {
            $topup->update(['status' => 'failed']);
            return redirect()->route('vendor.activate')->withErrors(['payment' => 'Payment was not completed.']);
        }

        $transactionId = $request->query('transaction_id');
        $verifyData = $this->verifyTransaction($transactionId);

        $verified = ($verifyData['status'] ?? null) === 'success'
            && ($verifyData['data']['status'] ?? null) === 'successful'
            && ($verifyData['data']['tx_ref'] ?? null) === $txRef
            && (float) ($verifyData['data']['amount'] ?? 0) >= (float) $topup->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            $topup->update(['status' => 'failed']);
            return redirect()->route('vendor.activate')->withErrors(['payment' => 'Payment verification failed.']);
        }

        DB::transaction(function () use ($topup) {
            $topup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($topup->status === 'successful') {
                return; // already processed by a concurrent request
            }

            $user = User::where('id', $topup->user_id)->lockForUpdate()->first();

            $topup->update(['status' => 'successful']);
            $user->update(['tier' => 'tier1', 'tier_upgraded_at' => now(), 'is_vendor_active' => true]);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'vendor_registration',
                'amount' => -5000,
                'description' => 'Vendor registration fee',
            ]);

            if ($user->referred_by) {
                $referrer = User::where('id', $user->referred_by)->lockForUpdate()->first();
                if ($referrer) {
                    $pct = $referrer->tier === 'tier2' ? 0.15 : 0;
                    $bonus = 5000 * $pct;
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
        });

        return redirect()->route('onboarding.interests')->with('status', 'Vendor account activated!');
    }

    public function initiateSignupFee(Request $request)
    {
        $user = Auth::user();

        if ($user->signup_fee_paid) {
            return redirect()->route('dashboard');
        }

        $amount = 2000; // Starter tier — corrected from old ₦1,000 base fee
        $txRef = 'SIGNUPFEE-' . $user->id . '-' . time();

        WalletTopup::create([
            'user_id' => $user->id,
            'tx_ref' => $txRef,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $response = Http::withToken($this->flwSecret())
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => 'NGN',
                'redirect_url' => route('signup-fee.callback'),
                'customer' => ['email' => $user->email, 'name' => $user->name],
                'customizations' => ['title' => 'Qelvo Account Activation'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            return redirect($data['data']['link']);
        }

        return back()->withErrors(['payment' => 'Could not start payment. Please try again.']);
    }

    public function signupFeeCallback(Request $request)
    {
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        $topup = WalletTopup::where('tx_ref', $txRef)->first();

        if (!$topup || $topup->status === 'successful') {
            return redirect()->route('dashboard');
        }

        if ($status !== 'successful') {
            $topup->update(['status' => 'failed']);
            return redirect()->route('signup-fee.initiate')->withErrors(['payment' => 'Payment was not completed.']);
        }

        $transactionId = $request->query('transaction_id');
        $verifyData = $this->verifyTransaction($transactionId);

        $verified = ($verifyData['status'] ?? null) === 'success'
            && ($verifyData['data']['status'] ?? null) === 'successful'
            && ($verifyData['data']['tx_ref'] ?? null) === $txRef
            && (float) ($verifyData['data']['amount'] ?? 0) >= (float) $topup->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            $topup->update(['status' => 'failed']);
            return redirect()->route('signup-fee.initiate')->withErrors(['payment' => 'Payment verification failed.']);
        }

        $settledCount = 0;

        DB::transaction(function () use ($topup, &$settledCount) {
            $topup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($topup->status === 'successful') {
                return;
            }

            $user = User::where('id', $topup->user_id)->lockForUpdate()->first();

            $topup->update(['status' => 'successful']);

            $cardUpdate = [
                'signup_fee_paid' => true,
                'badge_tier' => 'bronze',
            ];
            if ($user->tier === 'free') {
                $cardUpdate['tier'] = 'tier1';
                $cardUpdate['tier_upgraded_at'] = now();
            }
            $card = $verifyData['data']['card'] ?? null;
            if ($card && !empty($card['token'])) {
                $cardUpdate['card_token'] = $card['token'];
                $cardUpdate['card_last4'] = $card['last_4digits'] ?? null;
                $cardUpdate['card_type'] = $card['type'] ?? null;
            }
            $user->update($cardUpdate);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'signup_fee',
                'amount' => -2000,
                'description' => 'Account activation fee',
            ]);

            $guestToken = request()->cookie('qelvo_guest_token');
            if ($guestToken) {
                $settledCount = app(\App\Http\Controllers\GigController::class)->settleGuestGigs($user, $guestToken);
            }
        });

        $msg = 'Account activated!' . ($settledCount > 0 ? ' ' . $settledCount . ' held gig payment(s) released to your wallet.' : '');
        return redirect()->route('onboarding.interests')->with('status', $msg);
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $user = Auth::user();
        $txRef = 'QELVO-' . $user->id . '-' . time();

        WalletTopup::create([
            'user_id' => $user->id,
            'tx_ref' => $txRef,
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        $response = Http::withToken($this->flwSecret())
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref' => $txRef,
                'amount' => $validated['amount'],
                'currency' => 'NGN',
                'redirect_url' => route('flutterwave.callback'),
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'customizations' => [
                    'title' => 'Qelvo Wallet Top Up',
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            return redirect($data['data']['link']);
        }

        return back()->withErrors(['topup' => 'Could not start payment. Please try again.']);
    }

    public function callback(Request $request)
    {
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        $topup = WalletTopup::where('tx_ref', $txRef)->first();

        if (!$topup) {
            return redirect()->route('wallet.index')->withErrors(['topup' => 'Transaction not found.']);
        }

        if ($topup->status === 'successful') {
            return redirect()->route('wallet.index')->with('status', 'Payment already processed.');
        }

        if ($status !== 'successful') {
            $topup->update(['status' => 'failed']);
            return redirect()->route('wallet.index')->withErrors(['topup' => 'Payment was not completed.']);
        }

        // Always verify server-side against Flutterwave before crediting - never trust the redirect alone
        $transactionId = $request->query('transaction_id');
        $verifyData = $this->verifyTransaction($transactionId);

        $verified = ($verifyData['status'] ?? null) === 'success'
            && ($verifyData['data']['status'] ?? null) === 'successful'
            && ($verifyData['data']['tx_ref'] ?? null) === $txRef
            && (float) ($verifyData['data']['amount'] ?? 0) >= (float) $topup->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            $topup->update(['status' => 'failed']);
            return redirect()->route('wallet.index')->withErrors(['topup' => 'Payment verification failed.']);
        }

        DB::transaction(function () use ($topup) {
            $topup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($topup->status === 'successful') {
                return;
            }

            $user = User::where('id', $topup->user_id)->lockForUpdate()->first();

            $topup->update(['status' => 'successful']);
            $user->increment('wallet_balance', $topup->amount);

            Transaction::create([
                'user_id' => $topup->user_id,
                'type' => 'wallet_topup',
                'amount' => $topup->amount,
                'description' => 'Wallet top up via Flutterwave',
            ]);
        });

        return redirect()->route('wallet.index')->with('status', 'Wallet topped up successfully!');
    }
}
