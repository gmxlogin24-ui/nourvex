#!/bin/bash

echo "Creating UserBadge Model..."
cat > app/Models/UserBadge.php << 'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $table = 'user_badges';
    protected $fillable = ['user_id', 'badge_id', 'earned_at'];
    protected $casts = ['earned_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
MODEL

echo "✅ UserBadge Model created"

echo ""
echo "Updating Badge Model..."
cat > app/Models/Badge.php << 'BADGE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'criteria_type',
        'criteria_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'criteria_value' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
BADGE

echo "✅ Badge Model updated"

echo ""
echo "Updating Level Model..."
cat > app/Models/Level.php << 'LEVEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'name',
        'rank',
        'min_q_coins',
        'min_gigs_completed',
        'min_referrals',
        'requires_verified',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'requires_verified' => 'boolean',
        'is_active' => 'boolean',
        'min_q_coins' => 'integer',
        'min_gigs_completed' => 'integer',
        'min_referrals' => 'integer',
        'rank' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
LEVEL

echo "✅ Level Model updated"

echo ""
echo "Updating LevelBadgeService..."
cat > app/Services/LevelBadgeService.php << 'SERVICE'
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Level;
use App\Models\Badge;
use Illuminate\Support\Facades\DB;

class LevelBadgeService
{
    /**
     * Refresh user's level and badges based on current activity.
     * This is the main method called after significant user actions.
     */
    public function refresh(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $newBadges = $this->checkBadges($user);
            $leveledUp = $this->checkLevel($user);

            return [
                'new_badges' => $newBadges,
                'leveled_up' => $leveledUp,
            ];
        });
    }

    /**
     * Get count of user's referrals
     */
    protected function referralsCount(User $user): int
    {
        return User::where('referred_by', $user->id)
            ->where('tier', '!=', 'free')
            ->count();
    }

    /**
     * Check if user qualifies for a new level and update if needed
     */
    protected function checkLevel(User $user): ?Level
    {
        $eligible = Level::where('is_active', true)
            ->where('min_q_coins', '<=', $user->q_coin_balance ?? 0)
            ->where('min_gigs_completed', '<=', $user->completedGigsCount())
            ->where('min_referrals', '<=', $this->referralsCount($user))
            ->when(!$user->is_verified_seller, fn($q) => $q->where('requires_verified', false))
            ->orderByDesc('rank')
            ->first();

        if ($eligible && $eligible->id !== $user->level_id) {
            $user->update(['level_id' => $eligible->id]);
            return $eligible;
        }

        return null;
    }

    /**
     * Check what new badges user has earned
     */
    protected function checkBadges(User $user): array
    {
        $earned = [];
        $alreadyHas = $user->badges()->pluck('badge_id')->toArray();

        $badges = Badge::where('is_active', true)
            ->whereNotIn('id', $alreadyHas)
            ->get();

        foreach ($badges as $badge) {
            if ($this->userMeetsCriteria($user, $badge)) {
                $user->badges()->attach($badge->id, ['earned_at' => now()]);
                $earned[] = $badge;
            }
        }

        return $earned;
    }

    /**
     * Check if user meets badge criteria
     */
    protected function userMeetsCriteria(User $user, Badge $badge): bool
    {
        $value = match ($badge->criteria_type) {
            'gigs_completed' => $user->completedGigsCount(),
            'referrals' => $this->referralsCount($user),
            'q_coins' => $user->q_coin_balance ?? 0,
            'streak' => $user->q_coin_streak_days ?? 0,
            'profile_complete' => $this->isProfileComplete($user) ? 1 : 0,
            'verified' => $user->is_verified_seller ? 1 : 0,
            default => 0,
        };

        return $value >= $badge->criteria_value;
    }

    /**
     * Check if user profile is complete
     */
    protected function isProfileComplete(User $user): bool
    {
        return !empty($user->name)
            && !empty($user->email)
            && !empty($user->avatar_url)
            && ($user->is_verified_seller || !empty($user->bio));
    }

    /**
     * Get user's current level with details
     */
    public function getUserLevel(User $user): ?array
    {
        if (!$user->level_id) {
            return null;
        }

        $level = Level::find($user->level_id);
        if (!$level) {
            return null;
        }

        return [
            'id' => $level->id,
            'name' => $level->name,
            'rank' => $level->rank,
            'benefits' => $level->benefits ?? [],
            'progress' => $this->getLevelProgress($user, $level),
        ];
    }

    /**
     * Get progress toward next level
     */
    public function getLevelProgress(User $user, ?Level $currentLevel = null): array
    {
        $current = $currentLevel ?? Level::find($user->level_id);
        $nextLevel = Level::where('is_active', true)
            ->where('rank', '>', $current->rank ?? 0)
            ->orderBy('rank')
            ->first();

        if (!$nextLevel) {
            return ['current_level' => $current, 'next_level' => null, 'progress' => 100];
        }

        $qCoinProgress = (int) (($user->q_coin_balance ?? 0) / $nextLevel->min_q_coins * 100);
        $gigsProgress = (int) ($user->completedGigsCount() / $nextLevel->min_gigs_completed * 100);
        $referralsProgress = (int) ($this->referralsCount($user) / $nextLevel->min_referrals * 100);

        $avgProgress = (int) (($qCoinProgress + $gigsProgress + $referralsProgress) / 3);

        return [
            'current_level' => $current,
            'next_level' => $nextLevel,
            'q_coins' => ['current' => $user->q_coin_balance ?? 0, 'needed' => $nextLevel->min_q_coins, 'progress' => min($qCoinProgress, 100)],
            'gigs' => ['current' => $user->completedGigsCount(), 'needed' => $nextLevel->min_gigs_completed, 'progress' => min($gigsProgress, 100)],
            'referrals' => ['current' => $this->referralsCount($user), 'needed' => $nextLevel->min_referrals, 'progress' => min($referralsProgress, 100)],
            'overall_progress' => min($avgProgress, 99),
        ];
    }
}
SERVICE

