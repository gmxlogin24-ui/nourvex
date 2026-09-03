@csrf
@if ($badge->exists) @method('PUT') @endif

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="key" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="key" class="w-4 h-4"></i> Badge Key (slug)
            </label>
            <input type="text" name="key" id="key" required value="{{ old('key', $badge->key) }}" placeholder="first_gig"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Lowercase and underscores only.</p>
            @error('key') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="name" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="tag" class="w-4 h-4"></i> Badge Name
            </label>
            <input type="text" name="name" id="name" required value="{{ old('name', $badge->name) }}" placeholder="First Gig"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label for="description" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i data-lucide="align-left" class="w-4 h-4"></i> Description
        </label>
        <textarea name="description" id="description" required rows="3" placeholder="What does this badge represent?"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('description', $badge->description) }}</textarea>
        @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="icon" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i data-lucide="sparkles" class="w-4 h-4"></i> Lucide Icon Name
        </label>
        <div class="flex gap-2">
            <input type="text" name="icon" id="icon" value="{{ old('icon', $badge->icon ?? 'award') }}" placeholder="award"
                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            <div class="px-4 flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg" id="iconPreview">
                <i data-lucide="{{ old('icon', $badge->icon ?? 'award') }}" class="w-6 h-6 text-amber-600"></i>
            </div>
        </div>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Name only, e.g. award, trophy, flame. See lucide.dev/icons</p>
        @error('icon') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="criteria_type" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="list-checks" class="w-4 h-4"></i> Criteria Type
            </label>
            <select name="criteria_type" id="criteria_type" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="">Select a criteria type</option>
                @foreach (\App\Models\Badge::CRITERIA as $value => $label)
                <option value="{{ $value }}" @selected(old('criteria_type', $badge->criteria_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('criteria_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="criteria_value" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="hash" class="w-4 h-4"></i> Requirement
            </label>
            <input type="number" name="criteria_value" id="criteria_value" required min="1" value="{{ old('criteria_value', $badge->criteria_value ?? 1) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Met or exceeded to unlock.</p>
            @error('criteria_value') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <label class="flex items-center">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $badge->is_active ?? true)) class="w-4 h-4 text-blue-600 rounded">
        <span class="ml-3 flex items-center gap-2 text-gray-700 dark:text-gray-300">
            <i data-lucide="eye" class="w-4 h-4"></i> Active (users can earn this)
        </span>
    </label>

    <div class="flex gap-4 pt-4">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center justify-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> {{ $badge->exists ? 'Update Badge' : 'Create Badge' }}
        </button>
        <a href="{{ route('admin.badges.index') }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center justify-center gap-2">
            <i data-lucide="x" class="w-4 h-4"></i> Cancel
        </a>
    </div>
</div>

<script>
document.getElementById('icon').addEventListener('input', function () {
    document.getElementById('iconPreview').innerHTML =
        '<i data-lucide="' + (this.value || 'award') + '" class="w-6 h-6 text-amber-600"></i>';
    if (window.lucide) window.lucide.createIcons();
});
</script>
