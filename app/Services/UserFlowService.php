<?php

namespace App\Services;

use App\Models\QCoinTransaction;
use App\Models\RankPoint;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class UserFlowService
{
    public function forMonth(User $user, ?Carbon $month = null): array
    {
        $start = ($month ?? now())->copy()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $tx = Transaction::where('user_id', $user->id)->whereBetween('created_at', [$start, $end]);
        $in = (float) (clone $tx)->where('amount', '>', 0)->sum('amount');
        $out = abs((float) (clone $tx)->where('amount', '<', 0)->sum('amount'));

        $byType = (clone $tx)->where('amount', '>', 0)
            ->selectRaw('type, SUM(amount) as total')->groupBy('type')
            ->pluck('total', 'type')->map(fn ($v) => (float) $v);

        $gigs = (float) ($byType['gig_earning'] ?? 0);
        $market = (float) ($byType['marketplace_sale'] ?? 0);
        $referrals = (float) ($byType['referral_bonus'] ?? 0);
        $other = max(0, $in - $gigs - $market - $referrals);

        $q = QCoinTransaction::where('user_id', $user->id)->whereBetween('created_at', [$start, $end]);

        return [
            'in' => $in,
            'out' => $out,
            'net' => $in - $out,
            'earnings' => [
                'Gigs' => $gigs,
                'Market sales' => $market,
                'Referrals' => $referrals,
                'Other money in' => $other,
            ],
            'q_earned' => (int) (clone $q)->where('amount', '>', 0)->sum('amount'),
            'q_spent' => abs((int) (clone $q)->where('amount', '<', 0)->sum('amount')),
            'q_profit_share' => (int) (clone $q)->where('type', 'profit_share')->sum('amount'),
            'points' => (int) RankPoint::where('user_id', $user->id)->whereBetween('created_at', [$start, $end])->sum('points'),
            'investments' => 0,
        ];
    }
}
