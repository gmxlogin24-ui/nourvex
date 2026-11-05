<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminCoinController extends Controller
{
    private function safe(callable $f)
    {
        try { return $f(); } catch (\Throwable $e) { return null; }
    }

    public function index()
    {
        $rate = 20.0;
        foreach (['qcoin.rate', 'qcoin.display_rate', 'qcoin.per_naira', 'qcoin.q_per_naira', 'investment.qcoin_per_naira'] as $k) {
            $v = config($k);
            if (is_numeric($v) && $v > 0) { $rate = (float) $v; break; }
        }
        $daily = [];
        $fees = [];
        $defD = [0, 10, 15, 20];
        $defF = [0.05, 0.03, 0, 0];
        for ($t = 0; $t < 4; $t++) {
            $daily[] = (float) config("plans.rules.tier$t.qcoin_daily", config("plans.tier$t.qcoin_daily", $defD[$t]));
            $fees[] = (float) config("plans.rules.tier$t.gigs_commission", config("plans.tier$t.gigs_commission", $defF[$t]));
        }
        $hasTx = $this->safe(fn () => Schema::hasTable('q_coin_transactions'));
        $hasBal = $this->safe(fn () => Schema::hasColumn('users', 'q_coin_balance'));
        $tiers = $this->safe(fn () => DB::table('users')->selectRaw('tier, count(*) as c')->groupBy('tier')->pluck('c', 'tier')->all()) ?: [];
        $day = now()->subDay();
        $data = [
            'rate' => $rate,
            'users' => (int) $this->safe(fn () => DB::table('users')->count()),
            'tiers' => $tiers,
            'daily' => $daily,
            'fees' => $fees,
            'platformFee' => (float) config('investment.platform_fee_percent', 5),
            'supply' => $hasBal ? (float) $this->safe(fn () => DB::table('users')->sum('q_coin_balance')) : 0,
            'holders' => $hasBal ? (int) $this->safe(fn () => DB::table('users')->where('q_coin_balance', '>', 0)->count()) : 0,
            'tx' => $hasTx ? (int) $this->safe(fn () => DB::table('q_coin_transactions')->count()) : 0,
            'tx24' => $hasTx ? (int) $this->safe(fn () => DB::table('q_coin_transactions')->where('created_at', '>=', $day)->count()) : 0,
            'in24' => $hasTx ? (float) $this->safe(fn () => DB::table('q_coin_transactions')->where('created_at', '>=', $day)->where('amount', '>', 0)->sum('amount')) : 0,
            'out24' => $hasTx ? (float) $this->safe(fn () => DB::table('q_coin_transactions')->where('created_at', '>=', $day)->where('amount', '<', 0)->sum('amount')) : 0,
            'types' => $hasTx ? ($this->safe(fn () => DB::table('q_coin_transactions')->where('created_at', '>=', now()->subDays(30))
                ->select('type', DB::raw('count(*) as c'), DB::raw('sum(amount) as s'))->groupBy('type')->orderByDesc('c')->limit(12)->get()
                ->map(fn ($r) => ['type' => (string) $r->type, 'c' => (int) $r->c, 's' => round((float) $r->s, 2)])->values()->all()) ?: []) : [],
            'cfg' => collect(config('qcoin', []))->filter(fn ($v, $k) => is_scalar($v) && !preg_match('/key|secret|token|pass/i', (string) $k))->all(),
        ];
        return view('admin.coin', ['data' => $data]);
    }
}
