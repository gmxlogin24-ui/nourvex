<?php

namespace App\Support;

use App\Models\AppSetting;

class Pricing
{
    // Ad price in Q coin per duration (days). Falls back to config/qcoin.php until set in admin.
    public static function adPrices(): array
    {
        $base = (array) config('qcoin.ad_prices', [3 => 1500, 7 => 3000, 30 => 10000]);
        $saved = json_decode((string) AppSetting::get('pricing.ad_prices', ''), true);
        $out = [];
        foreach ([3, 7, 30] as $d) {
            $v = is_array($saved) && isset($saved[$d]) ? $saved[$d] : ($base[$d] ?? 0);
            $out[$d] = max(0, (int) $v);
        }
        return $out;
    }

    // Fee, in percent, that accounts without a virtual account pay to withdraw.
    public static function withdrawalFeePercent(): float
    {
        return max(0, min(10, (float) AppSetting::get('pricing.withdrawal_fee_percent', 1.5)));
    }

    public static function withdrawalFeeRate(): float
    {
        return self::withdrawalFeePercent() / 100;
    }

    public static function withdrawalFeeLabel(): string
    {
        return rtrim(rtrim(number_format(self::withdrawalFeePercent(), 2), '0'), '.') . '%';
    }
}
