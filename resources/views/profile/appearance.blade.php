@extends('layouts.app-authenticated')
@section('content')

<main class="profile-shell p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;max-width:1240px;' : '' }}">
    <a href="{{ route('profile.index') }}" class="profile-back-link"><i data-lucide="arrow-left" class="w-4 h-4"></i> Profile</a>
    <div class="profile-detail-grid">
    <div class="profile-detail-main">
    <h1 class="text-2xl font-semibold mb-1 hoverfade-text">Appearance</h1>
    <p class="text-muted text-sm mb-6">How the app looks for you</p>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="glass-card p-6 mb-6 reveal-on-scroll {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">

            <div>
                <p class="text-sm font-medium mb-1">Profile Picture</p>
                <input type="file" name="avatar" accept="image/*" class="input-field !mt-1">
            </div>

            <div>
                <p class="text-sm font-medium mb-1">Personal Background</p>
                <p class="text-muted text-xs mb-2">This replaces the default background across the whole site, just for you.</p>
                @if($user->background_url)
                <img src="{{ $user->background_url }}" class="w-full h-28 object-cover rounded-xl mb-2">
                @endif
                <input type="file" name="background" accept="image/*" class="input-field !mt-1">
            </div>

            <div>
                <p class="text-sm font-medium mb-2">Theme Style</p>
                <p class="text-muted text-xs mb-3">Changes how every card and button looks across the whole app.</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        'glass' => ['label' => 'Glass', 'desc' => 'Frosted, translucent'],
                        'neumorphic' => ['label' => 'Neumorphic', 'desc' => 'Soft, embossed'],
                        'plain' => ['label' => 'Minimal', 'desc' => 'Flat, no shadows'],
                        'aurora' => ['label' => 'Aurora', 'desc' => 'Soft, puffed glass'],
                    ] as $style => $meta)
                    <label class="theme-style-option" style="cursor:pointer;">
                        <input type="radio" name="theme_style" value="{{ $style }}" {{ ($user->theme_style ?? 'glass') === $style ? 'checked' : '' }} class="theme-style-radio" style="display:none;" onchange="previewThemeStyle('{{ $style }}')">
                        <div class="theme-style-swatch theme-style-swatch-{{ $style }}" data-style-swatch="{{ $style }}" style="border-radius:0.75rem;padding:0.75rem;text-align:center;border:2px solid var(--border-color);transition:border-color 0.2s;">
                            <p class="text-sm font-medium">{{ $meta['label'] }}</p>
                            <p class="text-muted text-xs mt-1">{{ $meta['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-sm font-medium mb-2">Accent Color</p>
                <div class="flex items-center gap-3 flex-wrap">
                    <input type="color" name="accent_color" id="accentColorInput" value="{{ $user->accent_color ?? '#0F9D6E' }}" style="width:3rem;height:2.6rem;border-radius:0.75rem;border:1px solid var(--border-color);background:none;cursor:pointer;">
                    <div class="flex gap-2">
                        @foreach(['#0F9D6E','#F472B6','#A78BFA','#FB923C','#4ADE80','#F43F5E','#FFFFFF'] as $swatch)
                        <button type="button" onclick="document.getElementById('accentColorInput').value='{{ $swatch }}'; document.getElementById('accentColorInput').dispatchEvent(new Event('input'));" class="w-7 h-7 rounded-full" style="background:{{ $swatch }};border:2px solid {{ strtoupper($swatch) === '#FFFFFF' ? '#94a3b8' : 'var(--border-color)' }};"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <p class="text-sm font-medium mb-2">Dock Style</p>
                <p class="text-muted text-xs mb-3">Changes the look of the bottom navigation bar on mobile.</p>
                <div class="grid grid-cols-3 gap-3">
                    @foreach([
                        'painted_pill' => ['label' => 'Painted Pill', 'desc' => 'Rounded bottom, painted icon badges'],
                        'edge_dock' => ['label' => 'Edge Dock', 'desc' => 'Draggable floating handle'],
                    ] as $style => $meta)
                    <label class="theme-style-option" style="cursor:pointer;">
                        <input type="radio" name="mobile_nav_style" value="{{ $style }}" {{ ($user->mobile_nav_style ?? 'painted_pill') === $style ? 'checked' : '' }} class="dock-style-radio" style="display:none;" onchange="previewDockStyle('{{ $style }}')">
                        <div class="theme-style-swatch" data-dock-swatch="{{ $style }}" style="border-radius:0.75rem;padding:0.75rem;text-align:center;border:2px solid {{ ($user->mobile_nav_style ?? 'painted_pill') === $style ? 'var(--color-accent)' : 'var(--border-color)' }};transition:border-color 0.2s;">
                            <p class="text-sm font-medium">{{ $meta['label'] }}</p>
                            <p class="text-muted text-xs mt-1">{{ $meta['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-sm font-medium mb-2">Sidebar Style</p>
                <p class="text-muted text-xs mb-3">Choose which sidebar design you see across the whole app.</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        'rail' => ['label' => 'Rail (New)', 'desc' => 'Compact icon rail, neumorphic'],
                        'classic' => ['label' => 'Classic', 'desc' => 'Original full sidebar'],
                    ] as $style => $meta)
                    <label class="theme-style-option" style="cursor:pointer;">
                        <input type="radio" name="sidebar_style" value="{{ $style }}" {{ ($user->sidebar_style ?? 'rail') === $style ? 'checked' : '' }} class="sidebar-style-radio" style="display:none;" onchange="previewSidebarStyle('{{ $style }}')">
                        <div class="theme-style-swatch" data-sidebar-swatch="{{ $style }}" style="border-radius:0.75rem;padding:0.75rem;text-align:center;border:2px solid {{ ($user->sidebar_style ?? 'rail') === $style ? 'var(--color-accent)' : 'var(--border-color)' }};transition:border-color 0.2s;">
                            <p class="text-sm font-medium">{{ $meta['label'] }}</p>
                            <p class="text-muted text-xs mt-1">{{ $meta['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Appearance</button>
        </form>

        <div class="flex items-center justify-between mt-5 pt-5" style="border-top:1px solid var(--border-color);">
            <div>
                <p class="text-sm font-medium">Dark Mode</p>
                <p class="text-muted text-xs mt-1">Switch between light and dark</p>
            </div>
            <button onclick="toggleTheme()" class="theme-toggle"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
        </div>
    </div>

        </div>
    <div class="profile-detail-aside profile-shape-appearance" id="appearanceShapeAside" style="background: linear-gradient(135deg, var(--user-accent, #0F9D6E), color-mix(in srgb, var(--user-accent, #0F9D6E) 40%, white));">
        <div class="profile-shape-inner">
            <p class="profile-shape-title" style="color:#fff;">Your Accent</p>
            <p class="profile-shape-sub" style="color:rgba(255,255,255,0.85);">Live preview</p>
        </div>
    </div>
    </div>

    <script>
    document.getElementById('accentColorInput')?.addEventListener('input', function () {
        document.body.style.setProperty('--user-accent', this.value);
        const aside = document.getElementById('appearanceShapeAside');
        if (aside) aside.style.background = 'linear-gradient(135deg, ' + this.value + ', white)';
    });
    function previewThemeStyle(style) {
        document.body.setAttribute('data-theme-style', style);
        document.querySelectorAll('[data-style-swatch]').forEach(el => {
            el.style.borderColor = el.dataset.styleSwatch === style ? 'var(--color-accent)' : 'var(--border-color)';
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        const checked = document.querySelector('.theme-style-radio:checked');
        if (checked) previewThemeStyle(checked.value);
    });
    function previewDockStyle(style) {
        document.body.setAttribute('data-mobile-nav-style', style);
        document.querySelectorAll('[data-dock-swatch]').forEach(el => {
            el.style.borderColor = el.dataset.dockSwatch === style ? 'var(--color-accent)' : 'var(--border-color)';
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        const checkedDock = document.querySelector('.dock-style-radio:checked');
        if (checkedDock) previewDockStyle(checkedDock.value);
    });
    function previewSidebarStyle(style) {
        document.body.setAttribute('data-sidebar-style', style);
        document.querySelectorAll('[data-sidebar-swatch]').forEach(el => {
            el.style.borderColor = el.dataset.sidebarSwatch === style ? 'var(--color-accent)' : 'var(--border-color)';
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        const checkedSidebar = document.querySelector('.sidebar-style-radio:checked');
        if (checkedSidebar) previewSidebarStyle(checkedSidebar.value);
    });
    </script>
</main>
@endsection
