<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOURVEX - @yield('title', 'Work, sell, get paid')</title>
@include('partials.seo-head')
    @include('partials.favicons')
@include('partials.nv-theme-global')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <link rel="stylesheet" href="{{ asset('css/site-nav.css') }}?v={{ @filemtime(public_path('css/site-nav.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/preloader.css') }}?v={{ @filemtime(public_path('css/preloader.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/logo.css') }}?v={{ @filemtime(public_path('css/logo.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/auth-pages.css') }}?v={{ @filemtime(public_path('css/auth-pages.css')) }}">

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
    @include('partials.preloader')
    <div class="lpxm-oldnav-wrap">@include('partials.site-nav')</div>
@include('partials.mobile-topbar')

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
        <script src="{{ asset('js/preloader.js') }}?v={{ @filemtime(public_path('js/preloader.js')) }}"></script>
    @yield('scripts')
</body>
</html>
