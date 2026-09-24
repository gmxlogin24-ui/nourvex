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
        $banks = Cache::remember('flutterwave_banks_ng', now()->addDay(), function () {
            $response = Http::withToken(config('services.flutterwave.secret'))
                ->get('https://api.flutterwave.com/v3/banks/NG');
            $data = $response->json();
            return ($data['status'] ?? null) === 'success' ? ($data['data'] ?? []) : [];
        });

        return view('wallet.bank-account', [
            'banks' => $banks,
            'user' => Auth::user(),
        ]);
    }

    // AJAX: confirm the account holder's name before the user commits to saving it.
    public function resolve(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|size:10',
            'bank_code' => 'required|string',
        ]);

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->post('https://api.flutterwave.com/v3/accounts/resolve', [
                'account_number' => $validated['account_number'],
                'account_bank' => $validated['bank_code'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) !== 'success') {
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

        // Re-resolve server-side rather than trusting whatever name the
        // client displayed - prevents tampering between resolve and save.
        $response = Http::withToken(config('services.flutterwave.secret'))
            ->post('https://api.flutterwave.com/v3/accounts/resolve', [
                'account_number' => $validated['account_number'],
                'account_bank' => $validated['bank_code'],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) !== 'success') {
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
