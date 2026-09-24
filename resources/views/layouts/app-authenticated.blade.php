<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo - @yield('title', 'Dashboard')</title>
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
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    @if((auth()->user()->theme_style ?? 'glass') === 'qelvo-dark')
    <link rel="stylesheet" href="{{ asset('css/qelvo-dark.css') }}?v={{ time() }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ time() }}">
    <script src="{{ asset('js/push.js') }}?v={{ time() }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/mobile-nav-styles.css') }}?v={{ time() }}">
    <!-- old levi.css removed -->
    <link rel="stylesheet" href="{{ asset('css/preloader.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-aurora.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/aurora-pages.css') }}?v={{ time() }}">

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
</head>
@php
    $routeName = request()->route() ? request()->route()->getName() : '';
    $pageKey = $routeName ? explode('.', $routeName)[0] : '';
@endphp
<body data-page="{{ $pageKey }}" data-theme-style="{{ auth()->user()->theme_style ?? 'glass' }}" style="@if(auth()->user()?->background_url)--au-bg-image: url('{{ auth()->user()->background_url }}');@endif"
    data-sidebar-style="{{ auth()->user()->sidebar_style ?? 'rail' }}"
    data-mobile-nav-style="{{ auth()->user()->mobile_nav_style ?? 'painted_pill' }}" style="--user-accent: {{ auth()->user()->accent_color ?? '#0F9D6E' }};">
    @include('partials.preloader')
    @if((auth()->user()->theme_style ?? 'glass') === 'plain')
        @include('partials.dqm-topbar')
    @endif

<div class="app-shell-flex">
        @if((auth()->user()->sidebar_style ?? 'rail') === 'rail')
        @include('partials.nav-rail', ['active' => $pageKey])
        @else
        @include('partials.nav-sidebar-classic', ['active' => $pageKey])
        @endif
        @include('partials.nav-mobile-bar', ['active' => $pageKey])
        @include('partials.nav-edge-dock', ['active' => $pageKey])
            <div class="auth-page-shell">
                <div class="app-push-content auth-reveal-content" id="appPushContent">
                    @unless(($pageKey ?? '') === 'dashboard')
                        @if((auth()->user()->theme_style ?? 'glass') !== 'plain')
                            @include('partials.nav-icons')
                            @endif
                        @unless(($pageKey ?? '') === 'dashboard')
                            @include('partials.levi-bubble-v2')
                        @endunless
                    @endunless
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/charts.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/reveal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/quick-actions-tilt.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/balance-tile-alive.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/app-enhance.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/balance-ledger.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/mobile-settings-menu.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/auth-shell.js') }}?v={{ time() }}"></script>
    <!-- old levi-bubble.js removed -->
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
<!-- levi.js removed: duplicate of levi-bubble.js, was causing double message submission -->
<script src="{{ asset('js/edge-dock.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/mnav-scroll-transparency.js') }}?v={{ time() }}"></script>
</body>
</html>
