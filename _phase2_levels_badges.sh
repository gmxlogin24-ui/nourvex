set -e
echo "=== PHASE 2: Levels & Badges ==="

# ---------------------------------------------------------------- MIGRATIONS
cat > database/migrations/2026_09_01_000001_create_levels_table.php << 'MIG1'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('level_number')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('star');
            $table->string('color')->default('blue');
            $table->unsignedBigInteger('min_q_coins')->default(0);
            $table->unsignedInteger('min_gigs_completed')->default(0);
            $table->unsignedInteger('min_sales')->default(0);
            $table->unsignedInteger('min_referrals')->default(0);
            $table->unsignedInteger('min_account_age_days')->default(0);
            $table->boolean('requires_verified')->default(false);
            $table->json('perks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
MIG1

cat > database/migrations/2026_09_01_000002_create_badges_tables.php << 'MIG2'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('award');
            $table->string('color')->default('amber');
            $table->string('criteria_type');
            $table->unsignedBigInteger('criteria_value')->default(1);
            $table->unsignedInteger('q_coin_reward')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
MIG2

cat > database/migrations/2026_09_01_000003_add_level_to_users_table.php << 'MIG3'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'current_level_id')) {
                $table->foreignId('current_level_id')->nullable()->after('q_coin_streak_days');
            }
            if (! Schema::hasColumn('users', 'level_achieved_at')) {
                $table->timestamp('level_achieved_at')->nullable()->after('current_level_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_level_id')) {
                $table->dropColumn('current_level_id');
            }
            if (Schema::hasColumn('users', 'level_achieved_at')) {
                $table->dropColumn('level_achieved_at');
            }
        });
    }
};
MIG3
echo "migrations created"

# -------------------------------------------------------------------- MODELS
cat > app/Models/Level.php << 'MODEL1'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'key', 'name', 'level_number', 'description', 'icon', 'color',
        'min_q_coins', 'min_gigs_completed', 'min_sales', 'min_referrals',
        'min_account_age_days', 'requires_verified', 'perks', 'is_active',
    ];

    protected $casts = [
        'level_number' => 'integer',
        'min_q_coins' => 'integer',
        'min_gigs_completed' => 'integer',
        'min_sales' => 'integer',
        'min_referrals' => 'integer',
        'min_account_age_days' => 'integer',
        'requires_verified' => 'boolean',
        'is_active' => 'boolean',
        'perks' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'current_level_id');
    }

    /** All numeric gates as [metric_key => required_value]. */
    public function gates(): array
    {
        return array_filter([
            'q_coins_earned' => $this->min_q_coins,
            'gigs_completed' => $this->min_gigs_completed,
            'sales' => $this->min_sales,
            'referrals' => $this->min_referrals,
            'account_age_days' => $this->min_account_age_days,
        ]);
    }

    public function requirementsMet(array $metrics): bool
    {
        if ($this->requires_verified && empty($metrics['verified'])) {
            return false;
        }

        foreach ($this->gates() as $metric => $required) {
            if (($metrics[$metric] ?? 0) < $required) {
                return false;
            }
        }

        return true;
    }
}
MODEL1

cat > app/Models/Badge.php << 'MODEL2'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    public const CRITERIA = [
        'gigs_completed' => 'Gigs Completed',
        'sales' => 'Marketplace Sales',
        'referrals' => 'Referrals',
        'q_coins' => 'Q Coins Earned',
        'streak' => 'Streak Days',
        'level' => 'Level Reached',
        'verified' => 'Verified Seller',
        'profile_complete' => 'Profile Complete (%)',
        'early_member' => 'Early Member (first N users)',
    ];

    protected $fillable = [
        'key', 'name', 'description', 'icon', 'color',
        'criteria_type', 'criteria_value', 'q_coin_reward', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'criteria_value' => 'integer',
        'q_coin_reward' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot('awarded_at');
    }

    public function criteriaLabel(): string
    {
        return self::CRITERIA[$this->criteria_type] ?? ucfirst(str_replace('_', ' ', $this->criteria_type));
    }
}
MODEL2

