<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\WalletTopup;
use App\Models\Transaction;
use App\Models\User;

class PaystackController extends Controller
{
    private function paystackSecret(): string
    {
        return config('services.paystack.secret') ?? '';
    }

    private function initializeTransaction(string $reference, float $amountNaira, string $email, string $callbackUrl, array $metadata = []): array
    {
        return Http::withToken($this->paystackSecret())
            ->post('https://api.paystack.co/transaction/initialize', [
                'reference' => $reference,
                'amount' => (int) round($amountNaira * 100),
                'currency' => 'NGN',
                'email' => $email,
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
            ])->json();
    }

    private function verifyTransaction(string $reference): array
    {
        return Http::withToken($this->paystackSecret())
            ->get("https://api.paystack.co/transaction/verify/{$reference}")
            ->json();
    }

    public function initiateVendor(Request $request)
    {
        $user = Auth::user();
        $amount = 5000;
        $txRef = 'VENDORREG-' . $user->id . '-' . time();

        WalletTopup::create(['user_id' => $user->id, 'tx_ref' => $txRef, 'amount' => $amount, 'status' => 'pending']);

        $data = $this->initializeTransaction($txRef, $amount, $user->email, route('vendor.callback'), ['title' => 'NOURVEX Vendor Registration']);

        if (($data['status'] ?? false) === true) {
            return redirect($data['data']['authorization_url']);
        }
        return back()->withErrors(['payment' => 'Could not start payment. Please try again.']);
    }

    public function vendorCallback(Request $request)
    {
        $txRef = $request->query('reference') ?? $request->query('trxref');
        $topup = WalletTopup::where('tx_ref', $txRef)->first();

        if (!$topup || $topup->status === 'successful') {
            return redirect()->route('dashboard');
        }

        $verifyData = $this->verifyTransaction($txRef);
        $verified = ($verifyData['status'] ?? false) === true
            && ($verifyData['data']['status'] ?? null) === 'success'
            && ($verifyData['data']['reference'] ?? null) === $txRef
            && ((float) ($verifyData['data']['amount'] ?? 0)) / 100 >= (float) $topup->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            $topup->update(['status' => 'failed']);
            return redirect()->route('vendor.activate')->withErrors(['payment' => 'Payment verification failed.']);
        }

        DB::transaction(function () use ($topup) {
            $topup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($topup->status === 'successful') return;

            $user = User::where('id', $topup->user_id)->lockForUpdate()->first();
            $topup->update(['status' => 'successful']);
            $user->update(['tier' => 'tier1', 'tier_upgraded_at' => now(), 'is_vendor_active' => true]);

            Transaction::create(['user_id' => $user->id, 'type' => 'vendor_registration', 'amount' => -5000, 'description' => 'Vendor registration fee']);

            if ($user->referred_by) {
                $referrer = User::where('id', $user->referred_by)->lockForUpdate()->first();
                if ($referrer && !$referrer->wallet_restricted) {
                    $pct = $referrer->tier === 'tier2' ? 0.15 : 0;
                    $bonus = 5000 * $pct;
                    if ($bonus > 0) {
                        $referrer->increment('wallet_balance', $bonus);
                        Transaction::create(['user_id' => $referrer->id, 'type' => 'referral_bonus', 'amount' => $bonus, 'description' => 'Referral bonus from ' . $user->name . ' vendor registration']);
                    }
                }
            }
        });

