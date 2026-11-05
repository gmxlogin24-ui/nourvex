<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileWallets extends Command
{
    protected $signature = 'wallets:reconcile';
    protected $description = 'Compare wallet_balance with the wallet-affecting transactions. Read-only.';

    // These transaction types are not always wallet movements: ad_post is paid in Q coin,
    // tier_upgrade and signup_fee are also written for card payments. They are handled apart.
    private const SKIP = ['ad_post', 'tier_upgrade', 'signup_fee', 'vendor_registration', 'marketplace_card', 'marketplace_sale_bank'];

    public function handle(): int
    {
        $sums = DB::table('transactions')->whereNotIn('type', self::SKIP)
            ->select('user_id', DB::raw('SUM(amount) as total'))->groupBy('user_id')->pluck('total', 'user_id');
        $walletSubs = DB::table('subscriptions')->where('payment_method', 'wallet')->whereIn('status', ['active', 'expired'])
            ->select('user_id', DB::raw('SUM(amount) as total'))->groupBy('user_id')->pluck('total', 'user_id');

        $bad = 0;
        $checked = 0;
        foreach (User::select('id', 'wallet_balance')->cursor() as $u) {
            $checked++;
            $expected = round((float) ($sums[$u->id] ?? 0) - (float) ($walletSubs[$u->id] ?? 0), 2);
            $stored = round((float) $u->wallet_balance, 2);
            if (abs($expected - $stored) > 0.01) {
                $bad++;
                $this->warn("user {$u->id}: wallet_balance {$stored}, transactions say {$expected}");
            }
        }
        $this->line('Skipped types (not always wallet money): ' . implode(', ', self::SKIP));
        $this->info($bad ? "{$bad} of {$checked} wallets differ. Wallet-paid signup fees show up here until they are separated." : "All {$checked} wallets match.");
        return $bad ? self::FAILURE : self::SUCCESS;
    }
}