echo "✅ LevelBadgeService updated"

echo ""
echo "Creating LevelBadgeController..."
cat > app/Http/Controllers/LevelBadgeController.php << 'CONTROLLER'
<?php

namespace App\Http\Controllers;

use App\Services\LevelBadgeService;
use Illuminate\Http\Request;

class LevelBadgeController extends Controller
{
    public function __construct(private LevelBadgeService $service)
    {
    }

    /**
     * Show user's level and badges
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        $currentLevel = $this->service->getUserLevel($user);
        $levelProgress = $currentLevel ? $this->service->getLevelProgress($user) : null;
        $badges = $user->badges()->get();
        $totalBadges = \App\Models\Badge::where('is_active', true)->count();

        return view('levels-badges.show', [
            'pageKey' => 'levels-badges',
            'currentLevel' => $currentLevel,
            'levelProgress' => $levelProgress,
            'badges' => $badges,
            'badgesEarned' => $badges->count(),
            'totalBadges' => $totalBadges,
        ]);
    }

    /**
     * Show all available badges
     */
    public function badges(Request $request)
    {
        $userBadgeIds = auth()->user()->badges()->pluck('badge_id')->toArray();
        $allBadges = \App\Models\Badge::where('is_active', true)
            ->get()
            ->map(function ($badge) use ($userBadgeIds) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'criteria_type' => $badge->criteria_type,
                    'criteria_value' => $badge->criteria_value,
                    'earned' => in_array($badge->id, $userBadgeIds),
                ];
            });

        return view('levels-badges.badges', [
            'pageKey' => 'badges',
            'badges' => $allBadges,
            'totalBadges' => $allBadges->count(),
            'earnedBadges' => collect($allBadges)->filter(fn($b) => $b['earned'])->count(),
        ]);
    }

    /**
     * Show levels
     */
    public function levels(Request $request)
    {
        $user = auth()->user();
        $levels = \App\Models\Level::where('is_active', true)
            ->orderBy('rank')
            ->get();

        return view('levels-badges.levels', [
            'pageKey' => 'levels',
            'levels' => $levels,
            'currentLevelId' => $user->level_id,
        ]);
    }
}
CONTROLLER

echo "✅ LevelBadgeController created"

echo ""
echo "Adding routes to web.php..."
grep -q "LevelBadgeController" routes/web.php || cat >> routes/web.php << 'ROUTES'

// Level & Badge routes
Route::middleware(['auth', 'signup.paid'])->group(function () {
    Route::prefix('levels-badges')->name('levels-badges.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LevelBadgeController::class, 'show'])->name('show');
        Route::get('/badges', [\App\Http\Controllers\LevelBadgeController::class, 'badges'])->name('badges');
        Route::get('/levels', [\App\Http\Controllers\LevelBadgeController::class, 'levels'])->name('levels');
    });
});
ROUTES

echo "✅ Routes added"

echo ""
echo "Creating view directories..."
mkdir -p resources/views/levels-badges

echo "✅ View directories created"

