<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo Admin - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/app-enhance.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/neumorphic-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/theme-presets.css') }}?v={{ time() }}">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
@php
    $routeName = request()->route() ? request()->route()->getName() : '';
@endphp
<body data-page="admin" data-theme-style="{{ auth()->user()->theme_style ?? 'glass' }}"
    style="--user-accent: {{ auth()->user()->accent_color ?? '#38D9E8' }};">
    @include('partials.nav-admin', ['activeRoute' => $routeName])
    <div class="admin-page-body">
        @yield('content')
    </div>
    <script>
        lucide.createIcons();
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
