@csrf
@if ($level->exists) @method('PUT') @endif

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="tag" class="w-4 h-4"></i> Name
            </label>
            <input type="text" name="name" id="name" required value="{{ old('name', $level->name) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="rank" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="hash" class="w-4 h-4"></i> Rank (higher = further along)
            </label>
            <input type="number" name="rank" id="rank" required min="1" value="{{ old('rank', $level->rank) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error('rank') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ([
            ['min_q_coins', 'Min Q Coins Earned', 'coins'],
            ['min_gigs_completed', 'Min Gigs Completed', 'briefcase'],
            ['min_referrals', 'Min Referrals', 'user-plus'],
        ] as [$field, $label, $icon])
        <div>
            <label for="{{ $field }}" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i> {{ $label }}
            </label>
            <input type="number" name="{{ $field }}" id="{{ $field }}" required min="0" value="{{ old($field, $level->{$field} ?? 0) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error($field) <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        @endforeach
    </div>

    <div>
        <label for="benefits_text" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i data-lucide="list" class="w-4 h-4"></i> Benefits (one per line)
        </label>
        <textarea name="benefits_text" id="benefits_text" rows="4" placeholder="Priority support&#10;Lower fees&#10;Custom badge"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('benefits_text', $level->benefits ? implode("\n", $level->benefits) : '') }}</textarea>
        @error('benefits') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <label class="flex items-center">
        <input type="checkbox" name="requires_verified" value="1" @checked(old('requires_verified', $level->requires_verified)) class="w-4 h-4 text-blue-600 rounded">
        <span class="ml-3 flex items-center gap-2 text-gray-700 dark:text-gray-300">
            <i data-lucide="shield-check" class="w-4 h-4"></i> Requires Verified Seller
        </span>
    </label>

    <label class="flex items-center">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $level->is_active ?? true)) class="w-4 h-4 text-blue-600 rounded">
        <span class="ml-3 flex items-center gap-2 text-gray-700 dark:text-gray-300">
            <i data-lucide="eye" class="w-4 h-4"></i> Active
        </span>
    </label>

    <div class="flex gap-4 pt-4">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center justify-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> {{ $level->exists ? 'Update Level' : 'Create Level' }}
        </button>
        <a href="{{ route('admin.levels.index') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center justify-center gap-2">
            <i data-lucide="x" class="w-4 h-4"></i> Cancel
        </a>
    </div>
</div>
