<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletActionController extends Controller
{
    public function topup(Request $request)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:100']);
        $user = Auth::user();

        $user->increment('wallet_balance', $validated['amount']);
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'topup',
            'amount' => $validated['amount'],
            'description' => 'Wallet top-up',
        ]);

        return redirect()->route('wallet.index')->with('status', 'Top-up successful.');
    }

    public function withdraw(Request $request)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:100']);
        $user = Auth::user();

        if ($user->wallet_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance.']);
        }

        $user->decrement('wallet_balance', $validated['amount']);
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => -$validated['amount'],
            'description' => 'Withdrawal',
        ]);

        return redirect()->route('wallet.index')->with('status', 'Withdrawal requested.');
    }
}
