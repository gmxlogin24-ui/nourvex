<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\PlatformRevenue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceService
{
    // Paystack Nigeria published pricing (support.paystack.com), checked 29 Oct 2026.
    // key => [label, default, group]. All editable on /admin/finance.
    public const PAYSTACK_FIELDS = [
        'paystack.checkout_percent' => ['Fee %', 1.5, 'Deposits by card or checkout'],
        'paystack.checkout_flat' => ['Flat fee (₦)', 100, 'Deposits by card or checkout'],
        'paystack.checkout_flat_from' => ['Flat fee applies from (₦)', 2500, 'Deposits by card or checkout'],
        'paystack.checkout_cap' => ['Fee cap (₦, 0 = none)', 2000, 'Deposits by card or checkout'],
        'paystack.dva_percent' => ['Fee %', 1, 'Deposits by dedicated virtual account'],
        'paystack.dva_cap' => ['Fee cap (₦, 0 = none)', 300, 'Deposits by dedicated virtual account'],
        'paystack.transfer_t1_max' => ['Tier 1 up to (₦)', 5000, 'Payouts to banks (transfers)'],
        'paystack.transfer_t1_fee' => ['Tier 1 fee (₦)', 10, 'Payouts to banks (transfers)'],
        'paystack.transfer_t2_max' => ['Tier 2 up to (₦)', 50000, 'Payouts to banks (transfers)'],
        'paystack.transfer_t2_fee' => ['Tier 2 fee (₦)', 25, 'Payouts to banks (transfers)'],
        'paystack.transfer_t3_fee' => ['Above tier 2 fee (₦)', 50, 'Payouts to banks (transfers)'],
        'paystack.stamp_duty' => ['Stamp duty per payout (₦)', 50, 'Stamp duty on payouts'],
        'paystack.stamp_duty_from' => ['Applies to payouts from (₦)', 10000, 'Stamp duty on payouts'],
    ];

    public const EXPENSE_CATEGORIES = [
        'hosting' => 'Hosting and servers',
        'sms_email' => 'SMS and email',
        'salaries' => 'Salaries and contractors',
        'marketing' => 'Marketing and ads',
        'paystack_adjustment' => 'Paystack fee correction (can be negative)',
        'other' => 'Other',
    ];

    // How each known transaction type is treated. Anything not listed is flagged
    // "not mapped yet" on the finance page so nothing is silently miscounted.
    public const TYPE_NOTES = [
        'withdrawal' => 'Customer money leaving. Not revenue.',
        'withdrawal_fee' => 'Counted from the withdrawals table (failed ones excluded).',
        'withdrawal_reversal' => 'Customer money returned. Not revenue.',
        'tier_upgrade' => 'Counted from the subscriptions table (avoids double counting).',
        'service_purchase' => 'Pass-through. Only the margin counts, via platform revenue (vtu).',
        'service_refund' => 'Pass-through refund. Not revenue.',
        'ad_post' => 'Ads are paid in Q coin, not naira. Not revenue; it takes Q out of circulation.',
        'signup_fee' => 'Revenue, unless it bought a plan (then it is counted under subscriptions).',
        'referral_bonus' => 'Cost. Counted as referral bonuses paid.',
        'wallet_topup' => 'Customer money in. Not revenue. The Paystack fee is the cost.',
        'transfer_sent' => 'User to user. Not revenue.',
        'transfer_received' => 'User to user. Not revenue.',
        'gig_escrow' => 'Held for a gig. Not revenue. Commission counts via platform revenue.',
        'gig_escrow_refund' => 'Gig money returned. Not revenue.',
        'gig_refund' => 'Gig money returned. Not revenue.',
        'gig_earning' => 'Paid to a worker. Commission counts via platform revenue.',
        'marketplace_escrow' => 'Held for a sale. Not revenue. Commission counts via platform revenue.',
        'marketplace_sale' => 'Paid to a seller. Commission counts via platform revenue.',
        'marketplace_refund' => 'Sale money returned. Not revenue.',
        'admin_adjustment' => 'Manual admin change to a balance. Review these; not counted.',
        'vendor_registration' => 'Revenue. Counted as vendor registration fees.',
        'investment' => 'Investor money held for a project. Not revenue.',
        'investment_release' => 'Held money released to the project owner. Not revenue.',
        'investment_return' => 'Returns paid to investors. Not revenue. The platform fee counts via platform revenue (investments).',
        'investment_refund' => 'Investment money returned. Not revenue.',
    ];

    public function percent(): float
    {
        $default = (float) config('qcoin.profit_share.pool_share', 0.4) * 100;
        return max(0, min(100, (float) AppSetting::get('profit.pool_percent', $default)));
    }

    public function setting(string $key): float
    {
        return (float) AppSetting::get($key, self::PAYSTACK_FIELDS[$key][1] ?? 0);
    }

    public function dvaTypes(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) (AppSetting::get('paystack.dva_types') ?: 'wallet_topup')))));
    }

    public function dvaDescription(): string
    {
        return (string) (AppSetting::get('paystack.dva_description') ?: 'Wallet funded via Paystack');
    }

    public function checkoutFee(float $a): float
    {
        $fee = $a * $this->setting('paystack.checkout_percent') / 100
            + ($a >= $this->setting('paystack.checkout_flat_from') ? $this->setting('paystack.checkout_flat') : 0);
        $cap = $this->setting('paystack.checkout_cap');
        return $cap > 0 ? min($cap, $fee) : $fee;
    }

    public function dvaFee(float $a): float
    {
        $fee = $a * $this->setting('paystack.dva_percent') / 100;
        $cap = $this->setting('paystack.dva_cap');
        return $cap > 0 ? min($cap, $fee) : $fee;
    }

    public function transferFee(float $a): float
    {
        if ($a <= $this->setting('paystack.transfer_t1_max')) {
            return $this->setting('paystack.transfer_t1_fee');
        }
        if ($a <= $this->setting('paystack.transfer_t2_max')) {
            return $this->setting('paystack.transfer_t2_fee');
        }
        return $this->setting('paystack.transfer_t3_fee');
    }

    public function stampDuty(float $a): float
    {
        return $a >= $this->setting('paystack.stamp_duty_from') ? $this->setting('paystack.stamp_duty') : 0.0;
    }

    public function summary(Carbon $start, Carbon $end): array
    {
        $range = [$start->copy()->startOfDay(), $end->copy()->endOfDay()];

        // ---- revenue ----
        $revenue = ['Gig commissions' => 0.0, 'Marketplace commissions' => 0.0, 'VTU margin' => 0.0];
        $labels = ['gigs' => 'Gig commissions', 'marketplace' => 'Marketplace commissions', 'vtu' => 'VTU margin'];
        if (Schema::hasTable('platform_revenue')) {
            $rows = PlatformRevenue::whereBetween('created_at', $range)
                ->selectRaw('source, SUM(amount) as t')->groupBy('source')->pluck('t', 'source');
            foreach ($rows as $src => $t) {
                $revenue[$labels[$src] ?? ucfirst($src) . ' revenue'] = (float) $t;
            }
        }
        $revenue['Subscriptions and tier upgrades'] = (float) DB::table('subscriptions')
            ->whereIn('status', ['active', 'expired'])->whereBetween('starts_at', $range)->sum('amount');
        $revenue['Withdrawal fees'] = (float) DB::table('withdrawals')
            ->where('status', '!=', 'failed')->whereBetween('created_at', $range)->sum('fee');
        $revenue['Signup fees'] = (float) abs(DB::table('transactions')->where('type', 'signup_fee')->where('description', 'not like', 'Subscribed to%')->whereBetween('created_at', $range)->sum('amount'));
        $revenue['Vendor registration fees'] = (float) abs(DB::table('transactions')->where('type', 'vendor_registration')->whereBetween('created_at', $range)->sum('amount'));
        $revenueTotal = round(array_sum($revenue), 2);

        // ---- costs: Paystack fees from your editable schedule ----
        $checkout = 0.0;
        foreach (DB::table('wallet_topups')
            ->whereNotIn('status', ['pending', 'failed', 'abandoned', 'cancelled'])
            ->whereBetween('created_at', $range)->pluck('amount') as $a) {
            $checkout += $this->checkoutFee((float) $a);
        }
        foreach (DB::table('subscriptions')->where('payment_method', 'paystack')->whereNotNull('paystack_tx_ref')
            ->whereIn('status', ['active', 'expired'])->whereBetween('starts_at', $range)->pluck('amount') as $a) {
            $checkout += $this->checkoutFee((float) $a);
        }
        $dva = 0.0;
        if ($this->dvaTypes()) {
            foreach (DB::table('transactions')->whereIn('type', $this->dvaTypes())->where('description', $this->dvaDescription())->where('amount', '>', 0)
                ->whereBetween('created_at', $range)->pluck('amount') as $a) {
                $dva += $this->dvaFee((float) $a);
            }
        }
        $payout = 0.0;
        $stamp = 0.0;
        foreach (DB::table('withdrawals')->where('status', '!=', 'failed')->whereBetween('created_at', $range)->pluck('amount') as $a) {
            $payout += $this->transferFee((float) $a);
            $stamp += $this->stampDuty((float) $a);
        }
        $referral = (float) DB::table('transactions')->where('type', 'referral_bonus')->where('amount', '>', 0)->whereBetween('created_at', $range)->sum('amount');
        $costs = [
            'Referral bonuses paid to users' => round($referral, 2),
            'Paystack card/checkout deposit fees' => round($checkout, 2),
            'Paystack virtual account deposit fees' => round($dva, 2),
            'Paystack payout fees' => round($payout, 2),
            'Stamp duty on payouts' => round($stamp, 2),
        ];
        if (Schema::hasTable('expenses')) {
            $byCat = DB::table('expenses')->whereBetween('incurred_on', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('category, SUM(amount) as t')->groupBy('category')->pluck('t', 'category');
            foreach (self::EXPENSE_CATEGORIES as $key => $label) {
                if (isset($byCat[$key])) {
                    $costs[$label] = (float) $byCat[$key];
                }
            }
        }
        $costsTotal = round(array_sum($costs), 2);

        // ---- profit and pool ----
        $profit = round($revenueTotal - $costsTotal, 2);
        $percent = $this->percent();
        $pool = round(max(0, $profit) * $percent / 100, 2);
        $qRate = (float) config('qcoin.profit_share.q_per_naira', 10);

        return [
            'revenue' => $revenue,
            'revenue_total' => $revenueTotal,
            'costs' => $costs,
            'costs_total' => $costsTotal,
            'profit' => $profit,
            'percent' => $percent,
            'pool' => $pool,
            'pool_q' => (int) floor($pool * $qRate),
            'customer_money' => round((float) DB::table('users')->sum('wallet_balance'), 2),
            'pending_withdrawals' => round((float) DB::table('withdrawals')->where('status', 'pending')->selectRaw('COALESCE(SUM(amount + fee),0) as t')->value('t'), 2),
        ];
    }

    public function transactionTypes(Carbon $start, Carbon $end)
    {
        return DB::table('transactions')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('type, COUNT(*) as c, SUM(amount) as s')->groupBy('type')->orderByDesc('c')->get()
            ->map(function ($r) {
                $r->note = self::TYPE_NOTES[$r->type] ?? null;
                return $r;
            });
    }
}
