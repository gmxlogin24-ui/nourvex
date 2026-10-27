<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAURVEX - @yield('title', 'Dashboard')</title>
    @include('partials.favicons')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#7c5cf0">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <meta name="tour-endpoint" content="{{ route('tour.complete') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/ScrollTrigger.min.js"></script>
    @unless(request()->routeIs('games.*'))<script src="{{ asset('js/no-motion.js') }}"></script>@endunless
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/dash-tweaks.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/holo-balance.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/halo-dock.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/app-enhance.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/balance-ledger.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/neumorphic-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/theme-presets.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/glass-dashboard.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-glass.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/nav-authenticated-green.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard2-minimal.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-premium.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-v3.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ time() }}">
    <script src="{{ asset('js/push.js') }}?v={{ time() }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/mobile-nav-styles.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/liquid-dock.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/nv-glass.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/preloader.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-aurora.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/aurora-pages.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/scrollbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/worlds.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dock-desktop.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/app-dark.css') }}?v={{ time() }}">

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        if (localStorage.getItem('sidebarCollapsed') === '1') {
            document.documentElement.setAttribute('data-sidebar', 'collapsed');
        }
    </script>
    <style>
    @media (max-width: 900px) {
      .neu-sidebar { display: none !important; }
      .d2-global-rail { display: none !important; }
    }
    @media (min-width: 901px) {
      .mnav-bar { display: none !important; }
    }
    @media (max-width: 900px) {
      .auth-page-shell, .app-push-content { margin-left: 0 !important; }
    }
    </style>
    <link rel="stylesheet" href="{{ asset('css/shell-fix.css') }}?v={{ time() }}">
    <script>try{var c=localStorage.getItem('nvEdge');if(c)document.documentElement.style.setProperty('--ov-edge',c);}catch(e){}</script>
<link rel="stylesheet" href="{{ asset('css/mono.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/buttons-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/readable.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/no-sidebar.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/no-motion.css') }}?v={{ time() }}">
</head>
@php
    $routeName = request()->route() ? request()->route()->getName() : '';
    $pageKey = $routeName ? explode('.', $routeName)[0] : '';
@endphp
<body data-page="{{ $pageKey }}" data-theme-style="{{ auth()->user()->theme_style ?? 'glass' }}" style="--user-accent: {{ auth()->user()->accent_color ?? '#0F9D6E' }};@if(auth()->user()?->background_url)--au-bg-image: url('{{ auth()->user()->background_url }}');@endif"
    data-sidebar-style="{{ auth()->user()->sidebar_style ?? 'rail' }}"
    data-mobile-nav-style="{{ auth()->user()->mobile_nav_style ?? 'painted_pill' }}">
    @include('partials.preloader')
    @include('partials.dg-lens')
    @include('partials.page-bar')
    <div class="nv-bg" aria-hidden="true"></div>
    @if(($pageKey ?? '') === 'dashboard')
        
    @endif

<div class="app-shell-flex">
        @if((auth()->user()->sidebar_style ?? 'rail') === 'rail')
        @include('partials.nav-rail', ['active' => $pageKey])
        @else
        @include('partials.nav-sidebar-classic', ['active' => $pageKey])
        @endif
        {{-- One dock for every page (overview renders its own inside its layout) --}}
        @include('partials.nv-dock')
            <div class="auth-page-shell">
                <div class="app-push-content auth-reveal-content" id="appPushContent">
                    @unless(($pageKey ?? '') === 'dashboard' || ($pageKey ?? '') === 'overview')
                        @if((auth()->user()->theme_style ?? 'glass') !== 'plain')
                            @include('partials.nav-icons')
                            @endif
                        @unless(($pageKey ?? '') === 'dashboard' || ($pageKey ?? '') === 'overview')
                            @include('partials.levi-bubble-v2')
                        @endunless
                    @endunless
@unless(in_array($pageKey ?? '', ['profile','dashboard','overview']))@endunless
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/world-timing.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/world-motion.js') }}?v={{ time() }}" defer></script>
    <script src="{{ asset('js/charts.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/reveal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/quick-actions-tilt.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/balance-tile-alive.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/app-enhance.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/balance-ledger.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/mobile-settings-menu.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/auth-shell.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/levi-tour.js') }}?v={{ time() }}"></script>
    <script>
        lucide.createIcons();
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector('i');
            function syncSidebarIcon() {
                const collapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
                icon.setAttribute('data-lucide', collapsed ? 'chevron-right' : 'chevron-left');
                lucide.createIcons();
            }
            syncSidebarIcon();
            sidebarToggle.addEventListener('click', function () {
                const collapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
                if (collapsed) {
                    document.documentElement.removeAttribute('data-sidebar');
                    localStorage.setItem('sidebarCollapsed', '0');
                } else {
                    document.documentElement.setAttribute('data-sidebar', 'collapsed');
                    localStorage.setItem('sidebarCollapsed', '1');
                }
                syncSidebarIcon();
            });
        }
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            html.setAttribute('data-theme', isDark ? 'light' : 'dark');
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        }
    </script>
        <script src="{{ asset('js/preloader.js') }}?v={{ time() }}"></script>
    @yield('scripts')
<script src="{{ asset('js/edge-dock.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/holo-balance.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/mnav-scroll-transparency.js') }}?v={{ time() }}"></script>
    @include('partials.world-hover')
    @include('partials.world-edge')
<script src="/js/auto-contrast.js?v=3" defer></script>
<script src="/js/glass-adapt.js?v=3" defer></script>
<script src="/js/theme-btn.js?v=1" defer></script>
<script src="/js/auto-dark.js?v=8" defer></script>
</body>
</html>
