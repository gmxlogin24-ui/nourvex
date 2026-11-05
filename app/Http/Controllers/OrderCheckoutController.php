<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingMessage;
use App\Models\ListingOrder;
use App\Models\OrderCheckout;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SpendLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderCheckoutController extends Controller
{
    // Starts a card payment for one marketplace listing. The price comes from the listing, never from the browser.
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'kind' => 'required|in:marketplace',
            'target_id' => 'required|integer',
        ]);
        $user = Auth::user();

        $listing = Listing::find((int) $validated['target_id']);
        if (!$listing || $listing->status !== 'available' || (int) $listing->user_id === (int) $user->id) {
            return back()->withErrors(['order' => 'This listing is not available to buy.']);
        }

        $amount = round((float) $listing->amount, 2);
        if ($reason = SpendLimit::blockedReason($user, $amount)) {
            return back()->withErrors(['order' => $reason]);
        }

        $secret = config('services.paystack.secret');
        if (empty($secret)) {
            return back()->withErrors(['order' => 'Card payments are not set up yet.']);
        }

        $txRef = 'ORDER-' . $user->id . '-' . time() . '-' . Str::random(4);
        $row = OrderCheckout::create([
            'user_id' => $user->id,
            'tx_ref' => $txRef,
            'amount' => $amount,
            'kind' => 'marketplace',
            'target_id' => (string) $listing->id,
            'status' => 'pending',
        ]);

        $res = [];
        try {
            $res = Http::withToken($secret)->timeout(30)->post('https://api.paystack.co/transaction/initialize', [
                'reference' => $txRef,
                'amount' => (int) round($amount * 100),
                'currency' => 'NGN',
                'email' => $user->email,
                'callback_url' => route('order.checkout.callback'),
                'metadata' => ['title' => 'NAURVEX marketplace order', 'listing_id' => $listing->id],
            ])->json();
        } catch (\Throwable $e) {
            Log::error('Order checkout init failed: ' . $e->getMessage());
        }

        if (($res['status'] ?? false) === true && !empty($res['data']['authorization_url'])) {
            return redirect($res['data']['authorization_url']);
        }

        $row->update(['status' => 'failed']);
        return back()->withErrors(['order' => 'Could not start payment. Please try again.']);
    }

    // Browser returns here after paying. Payment is verified with Paystack before the order is completed.
    public function callback(Request $request)
    {
        $txRef = $request->query('reference') ?? $request->query('trxref');
        $row = OrderCheckout::where('tx_ref', $txRef)->first();
        if (!$row || (int) $row->user_id !== (int) Auth::id()) {
            return redirect()->route('dashboard')->withErrors(['order' => 'Payment not found.']);
        }

        if (!in_array($row->status, ['paid', 'paid_unfulfilled', 'refunded'], true)) {
            $secret = config('services.paystack.secret');
            $v = [];
            try {
                $v = Http::withToken($secret)->timeout(30)->get('https://api.paystack.co/transaction/verify/' . $txRef)->json();
            } catch (\Throwable $e) {
                Log::warning('Order verify failed: ' . $e->getMessage());
            }
            $d = $v['data'] ?? [];
            $ok = ($v['status'] ?? false) === true
                && ($d['status'] ?? null) === 'success'
                && ($d['reference'] ?? null) === $txRef
                && ($d['currency'] ?? null) === 'NGN';
            if ($ok) {
                self::settle($row, ((float) ($d['amount'] ?? 0)) / 100);
            } elseif (in_array($d['status'] ?? null, ['failed', 'abandoned', 'reversed'], true)) {
                $row->update(['status' => 'failed']);
            }
        }

        $row->refresh();
        if ($row->status === 'paid') {
            return redirect()->route('marketplace.show', (int) $row->target_id)
                ->with('status', 'Payment received. Your order is placed and the seller has been notified.');
        }
        if ($row->status === 'paid_unfulfilled') {
            return redirect()->route('dashboard')
                ->withErrors(['order' => 'Your payment was received, but this item sold out first. You will be refunded.']);
        }
        return redirect()->route('dashboard')
            ->withErrors(['order' => 'Payment is not confirmed yet. If you were charged, it will show shortly.']);
    }

    // Completes a card-paid order: the item is sold to the buyer and the money is held in escrow.
    // The buyer's wallet is never touched. Runs once per payment, whether from the browser or the webhook.
    public static function settle(OrderCheckout $row, float $paid): void
    {
        if ($paid + 0.01 < (float) $row->amount) {
            return;
        }
        DB::transaction(function () use ($row) {
            $locked = OrderCheckout::where('id', $row->id)->lockForUpdate()->first();
            if (!$locked || in_array($locked->status, ['paid', 'paid_unfulfilled', 'refunded'], true)) {
                return;
            }

            $listing = Listing::where('id', (int) $locked->target_id)->lockForUpdate()->first();
            $buyerId = (int) $locked->user_id;

            if (!$listing || $listing->status !== 'available' || (int) $listing->user_id === $buyerId) {
                // Paid, but the item is no longer available. Held for a manual refund.
                $locked->update(['status' => 'paid_unfulfilled', 'paid_at' => now()]);
                return;
            }

            $locked->update(['status' => 'paid', 'paid_at' => now()]);

            if ($listing->stock_quantity !== null) {
                ListingOrder::create([
                    'listing_id' => $listing->id,
                    'buyer_id' => $buyerId,
                    'amount' => $listing->amount,
                    'status' => 'paid',
                ]);
                $listing->decrement('stock_quantity');
                if ($listing->fresh()->stock_quantity <= 0) {
                    $listing->update(['status' => 'sold']);
                }
            } else {
                $listing->update([
                    'status' => 'sold',
                    'buyer_id' => $buyerId,
                    'sold_at' => now(),
                ]);
            }

            Transaction::create([
                'user_id' => $buyerId,
                'type' => 'marketplace_card',
                'amount' => -(float) $listing->amount,
                'description' => 'Paid by card (held in escrow): ' . $listing->title,
            ]);

            if ($listing->user_id) {
                ListingMessage::postSystemEvent(
                    $listing->id, $buyerId, $listing->user_id,
                    '💰 Payment sent — ₦' . number_format($listing->amount) . ' held in escrow until delivery is confirmed.'
                );
                User::find($listing->user_id)?->notify(new \App\Notifications\MarketplaceOrderPlaced($listing, User::find($buyerId)));
            }
        });
    }

    public static function settleByRef(string $txRef, float $paid): void
    {
        $row = OrderCheckout::where('tx_ref', $txRef)->first();
        if ($row) {
            self::settle($row, $paid);
        }
    }
}
