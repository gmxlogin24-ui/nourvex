<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VirtualAccountController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        if ($user->virtual_account_number) {
            return view('wallet.virtual-account', ['user' => $user]);
        }
        return view('wallet.virtual-account-setup');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phonenumber' => 'required|string|max:15',
            'bvn' => 'required|string|size:11',
        ]);

        $user = Auth::user();
        $nameParts = explode(' ', trim($user->name), 2);
        $firstname = $nameParts[0];
        $lastname = $nameParts[1] ?? $nameParts[0];
        $secret = config('services.paystack.secret');

        // Step 1: create (or reuse) a Paystack customer
        $customerRes = Http::withToken($secret)->post('https://api.paystack.co/customer', [
            'email' => $user->email,
            'first_name' => $firstname,
            'last_name' => $lastname,
            'phone' => $validated['phonenumber'],
        ]);
        $customerData = $customerRes->json();

        $customerCode = $customerData['data']['customer_code'] ?? null;

        if (!$customerCode) {
            return back()->withErrors(['bvn' => $customerData['message'] ?? 'Could not create customer profile. Check your details and try again.']);
        }

        // Step 2: validate customer with BVN (required by Paystack before DVA can be issued for most banks)
        Http::withToken($secret)->post("https://api.paystack.co/customer/{$customerCode}/identification", [
            'country' => 'NG',
            'type' => 'bvn',
            'value' => $validated['bvn'],
            'bvn' => $validated['bvn'],
            'first_name' => $firstname,
            'last_name' => $lastname,
        ]);

        // Step 3: attach a dedicated virtual account to the customer
        $dvaRes = Http::withToken($secret)->post('https://api.paystack.co/dedicated_account', [
            'customer' => $customerCode,
            'preferred_bank' => 'wema-bank',
        ]);
        $dvaData = $dvaRes->json();

        if (($dvaData['status'] ?? false) !== true) {
            return back()->withErrors(['bvn' => $dvaData['message'] ?? 'Could not create virtual account. Check your details and try again.']);
        }

        $user->update([
            'virtual_account_number' => $dvaData['data']['account_number'],
            'virtual_account_bank' => $dvaData['data']['bank']['name'] ?? 'Wema Bank',
            'virtual_account_ref' => $customerCode,
        ]);

        return redirect()->route('wallet.virtual-account')->with('status', 'Virtual account created!');
    }
}
