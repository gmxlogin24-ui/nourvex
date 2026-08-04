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
    <style>
        .member-badge {
            display:inline-flex;align-items:center;gap:4px;
            padding:2px 10px 2px 8px;border-radius:999px;
            font-size:0.7rem;font-weight:700;letter-spacing:0.03em;
            line-height:1.6;white-space:nowrap;
        }
        .member-badge-bronze {
            background:linear-gradient(135deg, #cd7f32 0%, #a86428 100%);
            color:#fff5e6;
            box-shadow:0 1px 4px rgba(160,90,30,0.35), inset 0 1px 0 rgba(255,255,255,0.25);
        }
        .member-badge i { width:12px;height:12px; }
        .member-badge-gold {
            background:linear-gradient(135deg, #f5d576 0%, #d4a94a 100%);
            color:#4a3600;
            box-shadow:0 1px 4px rgba(180,140,30,0.35), inset 0 1px 0 rgba(255,255,255,0.4);
        }
    </style>
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

    $bgUrl = null;
    if (auth()->check() && auth()->user()->background_url) {
        $bgUrl = auth()->user()->background_url;
    }
    if ($pageKey && file_exists(public_path("images/bg-{$pageKey}.jpg"))) {
        $bgUrl = asset("images/bg-{$pageKey}.jpg");
    } elseif (file_exists(public_path('images/bg-default.jpg'))) {
        $bgUrl = asset('images/bg-default.jpg');
    }
@endphp
<body data-page="{{ $pageKey }}" @auth style="--user-accent: {{ auth()->user()->accent_color ?? '#38D9E8' }};" @endauth>
    @guest
    <div class="photo-bg" @if($bgUrl) style="background-image:url('{{ $bgUrl }}')" @endif></div>
    @endguest

    @include('partials.nav', ['active' => $pageKey, 'forceTopbar' => in_array($pageKey, ['welcome', 'pulse'])])

    <div class="app-push-content" id="appPushContent">
        @yield('content')
    </div>

    <script src="{{ asset('js/charts.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/reveal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/quick-actions-tilt.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/balance-tile-alive.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/app-enhance.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/balance-ledger.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/mobile-settings-menu.js') }}?v={{ time() }}"></script>
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

        if (window.location.search.includes('debug=overflow')) {
            document.querySelectorAll('*').forEach(function (el) {
                if (el.offsetWidth > document.documentElement.clientWidth) {
                    el.style.outline = '4px solid red';
                    console.log('OVERFLOW:', el.className || el.tagName, el.offsetWidth);
                }
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
