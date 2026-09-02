echo "Creating admin view directories..."
mkdir -p resources/views/admin/levels
mkdir -p resources/views/admin/badges

echo "✅ Directories created"

echo ""
echo "Creating admin/levels/index.blade.php..."
cat > resources/views/admin/levels/index.blade.php << 'VIEW1'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Levels</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Configure user progression levels</p>
        </div>
        <a href="{{ route('admin.levels.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
            <i class="fas fa-plus"></i> New Level
        </a>
    </div>

    @if (session('success'))
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif

    <!-- Levels Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Min Q Coins</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Min Gigs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Min Referrals</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($levels as $level)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-800 font-bold">
                            {{ $level->rank }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                        {{ $level->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                        {{ $level->min_q_coins }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                        {{ $level->min_gigs_completed }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                        {{ $level->min_referrals }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($level->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-times-circle mr-1"></i> Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('admin.levels.edit', $level) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.levels.destroy', $level) }}" style="display: inline;" onsubmit="return confirm('Delete this level?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                        No levels found. <a href="{{ route('admin.levels.create') }}" class="text-blue-600">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
VIEW1

echo "✅ admin/levels/index.blade.php created"

echo ""
echo "Creating admin/levels/create.blade.php..."
cat > resources/views/admin/levels/create.blade.php << 'VIEW2'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Level</h1>
    </div>

    <form method="POST" action="{{ route('admin.levels.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @csrf

        <div class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tag mr-2"></i> Level Name
                </label>
                <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g., Premium" value="{{ old('name') }}">
                @error('name') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Rank -->
            <div>
                <label for="rank" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-sort-numeric-up mr-2"></i> Rank (Display Order)
                </label>
                <input type="number" name="rank" id="rank" required min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('rank') }}">
                @error('rank') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Min Q Coins -->
            <div>
                <label for="min_q_coins" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-coins mr-2"></i> Minimum Q Coins
                </label>
                <input type="number" name="min_q_coins" id="min_q_coins" required min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('min_q_coins', 0) }}">
                @error('min_q_coins') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Min Gigs Completed -->
            <div>
                <label for="min_gigs_completed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tasks mr-2"></i> Minimum Gigs Completed
                </label>
                <input type="number" name="min_gigs_completed" id="min_gigs_completed" required min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('min_gigs_completed', 0) }}">
                @error('min_gigs_completed') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Min Referrals -->
            <div>
                <label for="min_referrals" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-user-friends mr-2"></i> Minimum Referrals
                </label>
                <input type="number" name="min_referrals" id="min_referrals" required min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('min_referrals', 0) }}">
                @error('min_referrals') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Requires Verified -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="requires_verified" value="1" class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-shield-alt mr-2"></i> Requires Verified Seller
                    </span>
                </label>
            </div>

            <!-- Active Status -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-eye mr-2"></i> Active
                    </span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Create Level
                </button>
                <a href="{{ route('admin.levels.index') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
VIEW2

echo "✅ admin/levels/create.blade.php created"

echo ""
echo "Creating admin/levels/edit.blade.php..."
cat > resources/views/admin/levels/edit.blade.php << 'VIEW3'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Level: {{ $level->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.levels.update', $level) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tag mr-2"></i> Level Name
                </label>
                <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('name', $level->name) }}">
                @error('name') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Rank -->
            <div>
                <label for="rank" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-sort-numeric-up mr-2"></i> Rank
                </label>
                <input type="number" name="rank" id="rank" required min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('rank', $level->rank) }}">
                @error('rank') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Min Q Coins -->
            <div>
                <label for="min_q_coins" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-coins mr-2"></i> Minimum Q Coins
                </label>
                <input type="number" name="min_q_coins" id="min_q_coins" required min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('min_q_coins', $level->min_q_coins) }}">
                @error('min_q_coins') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Min Gigs Completed -->
            <div>
                <label for="min_gigs_completed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tasks mr-2"></i> Minimum Gigs Completed
                </label>
                <input type="number" name="min_gigs_completed" id="min_gigs_completed" required min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('min_gigs_completed', $level->min_gigs_completed) }}">
                @error('min_gigs_completed') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Min Referrals -->
            <div>
                <label for="min_referrals" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-user-friends mr-2"></i> Minimum Referrals
                </label>
                <input type="number" name="min_referrals" id="min_referrals" required min="0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('min_referrals', $level->min_referrals) }}">
                @error('min_referrals') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Requires Verified -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="requires_verified" value="1" {{ old('requires_verified', $level->requires_verified) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-shield-alt mr-2"></i> Requires Verified Seller
                    </span>
                </label>
            </div>

            <!-- Active Status -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $level->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-eye mr-2"></i> Active
                    </span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Update Level
                </button>
                <a href="{{ route('admin.levels.index') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
VIEW3

echo "✅ admin/levels/edit.blade.php created"

