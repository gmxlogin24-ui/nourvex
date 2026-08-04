<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index()
    {
        $weekStart = Carbon::now()->startOfWeek();

        $topEarners = Transaction::where('transactions.created_at', '>=', $weekStart)
            ->where('amount', '>', 0)
            ->whereIn('type', ['gig_earning', 'marketplace_sale', 'referral_bonus'])
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('users.leaderboard_opt_in', true)
            ->selectRaw('users.id, users.name, SUM(transactions.amount) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('leaderboard.index', [
            'topEarners' => $topEarners,
            'optedIn' => Auth::user()->leaderboard_opt_in,
        ]);
    }

    public function toggleOptIn(Request $request)
    {
        $user = Auth::user();
        $user->update(['leaderboard_opt_in' => !$user->leaderboard_opt_in]);
        return back()->with('status', $user->leaderboard_opt_in ? 'You\'re now on the leaderboard!' : 'You\'ve been removed from the leaderboard.');
    }
}
