<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campulsy - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="{{ asset('css/app-v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/app-enhance.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/balance-ledger.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/guest-nav.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/neumorphic-auth.css') }}?v={{ time() }}">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        if (localStorage.getItem('sidebarCollapsed') === '1') {
            document.documentElement.setAttribute('data-sidebar', 'collapsed');
        }
    </script>
</head>
@php
    $routeName = request()->route() ? request()->route()->getName() : '';
    $pageKey = $routeName ? explode('.', $routeName)[0] : '';
@endphp
<body data-page="{{ $pageKey }}" style="--user-accent: {{ auth()->user()->accent_color ?? '#38D9E8' }};">
    <div class="auth-parallax-bg">
        <div class="auth-parallax-blob b1"></div>
        <div class="auth-parallax-blob b2"></div>
    </div>
    <div class="app-shell-flex">
        @include('partials.nav-authenticated', ['active' => $pageKey])

        <div class="app-shell-with-sidebar">
            <div class="auth-page-shell">
                <div class="app-push-content auth-reveal-content" id="appPushContent">
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
    @yield('scripts')
</body>
</html>
