<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Level;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recalculates a user's level and badges from their real activity metrics.
 * Safe to call after ANY significant action (gig completed, referral,
 * daily claim, sale, verification). Idempotent: nothing double-awards.
 */
class LevelBadgeService
{
    /** Badge criteria_type => metric key. */
    protected const BADGE_METRICS = [
        'gigs_completed' => 'gigs_completed',
        'sales' => 'sales',
        'referrals' => 'referrals',
        'q_coins' => 'q_coins_earned',
        'streak' => 'streak_days',
        'level' => 'level_number',
        'verified' => 'verified',
        'profile_complete' => 'profile_complete',
        'early_member' => 'user_number',
    ];

    protected static array $schemaCache = [];

    public function __construct(protected QCoinService $qCoins) {}

    public function refresh(User $user): array
    {
        return DB::transaction(function () use ($user) {
            /** @var User $locked */
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $metrics = $this->metrics($locked);
            [$level, $levelUp] = $this->syncLevel($locked, $metrics);

            $metrics['level_number'] = $level?->rank ?? 0;
            $newBadges = $this->syncBadges($locked, $metrics);

            $user->refresh();

            return [
                'metrics' => $metrics,
                'level' => $level,
                'level_up' => $levelUp,
                'new_badges' => $newBadges,
            ];
        });
    }

    public function metrics(User $user): array
    {
        $createdAt = $user->created_at;

        return [
            'q_coins_earned' => $this->qCoinsEarned($user),
            'q_coin_balance' => (int) ($user->q_coin_balance ?? 0),
            'streak_days' => (int) ($user->q_coin_streak_days ?? 0),
            'gigs_completed' => $this->countRows('gigs', $user, ['status' => 'completed']),
            'sales' => $this->salesCount($user),
            'referrals' => $this->countRows('users', $user, [], 'referred_by'),
            'account_age_days' => $createdAt ? (int) $createdAt->diffInDays(now()) : 0,
            'verified' => (int) (bool) ($user->is_verified_seller ?? 0),
            'profile_complete' => $this->profileCompletion($user),
            'user_number' => (int) $user->getKey(),
            'level_number' => (int) ($user->level?->rank ?? 0),
        ];
    }

    /** Assign the highest level whose requirements are satisfied. */
    protected function syncLevel(User $user, array $metrics): array
    {
        $earned = Level::active()->orderBy('rank')->get()
            ->filter(fn (Level $level) => $level->requirementsMet($metrics))
            ->last();

        if (! $earned || (int) $user->level_id === (int) $earned->id) {
            return [$earned ?? $user->level, false];
        }

        $previous = (int) ($user->level?->rank ?? 0);
        $user->level_id = $earned->id;
        $user->save();

        return [$earned, $earned->rank > $previous];
    }

    /** Award every unowned badge whose criteria are met. */
    protected function syncBadges(User $user, array $metrics): array
    {
        $owned = DB::table('user_badges')->where('user_id', $user->getKey())
            ->pluck('badge_id')->all();

        $awarded = [];

        foreach (Badge::active()->orderBy('id')->get() as $badge) {
            if (in_array($badge->id, $owned) || ! $this->badgeEarned($badge, $metrics)) {
                continue;
            }

            DB::table('user_badges')->insertOrIgnore([
                'user_id' => $user->getKey(),
                'badge_id' => $badge->id,
                'earned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $awarded[] = $badge;
        }

        return $awarded;
    }

    public function badgeEarned(Badge $badge, array $metrics): bool
    {
        $key = self::BADGE_METRICS[$badge->criteria_type] ?? null;

        if ($key === null) {
            return false;
        }

        $current = (int) ($metrics[$key] ?? 0);

        if ($badge->criteria_type === 'early_member') {
            return $current > 0 && $current <= max(1, $badge->criteria_value);
        }

        return $current >= max(1, $badge->criteria_value);
    }

    public function badgeProgress(Badge $badge, array $metrics): array
    {
        $key = self::BADGE_METRICS[$badge->criteria_type] ?? null;
        $target = max(1, $badge->criteria_value);
        $current = (int) ($metrics[$key] ?? 0);

        if ($badge->criteria_type === 'early_member') {
            return ['current' => $current, 'target' => $target, 'percent' => $this->badgeEarned($badge, $metrics) ? 100 : 0];
        }

        return [
            'current' => min($current, $target),
            'target' => $target,
            'percent' => (int) min(100, round($current / $target * 100)),
        ];
    }

    public function nextLevel(array $metrics): ?Level
    {
        return Level::active()->orderBy('rank')->get()
            ->first(fn (Level $level) => ! $level->requirementsMet($metrics));
    }

    /** Per-requirement progress rows for the next level. */
    public function levelProgress(?Level $level, array $metrics): array
    {
        if (! $level) {
            return [];
        }

        $labels = [
            'q_coins_earned' => 'Q Coins earned',
            'gigs_completed' => 'Gigs completed',
            'referrals' => 'Referrals',
        ];

        $rows = [];

        foreach ($level->gates() as $metric => $required) {
            $current = (int) ($metrics[$metric] ?? 0);
            $rows[] = [
                'label' => $labels[$metric] ?? $metric,
                'current' => $current,
                'target' => (int) $required,
                'percent' => (int) min(100, round($current / max(1, $required) * 100)),
                'met' => $current >= $required,
            ];
        }

        if ($level->requires_verified) {
            $rows[] = [
                'label' => 'Verified seller',
                'current' => (int) ($metrics['verified'] ?? 0),
                'target' => 1,
                'percent' => empty($metrics['verified']) ? 0 : 100,
                'met' => ! empty($metrics['verified']),
            ];
        }

        return $rows;
    }

    // ------------------------------------------------------------- helpers

    protected function qCoinsEarned(User $user): int
    {
        if (! $this->hasTable('q_coin_transactions')) {
            return (int) ($user->q_coin_balance ?? 0);
        }

        return (int) DB::table('q_coin_transactions')
            ->where('user_id', $user->getKey())
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    protected function salesCount(User $user): int
    {
        foreach (['orders' => 'seller_id', 'transactions' => 'seller_id', 'listings' => 'user_id'] as $table => $column) {
            if ($this->hasTable($table) && $this->hasColumn($table, $column)) {
                $query = DB::table($table)->where($column, $user->getKey());
                if ($this->hasColumn($table, 'status')) {
                    $query->whereIn('status', ['completed', 'delivered', 'paid', 'sold']);
                }

                return (int) $query->count();
            }
        }

        return 0;
    }

    protected function countRows(string $table, User $user, array $where = [], ?string $ownerColumn = null): int
    {
        if (! $this->hasTable($table)) {
            return 0;
        }

        $column = $ownerColumn ?? 'user_id';

        if (! $this->hasColumn($table, $column)) {
            return 0;
        }

        $query = DB::table($table)->where($column, $user->getKey());

        foreach ($where as $col => $val) {
            if ($this->hasColumn($table, $col)) {
                $query->where($col, $val);
            }
        }

        return (int) $query->count();
    }

    protected function profileCompletion(User $user): int
    {
        $fields = ['name', 'email', 'avatar_url'];
        $filled = 0;

        foreach ($fields as $field) {
            if (! empty($user->{$field})) {
                $filled++;
            }
        }

        return (int) round($filled / count($fields) * 100);
    }

    protected function hasTable(string $table): bool
    {
        return self::$schemaCache["table:{$table}"] ??= Schema::hasTable($table);
    }

    protected function hasColumn(string $table, string $column): bool
    {
        return self::$schemaCache["col:{$table}.{$column}"] ??= Schema::hasColumn($table, $column);
    }
}