cat > app/Models/UserBadge.php << 'MODEL3'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $table = 'user_badges';

    protected $fillable = ['user_id', 'badge_id', 'awarded_at'];

    protected $casts = ['awarded_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
MODEL3
echo "models created"

# ------------------------------------------------------------------- SERVICE
cat > app/Services/LevelBadgeService.php << 'SERVICE'
<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Level;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    /**
     * Full refresh. Returns ['metrics'=>[], 'level'=>Level|null,
     * 'level_up'=>bool, 'new_badges'=>Badge[]].
     */
    public function refresh(User $user): array
    {
        return DB::transaction(function () use ($user) {
            /** @var User $locked */
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $metrics = $this->metrics($locked);
            [$level, $levelUp] = $this->syncLevel($locked, $metrics);

            // Level badges depend on the level we just assigned.
            $metrics['level_number'] = $level?->level_number ?? 0;
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

    /** Every metric the level/badge engine understands. */
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
            'verified' => (int) (bool) ($user->is_verified ?? $user->verified ?? 0),
            'profile_complete' => $this->profileCompletion($user),
            'user_number' => (int) $user->getKey(),
            'level_number' => (int) ($user->level?->level_number ?? 0),
        ];
    }

    /** Assign the highest level whose requirements are satisfied. */
    protected function syncLevel(User $user, array $metrics): array
    {
        $earned = Level::active()->orderBy('level_number')->get()
            ->filter(fn (Level $level) => $level->requirementsMet($metrics))
            ->last();

        if (! $earned || (int) $user->current_level_id === (int) $earned->id) {
            return [$earned ?? $user->level, false];
        }

        $previous = (int) ($user->level?->level_number ?? 0);
        $user->current_level_id = $earned->id;
        $user->level_achieved_at = now();
        $user->save();

        return [$earned, $earned->level_number > $previous];
    }

    /** Award every unowned badge whose criteria are met. */
    protected function syncBadges(User $user, array $metrics): array
    {
        $owned = DB::table('user_badges')->where('user_id', $user->getKey())
            ->pluck('badge_id')->all();

        $awarded = [];

        foreach (Badge::active()->orderBy('sort_order')->get() as $badge) {
            if (in_array($badge->id, $owned) || ! $this->badgeEarned($badge, $metrics)) {
                continue;
            }

            DB::table('user_badges')->insertOrIgnore([
                'user_id' => $user->getKey(),
                'badge_id' => $badge->id,
                'awarded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($badge->q_coin_reward > 0) {
                try {
                    $this->qCoins->earn(
                        $user,
                        $badge->q_coin_reward,
                        'badge_reward',
                        "Badge unlocked: {$badge->name}",
                        $badge->id
                    );
                } catch (\Throwable $e) {
                    Log::warning('Badge Q Coin reward failed', [
                        'user' => $user->getKey(), 'badge' => $badge->key, 'error' => $e->getMessage(),
                    ]);
                }
            }

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

        // Early member is inverted: lower user number is better.
        if ($badge->criteria_type === 'early_member') {
            return $current > 0 && $current <= max(1, $badge->criteria_value);
        }

        return $current >= max(1, $badge->criteria_value);
    }

    /** Progress toward a badge for UI: current / target / percent. */
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
        return Level::active()->orderBy('level_number')->get()
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
            'sales' => 'Marketplace sales',
            'referrals' => 'Referrals',
            'account_age_days' => 'Days on QLEVO',
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

    /**
     * Defensive counter: skips gracefully when the table/column
     * does not exist yet in this stage of the build.
     */
    protected function countRows(string $table, User $user, array $where = [], ?string $ownerColumn = null): int
    {
        if (! $this->hasTable($table)) {
            return 0;
        }

        $ownerColumn ??= 'user_id';

        if (! $this->hasColumn($table, $ownerColumn)) {
            return 0;
        }

        $query = DB::table($table)->where($ownerColumn, $user->getKey());

        foreach ($where as $column => $value) {
            if ($this->hasColumn($table, $column)) {
                $query->where($column, $value);
            }
        }

        return (int) $query->count();
    }

    protected function profileCompletion(User $user): int
    {
        $fields = ['name', 'email', 'phone', 'bio', 'avatar', 'username'];
        $present = 0;
        $checked = 0;

        foreach ($fields as $field) {
            if (! $this->hasColumn($user->getTable(), $field)) {
                continue;
            }
            $checked++;
            if (filled($user->{$field})) {
                $present++;
            }
        }

        return $checked === 0 ? 0 : (int) round($present / $checked * 100);
    }

    protected function hasTable(string $table): bool
    {
        return self::$schemaCache["t:$table"] ??= Schema::hasTable($table);
    }

    protected function hasColumn(string $table, string $column): bool
    {
        return self::$schemaCache["c:$table.$column"] ??= Schema::hasColumn($table, $column);
    }
}
SERVICE
echo "LevelBadgeService created"

# ---------------------------------------------------------------- CONTROLLER
cat > app/Http/Controllers/LevelBadgeController.php << 'CTRL1'
<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Level;
use App\Services\LevelBadgeService;
use Illuminate\Http\Request;

class LevelBadgeController extends Controller
{
    public function __construct(protected LevelBadgeService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $result = $this->service->refresh($user);
        $metrics = $result['metrics'];

        $owned = $user->badges()->pluck('badges.id')->all();

        $badges = Badge::active()->orderBy('sort_order')->get()
            ->map(fn (Badge $badge) => [
                'badge' => $badge,
                'owned' => in_array($badge->id, $owned),
                'progress' => $this->service->badgeProgress($badge, $metrics),
            ]);

        $nextLevel = $this->service->nextLevel($metrics);

        return view('rewards.achievements', [
            'metrics' => $metrics,
            'currentLevel' => $result['level'],
            'nextLevel' => $nextLevel,
            'levelProgress' => $this->service->levelProgress($nextLevel, $metrics),
            'levels' => Level::active()->orderBy('level_number')->get(),
            'badges' => $badges,
            'earnedCount' => count($owned),
            'newBadges' => $result['new_badges'],
        ]);
    }
}
CTRL1

mkdir -p app/Http/Controllers/Admin

cat > app/Http/Controllers/Admin/LevelController.php << 'CTRL2'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function index()
    {
        return view('admin.levels.index', [
            'levels' => Level::orderBy('level_number')->withCount('users')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.levels.create', ['level' => new Level(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        Level::create($this->validated($request));

        return redirect()->route('admin.levels.index')->with('success', 'Level created.');
    }

    public function edit(Level $level)
    {
        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, Level $level)
    {
        $level->update($this->validated($request, $level));

        return redirect()->route('admin.levels.index')->with('success', 'Level updated.');
    }

    public function destroy(Level $level)
    {
        $level->users()->update(['current_level_id' => null]);
        $level->delete();

        return redirect()->route('admin.levels.index')->with('success', 'Level deleted.');
    }

    protected function validated(Request $request, ?Level $level = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('levels')->ignore($level)],
            'name' => ['required', 'string', 'max:80'],
            'level_number' => ['required', 'integer', 'min:1', 'max:255', Rule::unique('levels')->ignore($level)],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
            'min_q_coins' => ['required', 'integer', 'min:0'],
            'min_gigs_completed' => ['required', 'integer', 'min:0'],
            'min_sales' => ['required', 'integer', 'min:0'],
            'min_referrals' => ['required', 'integer', 'min:0'],
            'min_account_age_days' => ['required', 'integer', 'min:0'],
        ]);

        $data['requires_verified'] = $request->boolean('requires_verified');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
CTRL2

cat > app/Http/Controllers/Admin/BadgeController.php << 'CTRL3'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BadgeController extends Controller
{
    public function index()
    {
        return view('admin.badges.index', [
            'badges' => Badge::orderBy('sort_order')->withCount('users')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.badges.create', ['badge' => new Badge(['is_active' => true, 'criteria_value' => 1])]);
    }

    public function store(Request $request)
    {
        Badge::create($this->validated($request));

        return redirect()->route('admin.badges.index')->with('success', 'Badge created.');
    }

    public function edit(Badge $badge)
    {
        return view('admin.badges.edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge)
    {
        $badge->update($this->validated($request, $badge));

        return redirect()->route('admin.badges.index')->with('success', 'Badge updated.');
    }

    public function destroy(Badge $badge)
    {
        $badge->users()->detach();
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('success', 'Badge deleted.');
    }

    protected function validated(Request $request, ?Badge $badge = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('badges')->ignore($badge)],
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
            'criteria_type' => ['required', Rule::in(array_keys(Badge::CRITERIA))],
            'criteria_value' => ['required', 'integer', 'min:1'],
            'q_coin_reward' => ['required', 'integer', 'min:0', 'max:100000'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
CTRL3
echo "controllers created"

# --------------------------------------------------------------------- SEEDER
cat > database/seeders/LevelBadgeSeeder.php << 'SEEDER'
<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['key' => 'starter', 'name' => 'Starter', 'level_number' => 1, 'icon' => 'sprout', 'color' => 'slate',
             'description' => 'Welcome to QLEVO. Your journey begins here.',
             'min_q_coins' => 0, 'min_gigs_completed' => 0, 'min_sales' => 0, 'min_referrals' => 0, 'min_account_age_days' => 0,
             'perks' => ['Daily Q Coin claim', 'Basic profile']],
            ['key' => 'active', 'name' => 'Active', 'level_number' => 2, 'icon' => 'activity', 'color' => 'blue',
             'description' => 'You are showing up and using the platform.',
             'min_q_coins' => 500, 'min_gigs_completed' => 1, 'min_sales' => 0, 'min_referrals' => 0, 'min_account_age_days' => 3,
             'perks' => ['Higher daily task cap', 'Profile badge display']],
            ['key' => 'contributor', 'name' => 'Contributor', 'level_number' => 3, 'icon' => 'users', 'color' => 'emerald',
             'description' => 'You are building real activity across QLEVO.',
             'min_q_coins' => 2500, 'min_gigs_completed' => 5, 'min_sales' => 2, 'min_referrals' => 1, 'min_account_age_days' => 14,
             'perks' => ['Priority in search', 'Extra task slots']],
            ['key' => 'pro', 'name' => 'Pro', 'level_number' => 4, 'icon' => 'briefcase', 'color' => 'violet',
             'description' => 'A trusted, high-output QLEVO professional.',
             'min_q_coins' => 10000, 'min_gigs_completed' => 20, 'min_sales' => 10, 'min_referrals' => 5, 'min_account_age_days' => 30,
             'requires_verified' => true, 'perks' => ['Lower fees', 'Pro badge', 'Featured listings']],
            ['key' => 'elite', 'name' => 'Elite', 'level_number' => 5, 'icon' => 'crown', 'color' => 'amber',
             'description' => 'Top tier. Unlocks USDT reward eligibility.',
             'min_q_coins' => 50000, 'min_gigs_completed' => 60, 'min_sales' => 30, 'min_referrals' => 15, 'min_account_age_days' => 90,
             'requires_verified' => true, 'perks' => ['USDT rewards', 'Lowest fees', 'Early feature access']],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(['key' => $level['key']], $level);
        }

        $badges = [
            ['key' => 'first_gig', 'name' => 'First Gig', 'icon' => 'rocket', 'color' => 'blue', 'criteria_type' => 'gigs_completed', 'criteria_value' => 1, 'q_coin_reward' => 100, 'sort_order' => 1, 'description' => 'Completed your very first gig on QLEVO.'],
            ['key' => 'trusted_worker', 'name' => 'Trusted Worker', 'icon' => 'shield-check', 'color' => 'emerald', 'criteria_type' => 'gigs_completed', 'criteria_value' => 10, 'q_coin_reward' => 300, 'sort_order' => 2, 'description' => 'Delivered 10 completed gigs.'],
            ['key' => 'top_seller', 'name' => 'Top Seller', 'icon' => 'trending-up', 'color' => 'orange', 'criteria_type' => 'sales', 'criteria_value' => 25, 'q_coin_reward' => 500, 'sort_order' => 3, 'description' => 'Closed 25 marketplace sales.'],
            ['key' => 'marketplace_pro', 'name' => 'Marketplace Pro', 'icon' => 'store', 'color' => 'violet', 'criteria_type' => 'sales', 'criteria_value' => 50, 'q_coin_reward' => 800, 'sort_order' => 4, 'description' => 'A serious marketplace operator: 50 sales.'],
            ['key' => 'streak_7', 'name' => 'Week Warrior', 'icon' => 'flame', 'color' => 'red', 'criteria_type' => 'streak', 'criteria_value' => 7, 'q_coin_reward' => 150, 'sort_order' => 5, 'description' => 'Claimed your daily reward 7 days in a row.'],
            ['key' => 'streak_30', 'name' => 'Month of Fire', 'icon' => 'flame-kindling', 'color' => 'red', 'criteria_type' => 'streak', 'criteria_value' => 30, 'q_coin_reward' => 600, 'sort_order' => 6, 'description' => '30 day daily claim streak.'],
            ['key' => 'streak_100', 'name' => 'Unbreakable', 'icon' => 'infinity', 'color' => 'rose', 'criteria_type' => 'streak', 'criteria_value' => 100, 'q_coin_reward' => 2000, 'sort_order' => 7, 'description' => '100 consecutive days. Legendary.'],
            ['key' => 'referral_champion', 'name' => 'Referral Champion', 'icon' => 'user-plus', 'color' => 'cyan', 'criteria_type' => 'referrals', 'criteria_value' => 10, 'q_coin_reward' => 750, 'sort_order' => 8, 'description' => 'Brought 10 people into the QLEVO ecosystem.'],
            ['key' => 'community_builder', 'name' => 'Community Builder', 'icon' => 'heart-handshake', 'color' => 'pink', 'criteria_type' => 'referrals', 'criteria_value' => 25, 'q_coin_reward' => 1500, 'sort_order' => 9, 'description' => 'Grew the community with 25 referrals.'],
            ['key' => 'q_coin_collector', 'name' => 'Q Coin Collector', 'icon' => 'coins', 'color' => 'amber', 'criteria_type' => 'q_coins', 'criteria_value' => 10000, 'q_coin_reward' => 500, 'sort_order' => 10, 'description' => 'Earned 10,000 Q Coins in total.'],
            ['key' => 'profile_complete', 'name' => 'All Set Up', 'icon' => 'badge-check', 'color' => 'teal', 'criteria_type' => 'profile_complete', 'criteria_value' => 100, 'q_coin_reward' => 100, 'sort_order' => 11, 'description' => 'Filled out your entire QLEVO profile.'],
            ['key' => 'early_member', 'name' => 'Early Member', 'icon' => 'sunrise', 'color' => 'indigo', 'criteria_type' => 'early_member', 'criteria_value' => 1000, 'q_coin_reward' => 250, 'sort_order' => 12, 'description' => 'One of the first 1,000 members of QLEVO.'],
            ['key' => 'elite_user', 'name' => 'Elite User', 'icon' => 'crown', 'color' => 'yellow', 'criteria_type' => 'level', 'criteria_value' => 5, 'q_coin_reward' => 5000, 'sort_order' => 13, 'description' => 'Reached Level 5: Elite.'],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['key' => $badge['key']], $badge + ['is_active' => true]);
        }
    }
}
SEEDER
echo "seeder created"
echo "=== PHASE 2 backend done ==="
