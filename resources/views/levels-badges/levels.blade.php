@extends('layouts.app-authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">NAURVEX Levels</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Progress through levels to unlock benefits</p>
    </div>

    <div class="space-y-6">
        @foreach ($levels as $level)
        <div class="relative">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 {{ $currentLevelId === $level->id ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Level {{ $level->rank }} - {{ $level->name }}
                        @if ($currentLevelId === $level->id)
                        <span class="ml-3 inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">Current Level</span>
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
                                <span class="mr-3 text-green-600">✓</span> {{ $benefit }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
