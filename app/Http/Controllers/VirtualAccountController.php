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

        $response = Http::withToken(env('FLW_SECRET_KEY'))
            ->post('https://api.flutterwave.com/v3/virtual-account-numbers', [
                'email' => $user->email,
                'tx_ref' => 'VA-' . $user->id . '-' . time(),
                'phonenumber' => $validated['phonenumber'],
                'is_permanent' => true,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'narration' => 'Campulsy - ' . $user->name,
                'bvn' => $validated['bvn'],
            ]);

        // $validated (including bvn) goes out of scope here and is never stored - only Flutterwave's response is saved
        $data = $response->json();

        if (($data['status'] ?? null) !== 'success') {
            return back()->withErrors(['bvn' => $data['message'] ?? 'Could not create virtual account. Check your details and try again.']);
        }

        $user->update([
            'virtual_account_number' => $data['data']['account_number'],
            'virtual_account_bank' => $data['data']['bank_name'],
            'virtual_account_ref' => $data['data']['flw_ref'],
        ]);

        return redirect()->route('wallet.virtual-account')->with('status', 'Virtual account created!');
    }
}
