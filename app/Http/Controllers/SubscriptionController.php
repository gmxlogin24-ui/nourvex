<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $current = Subscription::currentFor($user);

        return view('subscriptions.index', [
            'user' => $user,
            'plans' => Subscription::PLANS,
            'current' => $current,
        ]);
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'target_tier' => 'required|in:tier1,tier2,tier3',
            'payment_method' => 'required|in:wallet,paystack',
        ]);

        $user = Auth::user();
        $target = $validated['target_tier'];
        $method = $validated['payment_method'];

        $current = Subscription::currentFor($user);

        if ($current && $current->plan === $target) {
            return back()->withErrors(['tier' => 'You already have an active ' . strtoupper($target) . ' subscription until ' . $current->expires_at->format('M j, Y') . '.']);
        }

        $cost = Subscription::PLANS[$target]['price'];

        if ($method === 'paystack') {
            return $this->initiatePaystack($user, $target, $cost);
        }

        $result = DB::transaction(function () use ($user, $target, $cost) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            $current = Subscription::currentFor($lockedUser);
            if ($current && $current->plan === $target) {
                return ['error' => 'You already have an active ' . strtoupper($target) . ' subscription until ' . $current->expires_at->format('M j, Y') . '.'];
            }

            if ($lockedUser->wallet_balance < $cost) {
                return ['error' => 'Insufficient wallet balance. Top up your wallet first.'];
            }

            $lockedUser->decrement('wallet_balance', $cost);

            $subscription = Subscription::create([
                'user_id' => $lockedUser->id,
                'plan' => $target,
                'amount' => $cost,
                'payment_method' => 'wallet',
                'status' => 'pending_payment',
                'starts_at' => now(),
                'expires_at' => now(),
            ]);

            $subscription->activate();
            $subscription->refresh();

            $lockedUser->notify(new \App\Notifications\SubscriptionActivated($subscription));

            return ['subscription' => $subscription];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['tier' => $result['error']]);
        }

        $sub = $result['subscription'];
        return redirect()->route('dashboard')->with('status', 'Subscribed to ' . Subscription::PLANS[$sub->plan]['label'] . '! Renews ' . $sub->expires_at->format('M j, Y') . '.');
    }

    private function initiatePaystack($user, $target, $cost)
    {
        $txRef = 'SUBSCRIBE-' . $target . '-' . $user->id . '-' . time();

        Subscription::create([
            'user_id' => $user->id,
            'plan' => $target,
            'amount' => $cost,
            'payment_method' => 'paystack',
            'paystack_tx_ref' => $txRef,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'expires_at' => now(),
        ]);

        $response = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'reference' => $txRef,
                'amount' => (int) round($cost * 100),
                'currency' => 'NGN',
                'email' => $user->email,
                'callback_url' => route('subscriptions.callback'),
                'metadata' => ['title' => 'NAURVEX ' . Subscription::PLANS[$target]['label'] . ' Subscription'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? false) === true) {
            return redirect($data['data']['authorization_url']);
        }

        return back()->withErrors(['tier' => 'Could not start payment. Please try again.']);
    }

    public function paystackCallback(Request $request)
    {
        $txRef = $request->query('reference') ?? $request->query('trxref');

        $sub = Subscription::where('paystack_tx_ref', $txRef)->first();

        if (!$sub) {
            return redirect()->route('subscriptions.index')->withErrors(['tier' => 'Subscription record not found.']);
        }

        $verify = Http::withToken(config('services.paystack.secret'))
            ->get("https://api.paystack.co/transaction/verify/{$txRef}");
        $verifyData = $verify->json();

        $verified = ($verifyData['status'] ?? false) === true
            && ($verifyData['data']['status'] ?? null) === 'success'
            && ($verifyData['data']['reference'] ?? null) === $txRef
            && ((float) ($verifyData['data']['amount'] ?? 0)) / 100 >= (float) $sub->amount
            && ($verifyData['data']['currency'] ?? null) === 'NGN';

        $result = DB::transaction(function () use ($sub, $verified) {
            $locked = Subscription::where('id', $sub->id)->lockForUpdate()->first();

            if ($locked->status === 'active') {
                return ['already' => true, 'sub' => $locked];
            }

            if (!$verified) {
                $locked->update(['status' => 'cancelled']);
                return ['error' => true];
            }

            $locked->activate();
            $locked->refresh();

            \App\Models\User::find($locked->user_id)?->notify(new \App\Notifications\SubscriptionActivated($locked));
            return ['sub' => $locked];
        });

        if (!empty($result['error'])) {
            return redirect()->route('subscriptions.index')->withErrors(['tier' => 'Payment verification failed.']);
        }

        $sub = $result['sub'];
        $msg = !empty($result['already'])
            ? 'Payment already processed.'
            : 'Subscribed to ' . Subscription::PLANS[$sub->plan]['label'] . '! Renews ' . $sub->expires_at->format('M j, Y') . '.';

        return redirect()->route('dashboard')->with('status', $msg);
    }
}
