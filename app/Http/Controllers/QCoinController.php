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
        $nextStreak = $user->q_coin_last_claim_date === now()->subDay()->toDateString() ? $user->q_coin_streak_days + 1 : 1;
        $nextStreakBonus = $qcoin->streakBonusFor($nextStreak);
        $dailyBase = $qcoin->baseFor($user);

        return view('q-coin.index', [
            'pageKey' => 'qcoin',
            'balance' => $user->q_coin_balance,
            'todayEarned' => $todayEarned,
            'totalEarned' => $totalEarned,
            'totalSpent' => $totalSpent,
            'streakDays' => $user->q_coin_streak_days,
            'canClaimToday' => $canClaimToday,
            'nextStreakBonus' => $nextStreakBonus,
            'dailyBase' => $dailyBase,
            'qcoinLocked' => $dailyBase <= 0,
            'history' => $history,
        ]);
    }

    public function claim(QCoinService $qcoin)
    {
        $user = auth()->user();

        if ($qcoin->baseFor($user) <= 0) {
            return back()->with('error', 'Daily Q Coin claims start on the Silver plan. Upgrade to unlock them.');
        }

        try {
            $txn = $qcoin->claimDaily($user);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage() === 'Already claimed today.'
                ? "You've already claimed today's Q Coins."
                : $e->getMessage());
        }

        app(\App\Services\LevelBadgeService::class)->refresh($user);

        try {
            app(\App\Services\RankingService::class)->award($user, 'qcoin_claim', null, 'claim:' . now()->toDateString());
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'You claimed ' . $txn->amount . ' Q Coins!');
    }
}
