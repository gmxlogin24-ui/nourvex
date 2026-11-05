<?php
namespace App\Support;

class Money
{
    public static function rate(): float
    {
        $r = (float) config('qcoin.q_per_naira_display', 10);
        return $r > 0 ? $r : 10.0;
    }
    public static function toQ($naira): float
    {
        return round((float) $naira * self::rate(), 2);
    }
    public static function toNaira($q): float
    {
        return round((float) $q / self::rate(), 2);
    }
    private static function fmtQ(float $q): string
    {
        $s = number_format(abs($q), 2);
        return 'Q ' . (substr($s, -3) === '.00' ? substr($s, 0, -3) : $s);
    }
    public static function naira($naira): string
    {
        $n = (float) $naira;
        return ($n < 0 ? '-' : '') . '₦' . number_format(abs($n), 2);
    }
    public static function q($naira): string
    {
        $q = self::toQ($naira);
        return ($q < 0 ? '-' : '') . self::fmtQ($q);
    }
    // Rows already stored in Q (ad payments) must not be multiplied by the rate again.
    public static function coinQ($q): string
    {
        $q = (float) $q;
        return '<span class="mny"><span class="mny-q">' . e(($q < 0 ? '-' : '') . self::fmtQ($q)) . '</span>'
            . '<small class="mny-n" style="display:block;font-size:.7em;font-weight:500;opacity:.65">≈ '
            . e(self::naira($q / self::rate())) . '</small></span>';
    }

    public static function forTx($type, $amount): string
    {
        return $type === 'ad_post' ? self::coinQ($amount) : self::coin($amount);
    }

    public static function coin($naira): string
    {
        return '<span class="mny"><span class="mny-n">' . e(self::naira($naira)) . '</span></span>';
    }
}
