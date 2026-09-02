@csrf
@if ($level->exists) @method('PUT') @endif

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="key" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="key" class="w-4 h-4"></i> Key
            </label>
            <input type="text" name="key" id="key" required value="{{ old('key', $level->key) }}" placeholder="contributor"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error('key') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="name" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="tag" class="w-4 h-4"></i> Name
            </label>
            <input type="text" name="name" id="name" required value="{{ old('name', $level->name) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="level_number" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="hash" class="w-4 h-4"></i> Level Number
            </label>
            <input type="number" name="level_number" id="level_number" required min="1" value="{{ old('level_number', $level->level_number) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error('level_number') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label for="description" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i data-lucide="align-left" class="w-4 h-4"></i> Description
        </label>
        <textarea name="description" id="description" rows="2"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('description', $level->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="icon" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Lucide Icon Name
            </label>
            <div class="flex gap-2">
                <input type="text" name="icon" id="icon" required value="{{ old('icon', $level->icon ?? 'star') }}"
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <div class="px-4 flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg" id="iconPreview">
                    <i data-lucide="{{ old('icon', $level->icon ?? 'star') }}" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            @error('icon') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="color" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="palette" class="w-4 h-4"></i> Accent Colour
            </label>
            <select name="color" id="color" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                @foreach (['slate','blue','emerald','violet','amber','rose','cyan','teal','indigo'] as $c)
                <option value="{{ $c }}" @selected(old('color', $level->color ?? 'blue') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ([
            ['min_q_coins', 'Min Q Coins Earned', 'coins'],
            ['min_gigs_completed', 'Min Gigs Completed', 'briefcase'],
            ['min_sales', 'Min Marketplace Sales', 'store'],
            ['min_referrals', 'Min Referrals', 'user-plus'],
            ['min_account_age_days', 'Min Account Age (days)', 'calendar-days'],
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

<script>
document.getElementById('icon').addEventListener('input', function () {
    document.getElementById('iconPreview').innerHTML =
        '<i data-lucide="' + (this.value || 'star') + '" class="w-6 h-6 text-blue-600"></i>';
    if (window.lucide) window.lucide.createIcons();
});
</script>
