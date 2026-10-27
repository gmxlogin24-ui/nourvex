<?php

namespace App\Http\Controllers;

use App\Models\QCoinTransaction;
use App\Services\QCoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class GolfVexController extends Controller
{
    private function playedToday(int $userId): int
    {
        return QCoinTransaction::where('user_id', $userId)->where('type', 'game_entry')->whereDate('created_at', now()->toDateString())->count();
    }

    public function index()
    {
        $user = auth()->user();
        $history = QCoinTransaction::where('user_id', $user->id)->whereIn('type', ['game_entry', 'game_win'])->latest()->limit(10)->get();

        return view('golfvex.index', [
            'balance' => (int) $user->q_coin_balance,
            'clubs' => config('golfvex.clubs'),
            'cap' => (int) config('golfvex.daily_cap'),
            'played' => $this->playedToday($user->id),
            'history' => $history,
        ]);
    }

    public function play(Request $request, QCoinService $qcoin)
    {
        $clubs = config('golfvex.clubs');
        $data = $request->validate(['club' => 'required|string|in:' . implode(',', array_keys($clubs))]);
        $club = $clubs[$data['club']];
        $user = $request->user();
        $cap = (int) config('golfvex.daily_cap');

        if ($this->playedToday($user->id) >= $cap) {
            return response()->json(['ok' => false, 'message' => "Daily limit of {$cap} swings reached. Come back tomorrow."], 422);
        }

        try {
            $result = DB::transaction(function () use ($qcoin, $user, $club, $data) {
                $ref = 'gv:' . Str::uuid();
                $qcoin->spend($user, (int) $club['stake'], 'game_entry', 'NaGolf ' . $club['name'] . ' swing', $ref);
                $won = random_int(1, 100) <= (int) $club['chance'];
                $lie = null;
                if ($won) {
                    $qcoin->earn($user, (int) $club['payout'], 'game_win', 'NaGolf ' . $club['name'] . ' hole out', $ref . ':win');
                } else {
                    $lies = config('golfvex.lies');
                    $lie = $lies[random_int(0, count($lies) - 1)];
                }
                return ['won' => $won, 'lie' => $lie];
            });
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'won' => $result['won'],
            'lie' => $result['lie'],
            'stake' => (int) $club['stake'],
            'payout' => $result['won'] ? (int) $club['payout'] : 0,
            'balance' => (int) $user->fresh()->q_coin_balance,
            'played' => $this->playedToday($user->id),
            'cap' => $cap,
        ]);
    }
}