        return redirect()->route('onboarding.interests')->with('status', 'Vendor account activated!');
    }

    public function initiateSignupFee(Request $request)
    {
        $user = Auth::user();
        if ($user->signup_fee_paid) return redirect()->route('dashboard');

        $amount = 2000;
        $txRef = 'SIGNUPFEE-' . $user->id . '-' . time();

        WalletTopup::create(['user_id' => $user->id, 'tx_ref' => $txRef, 'amount' => $amount, 'status' => 'pending']);

        try {
            $data = $this->initializeTransaction($txRef, $amount, $user->email, route('signup-fee.callback'), ['title' => 'NOURVEX Account Activation']);
            if (($data['status'] ?? false) === true) {
                return redirect($data['data']['authorization_url']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Signup fee payment init failed: ' . $e->getMessage());
        }

        return back()->withErrors(['payment' => 'Could not start payment. Please try again.']);
    }

    public function signupFeeCallback(Request $request)
    {
        $txRef = $request->query('reference') ?? $request->query('trxref');
        $user = User::where('id', auth()->id())->first();

        if (!$user || $user->signup_fee_paid) return redirect()->route('dashboard');

        $feeTopup = WalletTopup::where('tx_ref', $txRef)->where('user_id', $user->id)->first();
        if (!$feeTopup || !str_starts_with((string) $txRef, 'SIGNUPFEE-')) {
            return redirect()->route('signup-fee.activate')->withErrors(['payment' => 'Payment reference not recognised. Please try again.']);
        }

        $verifyData = $this->verifyTransaction($txRef);
        $verified = ($verifyData['status'] ?? false) === true
            && ($verifyData['data']['status'] ?? null) === 'success'
            && ($verifyData['data']['reference'] ?? null) === $txRef
            && ((float) ($verifyData['data']['amount'] ?? 0)) / 100 >= (float) $feeTopup->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            return redirect()->route('signup-fee.activate')->withErrors(['payment' => 'Payment not confirmed yet. If you were charged, your account activates automatically within a few minutes.']);
        }

        $settledCount = 0;

        DB::transaction(function () use ($user, $verifyData, $feeTopup, &$settledCount) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            if ($lockedUser->signup_fee_paid) return;

            $update = ['signup_fee_paid' => true, 'badge_tier' => 'bronze'];
            if ($lockedUser->tier === 'free') {
                $update['tier'] = 'tier1';
                $update['tier_upgraded_at'] = now();
            }
            $auth = $verifyData['data']['authorization'] ?? null;
            if ($auth && !empty($auth['authorization_code'])) {
                $update['card_token'] = $auth['authorization_code'];
                $update['card_last4'] = $auth['last4'] ?? null;
                $update['card_type'] = $auth['card_type'] ?? null;
            }
            $lockedUser->update($update);
            $feeTopup->update(['status' => 'successful']);

            Transaction::create(['user_id' => $lockedUser->id, 'type' => 'signup_fee', 'amount' => -((float) ($verifyData['data']['amount'] ?? 10000)) / 100, 'description' => 'Account activation via Paystack']);

            $guestToken = request()->cookie('qelvo_guest_token');
            if ($guestToken) {
                $settledCount = app(\App\Http\Controllers\GigController::class)->settleGuestGigs($lockedUser, $guestToken);
            }
        });

        $msg = 'Account activated!' . ($settledCount > 0 ? ' ' . $settledCount . ' held gig payment(s) released to your wallet.' : '');
        return redirect()->route('onboarding.interests')->with('status', $msg);
    }

    public function initiateRegistrationFee(Request $request)
    {
        $encrypted = $request->session()->get('pending_registration');
        if (!$encrypted) {
            return redirect()->route('register')->withErrors(['email' => 'Your registration session expired. Please fill the form again.']);
        }
        try {
            $pending = unserialize(\Illuminate\Support\Facades\Crypt::decryptString($encrypted));
        } catch (\Throwable $e) {
            return redirect()->route('register')->withErrors(['email' => 'Your registration session expired. Please fill the form again.']);
        }

        $amount = 2000;
        $txRef = 'REGFEE-' . $request->session()->getId() . '-' . time();
        $request->session()->put('pending_registration_txref', $txRef);

        try {
            $data = $this->initializeTransaction($txRef, $amount, $pending['email'], route('register.pay.callback'), ['title' => 'Campulsy Account Activation']);
            if (($data['status'] ?? false) === true) {
                return redirect($data['data']['authorization_url']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Registration fee payment init failed: ' . $e->getMessage());
        }

        return back()->withErrors(['payment' => 'Could not start payment. Please try again.']);
    }

    public function registrationFeeCallback(Request $request)
    {
        $txRef = $request->query('reference') ?? $request->query('trxref');

        $encrypted = $request->session()->get('pending_registration');
        $expectedTxRef = $request->session()->get('pending_registration_txref');

        if (!$encrypted || !$expectedTxRef || $txRef !== $expectedTxRef || !str_starts_with((string) $txRef, 'REGFEE-')) {
            return redirect()->route('register')->withErrors(['email' => 'Your registration session expired or the payment reference did not match. Please fill the form again.']);
        }

        try {
            $pending = unserialize(\Illuminate\Support\Facades\Crypt::decryptString($encrypted));
        } catch (\Throwable $e) {
            return redirect()->route('register')->withErrors(['email' => 'Your registration session expired. Please fill the form again.']);
        }

        $verifyData = $this->verifyTransaction($txRef);
        $verified = ($verifyData['status'] ?? false) === true
            && ($verifyData['data']['status'] ?? null) === 'success'
            && ($verifyData['data']['reference'] ?? null) === $txRef
            && ((float) ($verifyData['data']['amount'] ?? 0)) / 100 >= 2000
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            return redirect()->route('register.pay')->withErrors(['payment' => 'Payment not confirmed yet. If you were charged, try refreshing this page in a minute before registering again.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            $request->session()->forget(['pending_registration', 'pending_registration_txref']);
            return redirect()->route('register')->withErrors(['email' => 'That email was registered by someone else while your payment was processing. Please register again — you have not been charged for this attempt without an account, contact support if needed.']);
        }

        $referrer = null;
        if (!empty($pending['referral_code_used'])) {
            $referrer = User::where('referral_code', $pending['referral_code_used'])->first();
        }

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => $pending['password'],
            'gender' => $pending['gender'],
            'spoken_language' => $pending['spoken_language'],
            'referral_code' => strtoupper(\Illuminate\Support\Str::random(6)),
            'referred_by' => $referrer?->id,
            'account_type' => 'guest',
            'signup_fee_paid' => true,
            'badge_tier' => 'bronze',
            'tier' => 'tier1',
            'tier_upgraded_at' => now(),
        ]);

        $auth = $verifyData['data']['authorization'] ?? null;
        if ($auth && !empty($auth['authorization_code'])) {
            $user->update([
                'card_token' => $auth['authorization_code'],
                'card_last4' => $auth['last4'] ?? null,
                'card_type' => $auth['card_type'] ?? null,
            ]);
        }

        Auth::login($user);

        WalletTopup::create(['user_id' => $user->id, 'tx_ref' => $txRef, 'amount' => 2000, 'status' => 'successful']);
        Transaction::create(['user_id' => $user->id, 'type' => 'signup_fee', 'amount' => -((float) ($verifyData['data']['amount'] ?? 200000)) / 100, 'description' => 'Account activation via Paystack']);

        if (!empty($pending['bvn']) && !empty($pending['phonenumber'])) {
            app(AuthController::class)->trySetupVirtualAccountPublic($user, $pending['phonenumber'], $pending['bvn']);
        }

        if ($pending['intended_plan']) {
            $request->session()->put('intended_plan_after_onboarding', $pending['intended_plan']);
        }

        $settledCount = 0;
        $guestToken = $request->cookie('qelvo_guest_token');
        if ($guestToken) {
            $settledCount = app(\App\Http\Controllers\GigController::class)->settleGuestGigs($user, $guestToken);
        }

        $request->session()->forget(['pending_registration', 'pending_registration_txref']);

        $msg = 'Welcome to Campulsy! Your account is active.' . ($settledCount > 0 ? ' ' . $settledCount . ' held gig payment(s) released to your wallet.' : '');
        return redirect()->route('onboarding.interests')->with('status', $msg);
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate(
            ['amount' => 'required|numeric|min:100|max:1000000'],
            ['amount.min' => 'Minimum top-up is ₦100.', 'amount.max' => 'Maximum single top-up is ₦1,000,000.']
        );
        $validated['amount'] = round((float) $validated['amount'], 2);
        $user = Auth::user();
        $txRef = 'QELVO-' . $user->id . '-' . time();

        WalletTopup::create(['user_id' => $user->id, 'tx_ref' => $txRef, 'amount' => $validated['amount'], 'status' => 'pending']);

        $data = [];
        try {
            $data = $this->initializeTransaction($txRef, $validated['amount'], $user->email, route('flutterwave.callback'), ['title' => 'NOURVEX Wallet Top Up']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Wallet top-up init failed: ' . $e->getMessage());
        }

        if (($data['status'] ?? false) === true) {
            return redirect($data['data']['authorization_url']);
        }
        return back()->withErrors(['topup' => 'Could not start payment. Please try again.']);
    }

    public function callback(Request $request)
    {
        $txRef = $request->query('reference') ?? $request->query('trxref');
        $topup = WalletTopup::where('tx_ref', $txRef)->first();

        if (!$topup || (int) $topup->user_id !== (int) Auth::id()) return redirect()->route('wallet.index')->withErrors(['topup' => 'Transaction not found.']);
        if ($topup->status === 'successful') return redirect()->route('dashboard')->with('status', 'Payment already processed.');

        $verifyData = $this->verifyTransaction($txRef);
        $verified = ($verifyData['status'] ?? false) === true
            && ($verifyData['data']['status'] ?? null) === 'success'
            && ($verifyData['data']['reference'] ?? null) === $txRef
            && ((float) ($verifyData['data']['amount'] ?? 0)) / 100 >= (float) $topup->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        if (!$verified) {
            $psStatus = $verifyData['data']['status'] ?? null;
            if (in_array($psStatus, ['failed', 'abandoned', 'reversed'], true)) {
                $topup->update(['status' => 'failed']);
            }
            return redirect()->route('wallet.index')->withErrors(['topup' => $psStatus === 'success'
                ? 'Payment verification failed.'
                : 'Payment not confirmed yet. If you were charged, your wallet is credited automatically within a few minutes.']);
        }

        DB::transaction(function () use ($topup) {
            $topup = WalletTopup::where('id', $topup->id)->lockForUpdate()->first();
            if ($topup->status === 'successful') return;

            $user = User::where('id', $topup->user_id)->lockForUpdate()->first();
            $topup->update(['status' => 'successful']);
            $user->increment('wallet_balance', $topup->amount);

            Transaction::create(['user_id' => $topup->user_id, 'type' => 'wallet_topup', 'amount' => $topup->amount, 'description' => 'Wallet top up via Paystack']);
        });

        try {
            $topup->user->notify(new \App\Notifications\WalletTopupCredited($topup->amount));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WalletTopupCredited notify failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('status', 'Wallet topped up successfully!');
    }
}
