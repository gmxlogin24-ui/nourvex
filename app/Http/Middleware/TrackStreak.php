<?php

namespace App\Http\Middleware;

use App\Services\RankingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrackStreak
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $today = Carbon::today();

            if (!$user->last_active_date || !$today->isSameDay($user->last_active_date)) {
                $yesterday = $today->copy()->subDay();
                $newStreak = ($user->last_active_date && $yesterday->isSameDay($user->last_active_date))
                    ? $user->streak_days + 1
                    : 1;

                $user->update([
                    'streak_days' => $newStreak,
                    'last_active_date' => $today,
                ]);

                try {
                    $ranking = app(RankingService::class);
                    $day = $today->toDateString();
                    $ranking->award($user, 'daily_login', null, 'login:' . $day);
                    if ($newStreak === 7) {
                        $ranking->award($user, 'streak_7', null, 'streak7:' . $day);
                    }
                    if ($newStreak === 30) {
                        $ranking->award($user, 'streak_30', null, 'streak30:' . $day);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return $next($request);
    }
}
