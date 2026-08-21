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
            'payment_method' => 'required|in:wallet,flutterwave',
        ]);

        $user = Auth::user();
        $target = $validated['target_tier'];
        $method = $validated['payment_method'];

        $current = Subscription::currentFor($user);

        if ($current && $current->plan === $target) {
            return back()->withErrors(['tier' => 'You already have an active ' . strtoupper($target) . ' subscription until ' . $current->expires_at->format('M j, Y') . '.']);
        }

        $cost = Subscription::PLANS[$target]['price'];

        if ($method === 'flutterwave') {
            return $this->initiateFlutterwave($user, $target, $cost);
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

            return ['subscription' => $subscription];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['tier' => $result['error']]);
        }

        $sub = $result['subscription'];
        return redirect()->route('dashboard')->with('status', 'Subscribed to ' . Subscription::PLANS[$sub->plan]['label'] . '! Renews ' . $sub->expires_at->format('M j, Y') . '.');
    }

    private function initiateFlutterwave($user, $target, $cost)
    {
        $txRef = 'SUBSCRIBE-' . $target . '-' . $user->id . '-' . time();

        Subscription::create([
            'user_id' => $user->id,
            'plan' => $target,
            'amount' => $cost,
            'payment_method' => 'flutterwave',
            'flutterwave_tx_ref' => $txRef,
            'status' => 'pending_payment',
            'starts_at' => now(),
            'expires_at' => now(),
        ]);

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref' => $txRef,
                'amount' => $cost,
                'currency' => 'NGN',
                'redirect_url' => route('subscriptions.callback'),
                'customer' => ['email' => $user->email, 'name' => $user->name],
                'customizations' => ['title' => 'Qelvo ' . Subscription::PLANS[$target]['label'] . ' Subscription'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            return redirect($data['data']['link']);
        }

        return back()->withErrors(['tier' => 'Could not start payment. Please try again.']);
    }

    public function flutterwaveCallback(Request $request)
    {
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        $sub = Subscription::where('flutterwave_tx_ref', $txRef)->first();

        if (!$sub) {
            return redirect()->route('subscriptions.index')->withErrors(['tier' => 'Subscription record not found.']);
        }

        if ($status !== 'successful') {
            DB::transaction(function () use ($sub) {
                $locked = Subscription::where('id', $sub->id)->lockForUpdate()->first();
                if ($locked->status === 'pending_payment') {
                    $locked->update(['status' => 'cancelled']);
                }
            });
            return redirect()->route('subscriptions.index')->withErrors(['tier' => 'Payment was not completed.']);
        }

        $transactionId = $request->query('transaction_id');
        $verify = Http::withToken(config('services.flutterwave.secret'))
            ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");
        $verifyData = $verify->json();

        $verified = ($verifyData['status'] ?? null) === 'success'
            && ($verifyData['data']['status'] ?? null) === 'successful'
            && ($verifyData['data']['tx_ref'] ?? null) === $txRef
            && (float) ($verifyData['data']['amount'] ?? 0) >= (float) $sub->amount
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
