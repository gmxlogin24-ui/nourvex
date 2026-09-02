echo "Creating admin/badges/index.blade.php..."
cat > resources/views/admin/badges/index.blade.php << 'BADGEINDEX'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Badges</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Configure achievement badges</p>
        </div>
        <a href="{{ route('admin.badges.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
            <i class="fas fa-plus"></i> New Badge
        </a>
    </div>

    @if (session('success'))
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif

    <!-- Badges Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Key</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Criteria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Requirement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($badges as $badge)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-2xl">
                        {{ $badge->icon }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                        {{ $badge->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $badge->key }}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {{ ucfirst(str_replace('_', ' ', $badge->criteria_type)) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ $badge->criteria_value }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($badge->is_active)
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
                        <a href="{{ route('admin.badges.edit', $badge) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" style="display: inline;" onsubmit="return confirm('Delete this badge?');">
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
                        No badges found. <a href="{{ route('admin.badges.create') }}" class="text-blue-600">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
BADGEINDEX

echo "✅ admin/badges/index.blade.php created"

echo ""
echo "Creating admin/badges/create.blade.php..."
cat > resources/views/admin/badges/create.blade.php << 'BADGECREATE'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Badge</h1>
    </div>

    <form method="POST" action="{{ route('admin.badges.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @csrf

        <div class="space-y-6">
            <!-- Key -->
            <div>
                <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-key mr-2"></i> Badge Key (slug)
                </label>
                <input type="text" name="key" id="key" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g., first_gig" value="{{ old('key') }}">
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Unique identifier (lowercase, underscores only)</p>
                @error('key') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tag mr-2"></i> Badge Name
                </label>
                <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g., First Gig" value="{{ old('name') }}">
                @error('name') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-align-left mr-2"></i> Description
                </label>
                <textarea name="description" id="description" required rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="What does this badge represent?">{{ old('description') }}</textarea>
                @error('description') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Icon -->
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-icons mr-2"></i> Icon (FontAwesome class or HTML)
                </label>
                <div class="flex gap-2">
                    <input type="text" name="icon" id="icon" required class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g., fas fa-star" value="{{ old('icon') }}">
                    <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-2xl" id="iconPreview">
                        <i class="fas fa-question"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Use FontAwesome classes (e.g., fas fa-star, fas fa-trophy)</p>
                @error('icon') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Criteria Type -->
            <div>
                <label for="criteria_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tasks mr-2"></i> Criteria Type
                </label>
                <select name="criteria_type" id="criteria_type" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a criteria type</option>
                    <option value="gigs_completed" {{ old('criteria_type') === 'gigs_completed' ? 'selected' : '' }}>Gigs Completed</option>
                    <option value="referrals" {{ old('criteria_type') === 'referrals' ? 'selected' : '' }}>Referrals</option>
                    <option value="q_coins" {{ old('criteria_type') === 'q_coins' ? 'selected' : '' }}>Q Coins Earned</option>
                    <option value="streak" {{ old('criteria_type') === 'streak' ? 'selected' : '' }}>Streak Days</option>
                    <option value="verified" {{ old('criteria_type') === 'verified' ? 'selected' : '' }}>Verified Seller</option>
                    <option value="profile_complete" {{ old('criteria_type') === 'profile_complete' ? 'selected' : '' }}>Profile Complete</option>
                    <option value="early_member" {{ old('criteria_type') === 'early_member' ? 'selected' : '' }}>Early Member</option>
                </select>
                @error('criteria_type') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Criteria Value -->
            <div>
                <label for="criteria_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-sort-numeric-up mr-2"></i> Criteria Value (Requirement)
                </label>
                <input type="number" name="criteria_value" id="criteria_value" required min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g., 5" value="{{ old('criteria_value', 1) }}">
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">User must meet or exceed this value to earn badge</p>
                @error('criteria_value') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Active Status -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-eye mr-2"></i> Active (Users can earn this)
                    </span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Create Badge
                </button>
                <a href="{{ route('admin.badges.index') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('icon').addEventListener('input', function() {
    const preview = document.getElementById('iconPreview');
    preview.innerHTML = '<i class="' + this.value + '"></i>';
});

// Initial preview
document.getElementById('iconPreview').innerHTML = '<i class="' + (document.getElementById('icon').value || 'fas fa-question') + '"></i>';
</script>
@endsection
BADGECREATE

echo "✅ admin/badges/create.blade.php created"

echo ""
echo "Creating admin/badges/edit.blade.php..."
cat > resources/views/admin/badges/edit.blade.php << 'BADGEEDIT'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Badge: {{ $badge->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.badges.update', $badge) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Key -->
            <div>
                <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-key mr-2"></i> Badge Key
                </label>
                <input type="text" name="key" id="key" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('key', $badge->key) }}">
                @error('key') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tag mr-2"></i> Badge Name
                </label>
                <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('name', $badge->name) }}">
                @error('name') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-align-left mr-2"></i> Description
                </label>
                <textarea name="description" id="description" required rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('description', $badge->description) }}</textarea>
                @error('description') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Icon -->
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-icons mr-2"></i> Icon
                </label>
                <div class="flex gap-2">
                    <input type="text" name="icon" id="icon" required class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('icon', $badge->icon) }}">
                    <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-2xl" id="iconPreview">
                        <i class="{{ $badge->icon }}"></i>
                    </div>
                </div>
                @error('icon') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Criteria Type -->
            <div>
                <label for="criteria_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tasks mr-2"></i> Criteria Type
                </label>
                <select name="criteria_type" id="criteria_type" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                    <option value="gigs_completed" {{ old('criteria_type', $badge->criteria_type) === 'gigs_completed' ? 'selected' : '' }}>Gigs Completed</option>
                    <option value="referrals" {{ old('criteria_type', $badge->criteria_type) === 'referrals' ? 'selected' : '' }}>Referrals</option>
                    <option value="q_coins" {{ old('criteria_type', $badge->criteria_type) === 'q_coins' ? 'selected' : '' }}>Q Coins Earned</option>
                    <option value="streak" {{ old('criteria_type', $badge->criteria_type) === 'streak' ? 'selected' : '' }}>Streak Days</option>
                    <option value="verified" {{ old('criteria_type', $badge->criteria_type) === 'verified' ? 'selected' : '' }}>Verified Seller</option>
                    <option value="profile_complete" {{ old('criteria_type', $badge->criteria_type) === 'profile_complete' ? 'selected' : '' }}>Profile Complete</option>
                    <option value="early_member" {{ old('criteria_type', $badge->criteria_type) === 'early_member' ? 'selected' : '' }}>Early Member</option>
                </select>
                @error('criteria_type') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Criteria Value -->
            <div>
                <label for="criteria_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-sort-numeric-up mr-2"></i> Criteria Value
                </label>
                <input type="number" name="criteria_value" id="criteria_value" required min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500" value="{{ old('criteria_value', $badge->criteria_value) }}">
                @error('criteria_value') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Active Status -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $badge->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-3 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-eye mr-2"></i> Active
                    </span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Update Badge
                </button>
                <a href="{{ route('admin.badges.index') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('icon').addEventListener('input', function() {
    const preview = document.getElementById('iconPreview');
    preview.innerHTML = '<i class="' + this.value + '"></i>';
});
</script>
@endsection
BADGEEDIT

echo "✅ admin/badges/edit.blade.php created"

echo ""
echo "================================"
echo "✅ ALL BADGE ADMIN VIEWS CREATED!"
echo "================================"

