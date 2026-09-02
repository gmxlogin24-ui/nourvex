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
