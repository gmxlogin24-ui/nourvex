<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\Transaction;
use App\Services\RankingService;
use Illuminate\Support\Facades\Auth;

class OverviewController extends Controller
{
    public function index(RankingService $ranking)
    {
        $user = Auth::user();
        $id = $user->id;

        $totalIn = (float) Transaction::where('user_id', $id)->where('amount', '>', 0)->sum('amount');
        $totalOut = abs((float) Transaction::where('user_id', $id)->where('amount', '<', 0)->sum('amount'));

        $from = now()->subDays(6)->startOfDay();
        $rows = Transaction::where('user_id', $id)->where('created_at', '>=', $from)->get(['amount', 'created_at']);

        $flow = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $flow[] = round((float) $rows->filter(fn ($r) => $r->created_at->toDateString() === $key)->sum('amount'), 2);
            $labels[] = substr($day->format('D'), 0, 1);
        }

        return view('overview', [
            'balance' => (float) $user->wallet_balance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'weekIn' => (float) $rows->where('amount', '>', 0)->sum('amount'),
            'weekOut' => abs((float) $rows->where('amount', '<', 0)->sum('amount')),
            'flow' => $flow,
            'labels' => $labels,
            'qCoin' => (int) $user->q_coin_balance,
            'streak' => (int) $user->streak_days,
            'rankPoints' => $ranking->total($user, 'month'),
            'openGigs' => Gig::where('user_id', $id)->where('status', 'open')->count(),
            'completedGigs' => Gig::where('accepted_by', $id)->where('status', 'completed')->count(),
            'listings' => Listing::where('user_id', $id)->count(),
        ]);
    }
}
