<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function index(Request $request, RankingService $ranking)
    {
        $user = Auth::user();
        $areas = config('areas.list');

        $type = $request->query('type') === 'earnings' ? 'earnings' : 'points';
        $period = in_array($request->query('period'), ['week', 'month', 'all'], true) ? $request->query('period') : 'week';
        $area = array_key_exists((string) $request->query('area'), $areas) ? $request->query('area') : null;

        if ($type === 'earnings') {
            $q = Transaction::query()
                ->where('transactions.amount', '>', 0)
                ->whereIn('transactions.type', ['gig_earning', 'marketplace_sale', 'referral_bonus'])
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->where('users.leaderboard_opt_in', true)
                ->selectRaw('users.id, users.name, users.area, SUM(transactions.amount) as total')
                ->groupBy('users.id', 'users.name', 'users.area')
                ->orderByDesc('total')
                ->take(50);

            if ($since = $ranking->since($period)) {
                $q->where('transactions.created_at', '>=', $since);
            }
            if ($area) {
                $q->where('users.area', $area);
            }
            $rows = $q->get();
        } else {
            $rows = $ranking->board($period, $area, 50);
        }

        $myRank = null;
        foreach ($rows as $i => $row) {
            if ((int) $row->id === (int) $user->id) {
                $myRank = $i + 1;
                break;
            }
        }

        return view('leaderboard.index', [
            'rows' => $rows,
            'type' => $type,
            'period' => $period,
            'area' => $area,
            'areas' => $areas,
            'optedIn' => $user->leaderboard_opt_in,
            'myPoints' => $ranking->total($user, $period),
            'myRank' => $myRank,
        ]);
    }

    public function toggleOptIn(Request $request)
    {
        $user = Auth::user();
        $user->update(['leaderboard_opt_in' => !$user->leaderboard_opt_in]);

        return back()->with('status', $user->leaderboard_opt_in ? 'You\'re now on the leaderboard!' : 'You\'ve been removed from the leaderboard.');
    }
}
