<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BankAccountController extends Controller
{
    public function edit()
    {
        $banks = Cache::remember('paystack_banks_ng', now()->addDay(), function () {
            $response = Http::withToken(config('services.paystack.secret'))
                ->get('https://api.paystack.co/bank', ['country' => 'nigeria']);
            $data = $response->json();
            if (($data['status'] ?? false) !== true) {
                return [];
            }
            // Normalize to the same shape the old Paystack list used, so the blade view doesn't need changes.
            return array_map(fn($b) => ['code' => $b['code'], 'name' => $b['name']], $data['data'] ?? []);
        });

        return view('wallet.bank-account', [
            'banks' => $banks,
            'user' => Auth::user(),
        ]);
    }

    public function resolve(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|size:10',
            'bank_code' => 'required|string',
        ]);

        $response = Http::withToken(config('services.paystack.secret'))
            ->get('https://api.paystack.co/bank/resolve', [
                'account_number' => $validated['account_number'],
                'bank_code' => $validated['bank_code'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? false) !== true) {
            return response()->json(['error' => $data['message'] ?? 'Could not resolve account.'], 422);
        }

        return response()->json(['account_name' => $data['data']['account_name'] ?? '']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|size:10',
            'bank_code' => 'required|string',
            'bank_name' => 'required|string',
        ]);

        $response = Http::withToken(config('services.paystack.secret'))
            ->get('https://api.paystack.co/bank/resolve', [
                'account_number' => $validated['account_number'],
                'bank_code' => $validated['bank_code'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? false) !== true) {
            return back()->withErrors(['account_number' => $data['message'] ?? 'Could not verify account.']);
        }

        Auth::user()->update([
            'payout_bank_code' => $validated['bank_code'],
            'payout_bank_name' => $validated['bank_name'],
            'payout_account_number' => $validated['account_number'],
            'payout_account_name' => $data['data']['account_name'] ?? null,
        ]);

        return redirect()->route('wallet.index')->with('status', 'Payout bank account saved.');
    }
}
