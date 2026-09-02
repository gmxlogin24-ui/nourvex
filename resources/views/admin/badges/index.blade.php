@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Badges</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Configure achievement badges</p>
        </div>
        <a href="{{ route('admin.badges.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> New Badge
        </a>
    </div>

    @if (session('success'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        <i data-lucide="circle-check" class="w-4 h-4"></i> {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr class="text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    <th class="px-6 py-3">Icon</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Key</th>
                    <th class="px-6 py-3">Criteria</th>
                    <th class="px-6 py-3">Target</th>
                    <th class="px-6 py-3">Reward</th>
                    <th class="px-6 py-3">Holders</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($badges as $badge)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4"><i data-lucide="{{ $badge->icon }}" class="w-5 h-5 text-amber-600"></i></td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $badge->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-700 dark:text-gray-300">{{ $badge->key }}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $badge->criteriaLabel() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ number_format($badge->criteria_value) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ number_format($badge->q_coin_reward) }} Q</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $badge->users_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($badge->is_active)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i data-lucide="circle-check" class="w-3.5 h-3.5"></i> Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i data-lucide="circle-x" class="w-3.5 h-3.5"></i> Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.badges.edit', $badge) }}" class="text-blue-600 hover:text-blue-800">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" onsubmit="return confirm('Delete this badge? Holders will lose it.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-600 dark:text-gray-400">
                        No badges yet. <a href="{{ route('admin.badges.create') }}" class="text-blue-600">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
