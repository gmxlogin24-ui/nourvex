<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo - @yield('title', 'Work, sell, get paid')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="{{ asset('css/site-nav.css') }}?v={{ time() }}">
    <script src="{{ asset('js/site-nav.js') }}?v={{ time() }}" defer></script>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
@php
    $routeName = request()->route() ? request()->route()->getName() : '';
    $pageKey = $routeName ? explode('.', $routeName)[0] : '';
@endphp
<body data-page="{{ $pageKey }}" class="bg-white">
    @include('partials.site-nav')

    <div id="snPushContent">
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
