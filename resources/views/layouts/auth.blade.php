<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo - @yield('title', 'Account')</title>
    @include('partials.favicons')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-v2.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/neumorphic-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/preloader.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/auth-split.css') }}?v={{ time() }}">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
@php
    $authBg = \App\Models\AppSetting::get('auth_background_image', asset('images/auth-bg-abstract.svg'));
    $authAnimated = \App\Models\AppSetting::get('auth_animated_elements', '1') === '1';
@endphp
<body class="auth-body" style="--user-accent: #38D9E8; --auth-bg-image: url('{{ $authBg }}');">
    @include('partials.preloader')
    <div class="auth-split-shell">
        <div class="auth-visual-panel">
            <div class="auth-visual-topnav">
                <div class="auth-visual-brand">
                    <svg viewBox="0 0 100 100" width="22" height="22">
                        <g transform="rotate(0 50 50)"><rect x="46" y="10" width="8" height="38" rx="4" fill="#fff"></rect><circle cx="50" cy="10" r="7" fill="#fff"></circle></g>
                        <g transform="rotate(120 50 50)"><rect x="46" y="10" width="8" height="38" rx="4" fill="#fff"></rect><circle cx="50" cy="10" r="7" fill="#fff"></circle></g>
                        <g transform="rotate(240 50 50)"><rect x="46" y="10" width="8" height="38" rx="4" fill="#fff"></rect><circle cx="50" cy="10" r="7" fill="#fff"></circle></g>
                    </svg>
                    Qelvo
                </div>
                <div class="auth-visual-nav-pills">
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Log In</a>
                    <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">Sign Up</a>
                </div>
            </div>
            <div class="auth-visual-tagline">
                <h2>Work. Sell. Get Paid.</h2>
                <p>Qelvo connects gig workers, sellers, and buyers on one trusted platform — verified, secured, and built for speed.</p>
            </div>
        </div>

        <div class="auth-form-panel">
            @if($authAnimated)
                <div class="auth-form-float" style="width:14px;height:14px;top:12%;left:15%;animation-delay:0s;"></div>
                <div class="auth-form-float" style="width:9px;height:9px;top:70%;left:80%;animation-delay:1.4s;"></div>
                <div class="auth-form-float" style="width:11px;height:11px;top:85%;left:20%;animation-delay:2.6s;"></div>
                <div class="auth-form-float" style="width:7px;height:7px;top:20%;left:85%;animation-delay:0.8s;"></div>
            @endif
            <div class="auth-form-card">
                <div class="auth-form-wrap" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/preloader.js') }}?v={{ time() }}"></script>
    @yield('scripts')
</body>
</html>
