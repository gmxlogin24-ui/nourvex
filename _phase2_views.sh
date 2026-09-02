set -e
mkdir -p resources/views/rewards resources/views/admin/levels resources/views/admin/badges

# ------------------------------------------------- USER ACHIEVEMENTS PAGE
cat > resources/views/rewards/achievements.blade.php << 'ACH'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Levels &amp; Achievements</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Your progress across the QLEVO ecosystem.</p>
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
        <span class="text-emerald-800 dark:text-emerald-200">Maximum level reached. You are at the top of QLEVO.</span>
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
ACH
echo "rewards/achievements.blade.php created"

# ------------------------------------------------------- ADMIN: BADGES
cat > resources/views/admin/badges/index.blade.php << 'BI'
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
BI

cat > resources/views/admin/badges/_form.blade.php << 'BF'
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="icon" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Lucide Icon Name
            </label>
            <div class="flex gap-2">
                <input type="text" name="icon" id="icon" required value="{{ old('icon', $badge->icon ?? 'award') }}" placeholder="award"
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <div class="px-4 flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg" id="iconPreview">
                    <i data-lucide="{{ old('icon', $badge->icon ?? 'award') }}" class="w-6 h-6 text-amber-600"></i>
                </div>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Name only, e.g. award, trophy, flame. See lucide.dev/icons</p>
            @error('icon') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="color" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="palette" class="w-4 h-4"></i> Accent Colour
            </label>
            <select name="color" id="color" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                @foreach (['amber','blue','emerald','violet','red','rose','cyan','pink','teal','indigo','orange','yellow','slate'] as $c)
                <option value="{{ $c }}" @selected(old('color', $badge->color ?? 'amber') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

        <div>
            <label for="q_coin_reward" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i data-lucide="coins" class="w-4 h-4"></i> Q Coin Reward
            </label>
            <input type="number" name="q_coin_reward" id="q_coin_reward" required min="0" value="{{ old('q_coin_reward', $badge->q_coin_reward ?? 0) }}"
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Credited via the Q Coin ledger.</p>
            @error('q_coin_reward') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label for="sort_order" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i data-lucide="arrow-up-down" class="w-4 h-4"></i> Sort Order
        </label>
        <input type="number" name="sort_order" id="sort_order" required min="0" value="{{ old('sort_order', $badge->sort_order ?? 0) }}"
               class="w-full md:w-40 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
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
BF

cat > resources/views/admin/badges/create.blade.php << 'BC'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Create Badge</h1>
    <form method="POST" action="{{ route('admin.badges.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @include('admin.badges._form')
    </form>
</div>
@endsection
BC

cat > resources/views/admin/badges/edit.blade.php << 'BE'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Edit Badge: {{ $badge->name }}</h1>
    <form method="POST" action="{{ route('admin.badges.update', $badge) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @include('admin.badges._form')
    </form>
</div>
@endsection
BE
echo "admin badge views created"

# ------------------------------------------------------- ADMIN: LEVELS
cat > resources/views/admin/levels/index.blade.php << 'LI'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Levels</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Configure user progression tiers</p>
        </div>
        <a href="{{ route('admin.levels.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> New Level
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
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Level</th>
                    <th class="px-6 py-3">Q Coins</th>
                    <th class="px-6 py-3">Gigs</th>
                    <th class="px-6 py-3">Sales</th>
                    <th class="px-6 py-3">Referrals</th>
                    <th class="px-6 py-3">Verified</th>
                    <th class="px-6 py-3">Users</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($levels as $level)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $level->level_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-2 font-medium text-gray-900 dark:text-white">
                            <i data-lucide="{{ $level->icon }}" class="w-4 h-4 text-blue-600"></i> {{ $level->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ number_format($level->min_q_coins) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $level->min_gigs_completed }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $level->min_sales }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $level->min_referrals }}</td>
                    <td class="px-6 py-4">
                        <i data-lucide="{{ $level->requires_verified ? 'shield-check' : 'minus' }}" class="w-4 h-4 {{ $level->requires_verified ? 'text-emerald-600' : 'text-gray-400' }}"></i>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $level->users_count }}</td>
                    <td class="px-6 py-4">
                        @if ($level->is_active)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i data-lucide="circle-check" class="w-3.5 h-3.5"></i> Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i data-lucide="circle-x" class="w-3.5 h-3.5"></i> Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.levels.edit', $level) }}" class="text-blue-600 hover:text-blue-800">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.levels.destroy', $level) }}" onsubmit="return confirm('Delete this level? Users on it become unranked until refresh.');">
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
                    <td colspan="10" class="px-6 py-8 text-center text-gray-600 dark:text-gray-400">
                        No levels yet. <a href="{{ route('admin.levels.create') }}" class="text-blue-600">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
LI

cat > resources/views/admin/levels/_form.blade.php << 'LF'
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
LF

cat > resources/views/admin/levels/create.blade.php << 'LC'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Create Level</h1>
    <form method="POST" action="{{ route('admin.levels.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @include('admin.levels._form')
    </form>
</div>
@endsection
LC

cat > resources/views/admin/levels/edit.blade.php << 'LE'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Edit Level: {{ $level->name }}</h1>
    <form method="POST" action="{{ route('admin.levels.update', $level) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @include('admin.levels._form')
    </form>
</div>
@endsection
LE
echo "admin level views created"
