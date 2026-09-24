@extends('layouts.app-authenticated')

@section('content')
@if((auth()->user()->theme_style ?? 'glass') === 'plain')
@include('levels-badges.partials.levels-badges-minimal')
@else
<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Your Level & Badges</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Track your progress and achievements</p>
    </div>

    @if ($currentLevel)
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-bold mb-2">{{ $currentLevel->name }}</h2>
                <p class="text-blue-100">Level {{ $currentLevel->rank }} User</p>
            </div>
            <div class="text-6xl">⭐</div>
        </div>

        @if ($currentLevel->benefits)
        <div class="mt-6 pt-6 border-t border-blue-400">
            <h3 class="font-semibold mb-3">Benefits:</h3>
            <ul class="grid grid-cols-2 gap-2">
                @foreach ($currentLevel->benefits as $benefit)
                <li class="text-sm flex items-center"><span class="mr-2">✓</span> {{ $benefit }}</li>
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

    @if ($nextLevel && count($levelProgress))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Progress to {{ $nextLevel->name }}</h3>

        <div class="space-y-6">
            @foreach ($levelProgress as $row)
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $row['current'] }} / {{ $row['target'] }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="{{ $row['met'] ? 'bg-green-500' : 'bg-blue-500' }} h-2 rounded-full" style="width: {{ $row['percent'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <p class="text-gray-600 dark:text-gray-400 mb-2">Overall Progress</p>
                <div class="text-3xl font-bold text-blue-600">
                    {{ count($levelProgress) ? (int) round(collect($levelProgress)->avg('percent')) : 0 }}%
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
            Badges: {{ $badgesEarned }} / {{ $totalBadges }}
        </h3>

        @if ($badges->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($badges as $item)
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg {{ !$item['owned'] ? 'opacity-50' : '' }}">
                <div class="mb-2 flex justify-center {{ !$item['owned'] ? 'grayscale opacity-60' : '' }}"><i data-lucide="{{ $item['badge']->icon ?? 'award' }}" class="w-8 h-8 text-blue-600"></i></div>
                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $item['badge']->name }}</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $item['badge']->description }}</p>
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
@endif
@endsection
