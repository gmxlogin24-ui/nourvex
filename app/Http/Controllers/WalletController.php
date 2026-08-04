<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('wallet.index', [
            'balance' => $user->wallet_balance,
            'transactions' => Transaction::where('user_id', $user->id)->latest()->get(),
        ]);
    }

    public function moneyTracker()
    {
        $user = Auth::user();

        abort_unless(in_array($user->tier, ['tier2', 'tier3'], true), 403, 'Money Tracker is available on the Gold plan and above. Upgrade to unlock detailed cash flow tracking.');

        $totalIn = Transaction::where('user_id', $user->id)->where('amount', '>', 0)->sum('amount');
        $totalOut = abs(Transaction::where('user_id', $user->id)->where('amount', '<', 0)->sum('amount'));

        $categoryTotals = Transaction::where('user_id', $user->id)
            ->selectRaw('type, SUM(ABS(amount)) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $monthly = Transaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("strftime('%Y-%m', created_at) as ym, SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as inflow, SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as outflow")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return view('money-tracker.index', [
            'balance' => $user->wallet_balance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'categoryTotals' => $categoryTotals,
            'monthly' => $monthly,
            'transactions' => Transaction::where('user_id', $user->id)->latest()->take(30)->get(),
        ]);
    }
}
