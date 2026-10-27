<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\UserFlowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitStatsController extends Controller
{
    public function index(UserFlowService $flow)
    {
        $month = request('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $run = DB::table('profit_share_runs')->where('month', $month)->first();
        $allocs = $run
            ? DB::table('profit_share_allocations')
                ->join('users', 'users.id', '=', 'profit_share_allocations.user_id')
                ->where('profit_share_allocations.run_id', $run->id)
                ->orderByDesc('profit_share_allocations.q_share')
                ->select('users.id', 'users.name', 'profit_share_allocations.q_earned', 'profit_share_allocations.q_share', 'profit_share_allocations.credited_at')
                ->limit(200)->get()
            : collect();

        $runs = DB::table('profit_share_runs')->orderByDesc('month')->limit(12)->get();

        $ids = Transaction::whereBetween('created_at', [$start, $end])->distinct()->pluck('user_id')->take(200);
        $users = User::whereIn('id', $ids)->get()->map(fn ($u) => ['user' => $u, 'flow' => $flow->forMonth($u, $start)]);

        return view('admin.profit.index', compact('month', 'run', 'allocs', 'runs', 'users'));
    }

    public function user(User $user, UserFlowService $flow)
    {
        $month = request('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();

        return view('admin.profit.user', [
            'target' => $user,
            'month' => $month,
            'flow' => $flow->forMonth($user, $start),
        ]);
    }
}
