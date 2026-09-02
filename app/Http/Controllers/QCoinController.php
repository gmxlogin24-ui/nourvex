<?php

namespace App\Http\Controllers;

use App\Models\QCoinTransaction;
use App\Services\QCoinService;
use RuntimeException;

class QCoinController extends Controller
{
    public function index(QCoinService $qcoin)
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $todayEarned = QCoinTransaction::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalEarned = QCoinTransaction::where('user_id', $user->id)->where('amount', '>', 0)->sum('amount');
        $totalSpent = abs(QCoinTransaction::where('user_id', $user->id)->where('amount', '<', 0)->sum('amount'));

        $history = QCoinTransaction::where('user_id', $user->id)->latest()->limit(30)->get();

        $canClaimToday = $user->q_coin_last_claim_date !== $today;
        $nextStreakBonus = min((($user->q_coin_last_claim_date === now()->subDay()->toDateString() ? $user->q_coin_streak_days + 1 : 1)) * 5, 100);

        return view('q-coin.index', [
            'pageKey' => 'qcoin',
            'balance' => $user->q_coin_balance,
            'todayEarned' => $todayEarned,
            'totalEarned' => $totalEarned,
            'totalSpent' => $totalSpent,
            'streakDays' => $user->q_coin_streak_days,
            'canClaimToday' => $canClaimToday,
            'nextStreakBonus' => $nextStreakBonus,
            'history' => $history,
        ]);
    }

    public function claim(QCoinService $qcoin)
    {
        try {
            $txn = $qcoin->claimDaily(auth()->user());
        } catch (RuntimeException $e) {
            return back()->with('error', "You've already claimed today's Q Coins.");
        }

        app(\App\Services\LevelBadgeService::class)->refresh(auth()->user());
        return back()->with('success', 'You claimed ' . $txn->amount . ' Q Coins!');
    }
}
