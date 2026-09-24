@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Levels &amp; Achievements</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Your progress across the QELVO ecosystem.</p>
    </div>

    @foreach ($newBadges as $new)
    <div class="mb-4 flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 rounded-lg">
        <i data-lucide="party-popper" class="w-5 h-5 text-amber-600"></i>
        <span class="text-amber-800 dark:text-amber-200">
            New badge unlocked: <strong>{{ $new->name }}</strong>
            @if ($new->q_coin_reward) &mdash; +{{ number_format($new->q_coin_reward) }} Q @endif
        </span>
    </div>
    @endforeach

    <!-- Current level -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <i data-lucide="{{ $currentLevel->icon ?? 'sprout' }}" class="w-7 h-7 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Current level</p>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Level {{ $currentLevel->level_number ?? 0 }} &middot; {{ $currentLevel->name ?? 'Unranked' }}
                    </h2>
                    @if ($currentLevel?->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $currentLevel->description }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600 dark:text-gray-400">Badges earned</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $earnedCount }} / {{ $badges->count() }}</p>
            </div>
        </div>

        @if ($currentLevel && $currentLevel->perks)
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($currentLevel->perks as $perk)
            <span class="inline-flex items-center gap-1 px-3 py-1 text-sm rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                <i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $perk }}
            </span>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Next level progress -->
    @if ($nextLevel)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="{{ $nextLevel->icon }}" class="w-5 h-5 text-violet-600"></i>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Next: Level {{ $nextLevel->level_number }} &middot; {{ $nextLevel->name }}
            </h3>
        </div>

        <div class="space-y-4">
            @foreach ($levelProgress as $row)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                        <i data-lucide="{{ $row['met'] ? 'circle-check' : 'circle-dashed' }}" class="w-4 h-4 {{ $row['met'] ? 'text-green-600' : 'text-gray-400' }}"></i>
                        {{ $row['label'] }}
                    </span>
                    <span class="text-gray-600 dark:text-gray-400">{{ number_format($row['current']) }} / {{ number_format($row['target']) }}</span>
                </div>
                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $row['met'] ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $row['percent'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-300 rounded-lg">
        <i data-lucide="crown" class="w-5 h-5 text-emerald-600"></i>
        <span class="text-emerald-800 dark:text-emerald-200">Maximum level reached. You are at the top of QELVO.</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach ([
            ['coins', 'Q Coins earned', number_format($metrics['q_coins_earned'])],
            ['briefcase', 'Gigs completed', number_format($metrics['gigs_completed'])],
            ['flame', 'Streak days', number_format($metrics['streak_days'])],
            ['user-plus', 'Referrals', number_format($metrics['referrals'])],
        ] as [$icon, $label, $value])
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <i data-lucide="{{ $icon }}" class="w-5 h-5 text-gray-500 mb-2"></i>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    <!-- Badges -->
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Badges</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
        @foreach ($badges as $item)
        @php $badge = $item['badge']; $p = $item['progress']; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 {{ $item['owned'] ? '' : 'opacity-70' }}">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 {{ $item['owned'] ? 'bg-amber-100 dark:bg-amber-900/40' : 'bg-gray-100 dark:bg-gray-700' }}">
                    <i data-lucide="{{ $item['owned'] ? $badge->icon : 'lock' }}" class="w-5 h-5 {{ $item['owned'] ? 'text-amber-600' : 'text-gray-400' }}"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $badge->name }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $badge->description }}</p>
                </div>
            </div>

            <div class="mt-4">
                @if ($item['owned'])
                <span class="inline-flex items-center gap-1 text-sm font-medium text-green-700 dark:text-green-400">
                    <i data-lucide="circle-check" class="w-4 h-4"></i> Unlocked
                </span>
                @else
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                    <span>{{ $badge->criteriaLabel() }}</span>
                    <span>{{ number_format($p['current']) }} / {{ number_format($p['target']) }}</span>
                </div>
                <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full" style="width: {{ $p['percent'] }}%"></div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Level roadmap -->
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Level roadmap</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-700">
        @foreach ($levels as $level)
        @php $reached = ($currentLevel?->level_number ?? 0) >= $level->level_number; @endphp
        <div class="flex items-center gap-4 p-4">
            <i data-lucide="{{ $reached ? 'circle-check' : $level->icon }}" class="w-5 h-5 {{ $reached ? 'text-green-600' : 'text-gray-400' }}"></i>
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">Level {{ $level->level_number }} &middot; {{ $level->name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ number_format($level->min_q_coins) }} Q &middot; {{ $level->min_gigs_completed }} gigs &middot;
                    {{ $level->min_sales }} sales &middot; {{ $level->min_referrals }} referrals
                    @if ($level->requires_verified) &middot; verified @endif
                </p>
            </div>
            @if ($reached)
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800">Reached</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
