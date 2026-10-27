@extends('layouts.app-authenticated')
@section('title', 'Appearance')
@section('content')
<main class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-1">Customize your appearance</h1>
    <p class="text-sm mb-6" style="color:var(--color-muted)">Changes preview instantly. Click Save to keep them.</p>

    <div class="glass-card stat-card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="font-weight:700;margin-bottom:1rem;">Global style</h3>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            @foreach(['glass' => 'Glass', 'neumorphic' => 'Neumorphic', 'plain' => 'Plain'] as $val => $label)
            <button type="button" class="appearance-choice" data-field="theme_style" data-value="{{ $val }}"
                style="padding:0.6rem 1.2rem;border-radius:999px;border:2px solid var(--border-color,#e2e2e2);background:var(--color-card,#fff);cursor:pointer;font-weight:600;font-size:0.85rem;">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="glass-card stat-card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="font-weight:700;margin-bottom:1rem;">Accent color</h3>
        <input type="color" id="accent-picker" value="{{ auth()->user()->accent_color ?? '#38D9E8' }}" style="width:60px;height:40px;border:none;border-radius:0.5rem;cursor:pointer;">
    </div>

    <div class="glass-card stat-card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="font-weight:700;margin-bottom:1rem;">Mobile bottom nav style <span style="font-weight:400;font-size:0.78rem;color:var(--color-muted)">(phones only — desktop keeps the sidebar)</span></h3>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            @foreach([
                'painted_pill' => 'Painted Pill',
                'liquid_glass' => 'Liquid Glass',
                'edge_dock' => 'Edge Dock',
                'halo_dock' => 'Halo Dock',
            ] as $val => $label)
            <button type="button" class="mnav-style-choice" data-value="{{ $val }}"
                style="padding:0.5rem 1rem;border-radius:999px;border:1.5px solid var(--border-color,#e2e2e2);background:var(--color-card,#fff);cursor:pointer;font-size:0.8rem;font-weight:600;">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="glass-card stat-card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="font-weight:700;margin-bottom:1rem;">Per-section overrides <span style="font-weight:400;font-size:0.78rem;color:var(--color-muted)">(leave on Default to follow global style)</span></h3>
        @foreach([
            'navbar_style' => 'Navbar',
            'topbar_style' => 'Top bar',
            'notif_style' => 'Notifications',
            'sidebar_style' => 'Sidebar',
            'card_style' => 'Cards',
        ] as $field => $label)
        <div style="margin-bottom:1.1rem;">
            <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.4rem;">{{ $label }}</label>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                @foreach(['default' => 'Default', 'glass' => 'Glass', 'neumorphic' => 'Neumorphic', 'plain' => 'Plain'] as $val => $vlabel)
                <button type="button" class="appearance-choice" data-field="{{ $field }}" data-value="{{ $val }}"
                    style="padding:0.4rem 0.9rem;border-radius:999px;border:1.5px solid var(--border-color,#e2e2e2);background:var(--color-card,#fff);cursor:pointer;font-size:0.78rem;font-weight:600;">
                    {{ $vlabel }}
                </button>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <button id="save-appearance" class="btn-primary" style="padding:0.75rem 2rem;border-radius:0.75rem;font-weight:700;cursor:pointer;border:none;">
        Save changes
    </button>
    <span id="save-status" style="margin-left:1rem;font-size:0.85rem;color:var(--color-accent3);"></span>
</main>

<script>
(function(){
    const state = {
        theme_style: '{{ auth()->user()->theme_style ?? "glass" }}',
        navbar_style: '{{ auth()->user()->navbar_style ?? "default" }}',
        topbar_style: '{{ auth()->user()->topbar_style ?? "default" }}',
        notif_style: '{{ auth()->user()->notif_style ?? "default" }}',
        sidebar_style: '{{ auth()->user()->sidebar_style ?? "default" }}',
        card_style: '{{ auth()->user()->card_style ?? "default" }}',
        accent_color: '{{ auth()->user()->accent_color ?? "#38D9E8" }}',
        mobile_nav_style: '{{ auth()->user()->mobile_nav_style ?? "painted_pill" }}',
    };

    const attrMap = {
        theme_style: 'data-theme-style',
        navbar_style: 'data-navbar-style',
        topbar_style: 'data-topbar-style',
        notif_style: 'data-notif-style',
        sidebar_style: 'data-sidebar-style',
        card_style: 'data-card-style',
    };

    function highlight() {
        document.querySelectorAll('.appearance-choice').forEach(btn => {
            const field = btn.dataset.field, value = btn.dataset.value;
            const isActive = state[field] === value;
            btn.style.borderColor = isActive ? 'var(--user-accent, #38D9E8)' : 'var(--border-color,#e2e2e2)';
            btn.style.background = isActive ? 'var(--user-accent, #38D9E8)' : 'var(--color-card,#fff)';
            btn.style.color = isActive ? '#fff' : 'var(--color-text,#333)';
        });
    }

    function applyPreview() {
        const body = document.body;
        Object.entries(attrMap).forEach(([field, attr]) => {
            let val = state[field];
            if (val === 'default') val = state.theme_style;
            body.setAttribute(attr, val);
        });
        body.style.setProperty('--user-accent', state.accent_color);
        highlight();
    }

    document.querySelectorAll('.appearance-choice').forEach(btn => {
        btn.addEventListener('click', () => {
            state[btn.dataset.field] = btn.dataset.value;
            applyPreview();
        });
    });

    document.getElementById('accent-picker').addEventListener('input', e => {
        state.accent_color = e.target.value;
        applyPreview();
    });

    document.querySelectorAll('.mnav-style-choice').forEach(btn => {
        btn.addEventListener('click', () => {
            state.mobile_nav_style = btn.dataset.value;
            document.body.setAttribute('data-mobile-nav-style', state.mobile_nav_style);
            document.querySelectorAll('.mnav-style-choice').forEach(b => {
                const active = b.dataset.value === state.mobile_nav_style;
                b.style.borderColor = active ? 'var(--user-accent, #38D9E8)' : 'var(--border-color,#e2e2e2)';
                b.style.background = active ? 'var(--user-accent, #38D9E8)' : 'var(--color-card,#fff)';
                b.style.color = active ? '#fff' : 'var(--color-text,#333)';
            });
        });
    });

    document.getElementById('save-appearance').addEventListener('click', () => {
        const status = document.getElementById('save-status');
        status.textContent = 'Saving...';
        fetch('{{ route("appearance.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(state),
        })
        .then(r => r.json())
        .then(data => { status.textContent = data.ok ? 'Saved ✓' : 'Error saving'; setTimeout(()=>status.textContent='', 2000); })
        .catch(() => { status.textContent = 'Error saving'; });
    });

    applyPreview();
})();
</script>
@endsection
