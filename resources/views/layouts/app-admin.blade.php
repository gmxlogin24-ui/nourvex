<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Qelvo Admin - @yield('title', 'Dashboard')</title>
    @include('partials.favicons')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/app-enhance.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/neumorphic-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/theme-presets.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dispute-ring.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-shell.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/split-view.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/preloader.css') }}?v={{ time() }}">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        window.__adminNavStyle = localStorage.getItem('adminNavStyle') || 'sidebar';
    </script>
</head>
@php
    $routeName = request()->route() ? request()->route()->getName() : '';
@endphp
<body data-page="admin" data-theme-style="{{ auth()->user()->theme_style ?? 'glass' }}" data-admin-nav="sidebar"
    style="--user-accent: {{ auth()->user()->accent_color ?? '#38D9E8' }};">
    <script>document.body.setAttribute('data-admin-nav', window.__adminNavStyle);</script>
    @include('partials.preloader')
    @include('partials.nav-admin', ['activeRoute' => $routeName])
    <div class="admin-page-body">
        @yield('content')
    </div>
    <script>
        lucide.createIcons();
        function toggleAdminNavStyle() {
            const body = document.body;
            const current = body.getAttribute('data-admin-nav');
            const next = current === 'topbar' ? 'sidebar' : 'topbar';
            body.setAttribute('data-admin-nav', next);
            localStorage.setItem('adminNavStyle', next);
        }
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            html.setAttribute('data-theme', isDark ? 'light' : 'dark');
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        }
    </script>
    <script src="{{ asset('js/dispute-ring.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/lazy-mode.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/preloader.js') }}?v={{ time() }}"></script>
    @yield('scripts')
</body>
</html>
