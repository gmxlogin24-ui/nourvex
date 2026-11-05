<?php

namespace App\Console\Commands;

use App\Models\PlatformRevenue;
use App\Models\QCoinTransaction;
use App\Models\User;
use App\Services\QCoinService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QCoinProfitShare extends Command
{
    protected $signature = 'qcoin:profit-share
        {--month= : Month to calculate, YYYY-MM (default: last month)}
        {--costs=0 : Costs for the month in naira, subtracted from revenue}
        {--apply : Credit Q Coins. Only has effect when qcoin.mode is live.}';

    protected $description = 'Recalculate the monthly Q Coin profit share. Test mode saves a preview only.';

    public function handle(QCoinService $qcoin): int
    {
        $month = $this->option('month') ?: now()->subMonth()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $mode = config('qcoin.mode', 'test');
        $live = $mode === 'live' && (bool) $this->option('apply');

        $existing = DB::table('profit_share_runs')->where('month', $month)->first();
        if ($existing && $existing->status === 'paid') {
            $this->warn("{$month} is already paid. Nothing to recalculate.");
            return self::SUCCESS;
        }

        $fin = app(\App\Services\FinanceService::class)->summary($start, $end);
        $revenue = (float) $fin['revenue_total'];
        $costs = (float) $this->option('costs') ?: (float) $fin['costs_total'];
        $profit = max(0, $revenue - $costs);
        $share = $fin['percent'] / 100;
        $rate = (float) config('qcoin.profit_share.q_per_naira', 100);
        $pool = round($profit * $share, 2);
        $poolQ = (int) floor($pool * $rate);

        $earners = QCoinTransaction::whereBetween('created_at', [$start, $end])
            ->where('amount', '>', 0)
            ->where('type', '!=', 'profit_share')
            ->selectRaw('user_id, SUM(amount) as earned')
            ->groupBy('user_id')
            ->pluck('earned', 'user_id')
            ->map(fn ($v) => (int) $v);
        $totalEarned = (int) $earners->sum();

        $allocations = [];
        if ($poolQ > 0 && $totalEarned > 0) {
            foreach ($earners as $uid => $earned) {
                $q = (int) floor($poolQ * $earned / $totalEarned);
                if ($q > 0) {
                    $allocations[] = ['user_id' => $uid, 'q_earned' => $earned, 'q_share' => $q];
                }
            }
        }
        $totalQ = array_sum(array_column($allocations, 'q_share'));

        $this->line("Month: {$month} · mode: {$mode}");
        $this->line('Platform revenue: ₦' . number_format($revenue, 2));
        $this->line('Costs: ₦' . number_format($costs, 2) . ' · Profit: ₦' . number_format($profit, 2));
        $this->line('Pool (' . (int) round($share * 100) . '%): ₦' . number_format($pool, 2) . ' = ' . number_format($poolQ) . ' Q');
        $this->line('Q earners: ' . $earners->count() . ' · Q earned: ' . number_format($totalEarned) . ' · To share: ' . number_format($totalQ) . ' Q');

        $runData = [
            'revenue' => $revenue,
            'costs' => $costs,
            'profit' => $profit,
            'pool' => $pool,
            'q_per_naira' => $rate,
            'pool_q' => $poolQ,
            'status' => $live ? 'paid' : 'preview',
            'mode' => $mode,
            'updated_at' => now(),
        ];

        if ($existing) {
            $runId = $existing->id;
            DB::table('profit_share_runs')->where('id', $runId)->update($runData);
        } else {
            $runId = DB::table('profit_share_runs')->insertGetId($runData + ['month' => $month, 'created_at' => now()]);
        }

        DB::table('profit_share_allocations')->where('run_id', $runId)->delete();
        $now = now();
        $rows = array_map(fn ($a) => $a + ['run_id' => $runId, 'credited_at' => null, 'created_at' => $now, 'updated_at' => $now], $allocations);
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('profit_share_allocations')->insert($chunk);
        }

        $this->table(['user_id', 'Q earned', 'Q share'], array_slice($allocations, 0, 25));

        if (!$live) {
            $this->comment('Preview saved. Test mode: nothing credited.');
            return self::SUCCESS;
        }

        $credited = 0;
        foreach ($allocations as $a) {
            DB::transaction(function () use ($a, $runId, $month, $qcoin, &$credited) {
                $already = QCoinTransaction::where('user_id', $a['user_id'])
                    ->where('type', 'profit_share')
                    ->where('reference_id', "profit:{$month}")
                    ->exists();
                if ($already) {
                    return;
                }
                $user = User::find($a['user_id']);
                if (!$user) {
                    return;
                }
                $qcoin->earn($user, $a['q_share'], 'profit_share', "Profit share for {$month}", "profit:{$month}");
                DB::table('profit_share_allocations')
                    ->where('run_id', $runId)
                    ->where('user_id', $a['user_id'])
                    ->update(['credited_at' => now()]);
                $credited++;
            });
        }

        $this->info("Credited {$credited} user(s) for {$month}.");
        return self::SUCCESS;
    }
}
