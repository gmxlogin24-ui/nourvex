<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SpendLimit
{
    // Types that take money OUT of the wallet. Plan payments, sign-up charges
    // and vendor fees are not listed, so they never count toward the daily limit.
    public const OUT_TYPES = [
        'transfer_sent', 'withdrawal', 'withdrawal_fee',
        'marketplace_escrow', 'service_purchase', 'gig_escrow', 'ad_wallet', 'marketplace_card',
    ];

    public static function usedToday(User $user): float
    {
        // Day starts at midnight in Lagos time.
        $start = now()->setTimezone('Africa/Lagos')->startOfDay()->setTimezone(config('app.timezone'));
        return (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereIn('type', self::OUT_TYPES)
            ->where('amount', '<', 0)
            ->where('created_at', '>=', $start)
            ->sum(DB::raw('-amount'));
    }

    public static function blockedReason(User $user, float $amount): ?string
    {
        if (!empty($user->is_admin)) {
            return null;
        }
        $limit = (float) config('kyc.daily_out.' . $user->kycLevel(), 0);
        $used = self::usedToday($user);
        if ($used + $amount > $limit) {
            return 'Daily limit reached for your account level. You have sent ₦' . number_format($used)
                . ' of ₦' . number_format($limit) . ' today. Verify your account to raise it.';
        }
        return null;
    }
}
