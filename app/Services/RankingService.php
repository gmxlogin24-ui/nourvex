<?php

namespace App\Services;

use App\Models\RankPoint;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

class RankingService
{
    // $ref makes an award one-time (e.g. "login:2026-10-03"). Same ref twice = ignored.
    public function award(User $user, string $source, ?int $points = null, ?string $ref = null): ?RankPoint
    {
        $base = $points ?? (int) config("ranking.points.$source", 0);
        if ($base <= 0) {
            return null;
        }

        if ($ref !== null && RankPoint::where('user_id', $user->id)->where('source', $source)->where('ref', $ref)->exists()) {
            return null;
        }

        $mult = (float) config('ranking.plan_multiplier.' . $user->planKey(), 1);

        try {
            return RankPoint::create([
                'user_id' => $user->id,
                'source' => $source,
                'points' => (int) round($base * $mult),
                'ref' => $ref,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return null;
        }
    }

    public function since(string $period)
    {
        return match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };
    }

    public function total(User $user, string $period = 'all'): int
    {
        $q = RankPoint::where('user_id', $user->id);
        if ($since = $this->since($period)) {
            $q->where('created_at', '>=', $since);
        }
        return (int) $q->sum('points');
    }

    // $period: week | month | all. $area: null = everyone.
    public function board(string $period = 'week', ?string $area = null, int $limit = 20)
    {
        $q = RankPoint::query()
            ->join('users', 'users.id', '=', 'rank_points.user_id')
            ->where('users.leaderboard_opt_in', true)
            ->selectRaw('users.id, users.name, users.avatar_url, users.area, users.tier, SUM(rank_points.points) as total')
            ->groupBy('users.id', 'users.name', 'users.avatar_url', 'users.area', 'users.tier')
            ->orderByDesc('total')
            ->limit($limit);

        if ($since = $this->since($period)) {
            $q->where('rank_points.created_at', '>=', $since);
        }
        if ($area) {
            $q->where('users.area', $area);
        }

        return $q->get();
    }
}
