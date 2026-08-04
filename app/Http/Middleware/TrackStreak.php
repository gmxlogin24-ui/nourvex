<?php

namespace App\Http\Middleware;

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
            }
        }

        return $next($request);
    }
}
