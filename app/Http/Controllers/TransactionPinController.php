<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionPinController extends Controller
{
    public function edit()
    {
        return view('wallet.pin', [
            'hasPin' => Auth::user()->hasTransactionPin(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_pin' => $user->hasTransactionPin() ? 'required|string' : 'nullable',
            'pin' => 'required|string|digits_between:4,6|confirmed',
        ]);

        if ($user->hasTransactionPin() && !$user->verifyTransactionPin($validated['current_pin'])) {
            return back()->withErrors(['current_pin' => 'Current PIN is incorrect.']);
        }

        $user->update(['transaction_pin' => $validated['pin']]);

        return back()->with('status', $user->wasRecentlyModified ? 'Transaction PIN updated.' : 'Transaction PIN set.');
    }
}
