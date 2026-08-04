<?php

namespace App\Http\Controllers;

use App\Models\ServicePurchase;
use App\Models\Transaction;
use App\Services\VtuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    public function index()
    {
        return view('services.index', [
            'balance' => Auth::user()->wallet_balance,
            'networks' => array_keys(VtuService::NETWORKS),
        ]);
    }

    public function dataPlans(Request $request, VtuService $vtu)
    {
        $validated = $request->validate(['network' => 'required|string']);
        $margin = 1 + ((float) env('VTU_MARGIN_PERCENT', 4) / 100);

        $plans = collect($vtu->getDataPlans($validated['network']))->map(function ($plan) use ($margin) {
            $cost = (float) ($plan['amount'] ?? $plan['price'] ?? 0);
            return [
                'id' => $plan['id'] ?? $plan['data_id'] ?? null,
                'label' => ($plan['plan_type'] ?? $plan['name'] ?? 'Plan'),
                'price' => round($cost * $margin),
            ];
        })->filter(fn($p) => $p['id'] !== null)->values();

        return response()->json(['plans' => $plans]);
    }

    public function buyAirtime(Request $request, VtuService $vtu)
    {
        $validated = $request->validate([
            'network' => 'required|string|in:mtn,glo,airtel,9mobile',
            'phone' => 'required|string|min:11|max:14',
            'amount' => 'required|numeric|min:50|max:50000',
        ]);

        return $this->processPurchase($vtu, 'airtime', $validated['network'], $validated['phone'], (float) $validated['amount'], null);
    }

    public function buyData(Request $request, VtuService $vtu)
    {
        $validated = $request->validate([
            'network' => 'required|string|in:mtn,glo,airtel,9mobile',
            'phone' => 'required|string|min:11|max:14',
            'plan_id' => 'required|string',
            'price' => 'required|numeric|min:1',
        ]);

        return $this->processPurchase($vtu, 'data', $validated['network'], $validated['phone'], (float) $validated['price'], $validated['plan_id']);
    }

    private function processPurchase(VtuService $vtu, string $type, string $network, string $phone, float $amount, ?string $planId)
    {
        $user = Auth::user();

        if ($user->wallet_balance < $amount) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance. Top up your wallet first.']);
        }

        $reference = strtoupper($type) . '-' . $user->id . '-' . time() . '-' . Str::random(4);

        $user->decrement('wallet_balance', $amount);
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'service_purchase',
            'amount' => -$amount,
            'description' => ucfirst($type) . ' purchase — ' . strtoupper($network) . ' ' . $phone,
        ]);

        $purchase = ServicePurchase::create([
            'user_id' => $user->id,
            'service_type' => $type,
            'network' => $network,
            'phone' => $phone,
            'plan_code' => $planId,
            'amount' => $amount,
            'status' => 'pending',
            'provider_reference' => $reference,
        ]);

        $result = $type === 'airtime'
            ? $vtu->buyAirtime($network, $phone, $amount, $reference)
            : $vtu->buyData($network, $phone, $planId, $reference);

        if ($result['success']) {
            $purchase->update(['status' => 'successful']);
            return redirect()->route('services.index')->with('status', ucfirst($type) . ' purchase successful!');
        }

        $purchase->update(['status' => 'failed']);
        $user->increment('wallet_balance', $amount);
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'service_refund',
            'amount' => $amount,
            'description' => 'Refund — failed ' . $type . ' purchase',
        ]);

        return back()->withErrors(['service' => $result['message'] ?? 'Purchase failed. Your wallet has been refunded.']);
    }
}
