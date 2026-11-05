<?php

namespace App\Http\Controllers;

use App\Models\ServicePurchase;
use App\Models\Transaction;
use App\Models\User;
use App\Services\VtuService;
use App\Services\VtuNgService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        $t = $request->query('tab');
        if (in_array($t, ['airtime', 'data', 'cable'], true)) {
            return redirect()->route('services.show', $t);
        }
        return view('services.hub', ['balance' => Auth::user()->wallet_balance]);
    }

    public function show(string $service)
    {
        return view('services.index', [
            'service' => $service,
            'balance' => Auth::user()->wallet_balance,
            'networks' => VtuService::NETWORKS,
            'hasPin' => Auth::user()->hasTransactionPin(),
            'idempotencyKey' => (string) Str::uuid(),
            'savedAirtimeNumbers' => \App\Models\SavedNumber::where('user_id', Auth::id())->where('type', 'airtime')->latest()->limit(6)->get(),
            'savedDataNumbers' => \App\Models\SavedNumber::where('user_id', Auth::id())->where('type', 'data')->latest()->limit(6)->get(),
            'savedCards' => \App\Models\SavedNumber::where('user_id', Auth::id())->where('type', 'smart_card')->latest()->limit(6)->get(),
        ]);
    }

    public function dataPlans(Request $request, VtuService $vtu)
    {
        $validated = $request->validate(['network' => 'required|string']);
        $margin = VtuService::marginMultiplier();

        $plans = collect($vtu->getDataPlans($validated['network']))
            ->filter(fn($p) => $p['id'] !== null)
            ->map(function ($plan) use ($margin) {
                $validity = VtuService::normalizeValidity($plan['validity'] ?? '');
                return [
                    'id' => $plan['id'],
                    'label' => trim(($plan['plan_type'] ?? '') . ' - ' . ($plan['size'] ?? '')),
                    'price' => round($plan['amount'] * $margin),
                    'validity_label' => $validity['label'],
                    'validity_days' => $validity['days'],
                ];
            });

        $groups = $plans
            ->groupBy('validity_label')
            ->map(fn($group, $label) => [
                'label' => $label,
                'days' => $group->first()['validity_days'],
                'plans' => $group->sortBy('price')->map(fn($p) => [
                    'id' => $p['id'],
                    'label' => $p['label'],
                    'price' => $p['price'],
                ])->values(),
            ])
            ->sortBy('days')
            ->values();

        return response()->json(['groups' => $groups]);
    }

    public function cablePlans(Request $request, VtuService $vtu)
    {
        $validated = $request->validate(['provider' => 'required|string|in:gotv,dstv,startime']);
        $margin = VtuService::marginMultiplier();

        $plans = collect($vtu->getCablePlans($validated['provider']))
            ->map(fn($plan) => [
                'id' => $plan['id'],
                'label' => $plan['label'],
                'price' => round($plan['amount'] * $margin),
            ])
            ->sortBy('price')
            ->values();

        return response()->json(['plans' => $plans]);
    }

    public function buyAirtime(Request $request, VtuService $vtu, VtuNgService $vtuNg)
    {
        $validated = $request->validate([
            'network' => 'required|string|in:mtn,glo,airtel,9mobile',
            'phone' => 'required|string|min:11|max:14',
            'amount' => 'required|numeric|min:50|max:50000',
            'pin' => 'required|string',
            'idempotency_key' => 'required|string',
        ]);

        $this->saveNumberIfRequested($request, 'airtime', $validated['network'], $validated['phone']);

        return $this->processPurchase($vtu, $vtuNg, 'airtime', $validated['network'], $validated['phone'], (float) $validated['amount'], null, $validated['pin'], $validated['idempotency_key'], (float) $validated['amount']);
    }

    public function buyData(Request $request, VtuService $vtu, VtuNgService $vtuNg)
    {
        $validated = $request->validate([
            'network' => 'required|string|in:mtn,glo,airtel,9mobile',
            'phone' => 'required|string|min:11|max:14',
            'plan_id' => 'required|string',
            'pin' => 'required|string',
            'idempotency_key' => 'required|string',
        ]);

        $this->saveNumberIfRequested($request, 'data', $validated['network'], $validated['phone']);

        $margin = VtuService::marginMultiplier();
        $plan = collect($vtu->getDataPlans($validated['network']))
            ->first(fn($p) => (string) $p['id'] === $validated['plan_id']);

        abort_if(!$plan, 422, 'Selected plan is no longer available.');

        $cost = (float) ($plan['amount'] ?? $plan['price'] ?? 0);
        $price = round($cost * $margin);

        return $this->processPurchase($vtu, $vtuNg, 'data', $validated['network'], $validated['phone'], $price, $validated['plan_id'], $validated['pin'], $validated['idempotency_key'], $cost);
    }

    public function buyCable(Request $request, VtuService $vtu, VtuNgService $vtuNg)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:gotv,dstv,startime',
            'smart_card_number' => 'required|string|min:10|max:20',
            'plan_id' => 'required|string',
            'pin' => 'required|string',
            'idempotency_key' => 'required|string',
        ]);

        $this->saveNumberIfRequested($request, 'smart_card', $validated['provider'], $validated['smart_card_number']);

        // Never trust a client-supplied price - recompute server-side from
        // plan_id against the live cable plan list, same reasoning as buyData().
        $margin = VtuService::marginMultiplier();
        $plan = collect($vtu->getCablePlans($validated['provider']))
            ->first(fn($p) => (string) $p['id'] === $validated['plan_id']);

        abort_if(!$plan, 422, 'Selected cable plan is no longer available.');

        $cost = (float) $plan['amount'];
        $price = round($cost * $margin);

        return $this->processPurchase($vtu, $vtuNg, 'cable', $validated['provider'], $validated['smart_card_number'], $price, $validated['plan_id'], $validated['pin'], $validated['idempotency_key'], $cost);
    }

    private function saveNumberIfRequested(Request $request, string $type, ?string $provider, string $number): void
    {
        if (!$request->boolean('save_number')) return;

        \App\Models\SavedNumber::updateOrCreate(
            ['user_id' => Auth::id(), 'type' => $type, 'number' => $number],
            ['provider' => $provider]
        );
    }

    public function receipt(ServicePurchase $purchase)
    {
        abort_if($purchase->user_id !== Auth::id(), 403);
        return view('services.receipt', ['purchase' => $purchase]);
    }

    // Airtime is the one service type where both providers accept the exact
    // same inputs (network slug, phone, naira amount), so a failed Oseultimate
    // attempt is retried on vtu.ng before the purchase is treated as failed.
    // A '-NG' suffix keeps the two providers' request_id namespaces separate.
    private function buyAirtimeWithFailover(VtuService $vtu, VtuNgService $vtuNg, string $network, string $phone, float $amount, string $reference): array
    {
        $primary = $vtu->buyAirtime($network, $phone, $amount, $reference);
        if ($primary['success']) return $primary;

        Log::warning('Oseultimate airtime failed, trying vtu.ng failover', ['reference' => $reference, 'message' => $primary['message'] ?? null]);

        $secondary = $vtuNg->buyAirtime($network, $phone, $amount, $reference . '-NG');
        if ($secondary['success']) return $secondary;

        Log::error('vtu.ng airtime failover also failed', ['reference' => $reference, 'message' => $secondary['message'] ?? null]);

        return ['success' => false, 'message' => 'Both airtime providers failed. Your wallet has been refunded.'];
    }

    private function processPurchase(VtuService $vtu, VtuNgService $vtuNg, string $type, string $network, string $phone, float $amount, ?string $planId, string $pin, string $idempotencyKey, ?float $cost = null)
    {
        $userId = Auth::id();

        // Idempotency: if this exact key already produced a purchase (e.g. a
        // double-tapped submit button, or a retried request), return that
        // result instead of charging the wallet again.
        $existing = ServicePurchase::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            if ($existing->status === 'successful') {
                return redirect()->route('services.receipt', $existing)->with('status', ucfirst($type) . ' purchase successful!');
            }
            if ($existing->status === 'pending') {
                return back()->withErrors(['service' => 'This purchase is already being processed.']);
            }
            // status === 'failed' - already refunded, safe to let the user retry
            // manually with a fresh key rather than silently reprocessing.
            return back()->withErrors(['service' => 'That purchase already failed and was refunded. Please submit again.']);
        }

        $user = Auth::user();
        if (!$user->hasTransactionPin()) {
            return back()->withErrors(['pin' => 'Set a transaction PIN before making purchases.']);
        }
        if (!$user->verifyTransactionPin($pin)) {
            return back()->withErrors(['pin' => $user->pinErrorMessage()]);
        }

        $reference = strtoupper($type) . '-' . $userId . '-' . time() . '-' . Str::random(4);

        // Balance check + decrement + purchase record must be one atomic
        // unit under a row lock, otherwise concurrent requests can both
        // pass the balance check before either decrements.
        if ($reason = \App\Services\SpendLimit::blockedReason(\App\Models\User::find($userId), (float) $amount)) {
            return back()->withErrors(['amount' => $reason]);
        }

        $purchase = DB::transaction(function () use ($userId, $type, $network, $phone, $amount, $planId, $reference, $idempotencyKey, $cost) {
            $user = User::where('id', $userId)->lockForUpdate()->first();

            if ($user->wallet_restricted) {
                return null;
            }
            if ($user->wallet_balance < $amount) {
                return null;
            }

            $user->decrement('wallet_balance', $amount);
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'service_purchase',
                'amount' => -$amount,
                'description' => ucfirst($type) . ' purchase — ' . strtoupper($network) . ' ' . $phone,
            ]);

            return ServicePurchase::create([
                'user_id' => $user->id,
                'service_type' => $type,
                'network' => $network,
                'phone' => $phone,
                'plan_code' => $planId,
                'amount' => $amount,
                'cost' => $cost,
                'status' => 'pending',
                'provider_reference' => $reference,
                'idempotency_key' => $idempotencyKey,
            ]);
        });

        if (!$purchase) {
            $restricted = User::where('id', $userId)->value('wallet_restricted');
            if ($restricted) {
                return back()->withErrors(['amount' => 'Your wallet is restricted due to an unresolved payment issue. Clear your balance to make purchases.']);
            }
            return back()->withErrors(['amount' => 'Insufficient wallet balance. Top up your wallet first.']);
        }

        // External provider call happens outside the DB transaction/lock —
        // never hold a row lock across a network call. Wrapped in try/catch
        // so a thrown exception (timeout, connection drop) can never leave
        // the wallet debited with no refund and the purchase stuck pending.
        try {
            $result = match ($type) {
                'airtime' => $this->buyAirtimeWithFailover($vtu, $vtuNg, $network, $phone, $amount, $reference),
                'data' => $vtu->buyData($network, $phone, $planId, $reference),
                'cable' => $vtu->buyCable($network, $phone, $planId, $reference),
                default => ['success' => false, 'message' => 'Unknown service type.'],
            };
        } catch (\Throwable $e) {
            Log::error('VTU purchase call threw an exception', ['type' => $type, 'reference' => $reference, 'error' => $e->getMessage()]);
            $result = ['success' => false, 'message' => 'Provider error. Your wallet has been refunded.'];
        }

        if ($result['success']) {
            $purchase->update(['status' => 'successful']);
            try {
                $buyer = \App\Models\User::find($purchase->user_id);
                if ($buyer) {
                    $pts = (int) config('ranking.points.service_purchase', 0)
                        + min(intdiv((int) $amount, 100) * (int) config('ranking.service_per_100', 1), (int) config('ranking.service_cap', 25));
                    app(\App\Services\RankingService::class)->award($buyer, 'service_purchase', $pts, 'svc:' . $purchase->id);
                }
            } catch (\Throwable $e) {
                report($e);
            }
            $margin = $purchase->cost !== null ? round($amount - $purchase->cost, 2) : 0;
            if ($margin > 0) {
                \App\Models\PlatformRevenue::record('vtu', $margin, ucfirst($type) . ' margin — ' . strtoupper($network), 'service_purchase', $purchase->id);
            }
            return redirect()->route('services.receipt', $purchase)->with('status', ucfirst($type) . ' purchase successful!');
        }

        DB::transaction(function () use ($userId, $amount, $type, $purchase) {
            $user = User::where('id', $userId)->lockForUpdate()->first();
            $user->increment('wallet_balance', $amount);
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'service_refund',
                'amount' => $amount,
                'description' => 'Refund — failed ' . $type . ' purchase',
            ]);
            $purchase->update(['status' => 'failed']);
        });

        return back()->withErrors(['service' => $result['message'] ?? 'Purchase failed. Your wallet has been refunded.']);
    }
}