echo ""
echo "Creating levels-badges/show.blade.php..."
cat > resources/views/levels-badges/show.blade.php << 'VIEW1'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Your Level & Badges</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Track your progress and achievements</p>
    </div>

    <!-- Current Level Section -->
    @if ($currentLevel)
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-bold mb-2">{{ $currentLevel['name'] }}</h2>
                <p class="text-blue-100">Level {{ $currentLevel['rank'] }} User</p>
            </div>
            <div class="text-6xl">⭐</div>
        </div>

        @if ($currentLevel['benefits'])
        <div class="mt-6 pt-6 border-t border-blue-400">
            <h3 class="font-semibold mb-3">Benefits:</h3>
            <ul class="grid grid-cols-2 gap-2">
                @foreach ($currentLevel['benefits'] as $benefit)
                <li class="text-sm flex items-center">
                    <span class="mr-2">✓</span> {{ $benefit }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @else
    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-8 mb-8">
        <p class="text-gray-600 dark:text-gray-400">Complete activities to unlock your first level!</p>
    </div>
    @endif

    <!-- Progress to Next Level -->
    @if ($levelProgress && $levelProgress['next_level'])
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Progress to Next Level</h3>

        <div class="space-y-6">
            <!-- Q Coins Progress -->
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Q Coins</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $levelProgress['q_coins']['current'] }} / {{ $levelProgress['q_coins']['needed'] }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min($levelProgress['q_coins']['progress'], 100) }}%"></div>
                </div>
            </div>

            <!-- Gigs Progress -->
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Gigs Completed</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $levelProgress['gigs']['current'] }} / {{ $levelProgress['gigs']['needed'] }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($levelProgress['gigs']['progress'], 100) }}%"></div>
                </div>
            </div>

            <!-- Referrals Progress -->
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Referrals</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $levelProgress['referrals']['current'] }} / {{ $levelProgress['referrals']['needed'] }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ min($levelProgress['referrals']['progress'], 100) }}%"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <p class="text-gray-600 dark:text-gray-400 mb-2">Overall Progress</p>
                <div class="text-3xl font-bold text-blue-600">{{ $levelProgress['overall_progress'] }}%</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Badges Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
            Badges: {{ $badgesEarned }} / {{ $totalBadges }}
        </h3>

        @if ($badges->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($badges as $badge)
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-4xl mb-2">{{ $badge->icon ?? '🏆' }}</div>
                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $badge->name }}</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
            </div>
            @endforeach
        </div>
        <div class="mt-6">
            <a href="{{ route('levels-badges.badges') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                View all badges →
            </a>
        </div>
        @else
        <p class="text-gray-600 dark:text-gray-400">Complete activities to earn badges!</p>
        @endif
    </div>
</div>
@endsection
VIEW1

echo "✅ show.blade.php created"

echo ""
echo "Creating levels-badges/badges.blade.php..."
cat > resources/views/levels-badges/badges.blade.php << 'VIEW2'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">All Badges</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">You have earned {{ $earnedBadges }} of {{ $totalBadges }} badges</p>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Badge Progress</span>
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $earnedBadges }} / {{ $totalBadges }}</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full" style="width: {{ ($earnedBadges / $totalBadges * 100) }}%"></div>
        </div>
    </div>

    <!-- Badges Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($badges as $badge)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center {{ !$badge['earned'] ? 'opacity-50' : '' }}">
            <div class="text-5xl mb-3 filter {{ !$badge['earned'] ? 'grayscale' : '' }}">
                {{ $badge['icon'] ?? '🏆' }}
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white">{{ $badge['name'] }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $badge['description'] }}</p>

            @if ($badge['earned'])
            <div class="mt-3 inline-block bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                ✓ Earned
            </div>
            @else
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                {{ ucfirst(str_replace('_', ' ', $badge['criteria_type'])) }}: {{ $badge['criteria_value'] }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
VIEW2

echo "✅ badges.blade.php created"

echo ""
echo "Creating levels-badges/levels.blade.php..."
cat > resources/views/levels-badges/levels.blade.php << 'VIEW3'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">QLEVO Levels</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Progress through levels to unlock benefits</p>
    </div>

    <!-- Levels Timeline -->
    <div class="space-y-6">
        @foreach ($levels as $level)
        <div class="relative">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 {{ $currentLevelId === $level->id ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Level {{ $level->rank }} - {{ $level->name }}
                            @if ($currentLevelId === $level->id)
                            <span class="ml-3 inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                Current Level
                            </span>
                            @endif
                        </h3>

                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Min Q Coins</p>
                                <p class="text-lg font-bold text-blue-600">{{ $level->min_q_coins }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Gigs Completed</p>
                                <p class="text-lg font-bold text-green-600">{{ $level->min_gigs_completed }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Referrals</p>
                                <p class="text-lg font-bold text-purple-600">{{ $level->min_referrals }}</p>
                            </div>
                        </div>

                        @if ($level->benefits && count($level->benefits) > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Benefits:</h4>
                            <ul class="space-y-2">
                                @foreach ($level->benefits as $benefit)
                                <li class="flex items-center text-gray-700 dark:text-gray-300">
                                    <span class="mr-3 text-green-600">✓</span>
                                    {{ $benefit }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
VIEW3

echo "✅ levels.blade.php created"

echo ""
echo "================================"
echo "✅ ALL LEVEL/BADGE SYSTEM CREATED!"
echo "================================"

