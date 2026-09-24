set -e

cat > public/css/auth-split.css << 'CSSEOF'
.auth-split-shell {
    display: flex;
    min-height: 100vh;
    width: 100%;
}
.auth-visual-panel {
    display: none;
    flex: 1 1 50%;
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 20% 20%, color-mix(in srgb, var(--color-accent, #38D9E8) 30%, transparent), transparent 55%),
        radial-gradient(circle at 80% 80%, color-mix(in srgb, var(--color-accent, #38D9E8) 18%, transparent), transparent 50%),
        linear-gradient(160deg, #05070c 0%, #0b0f1a 55%, #060810 100%);
    align-items: center;
    justify-content: center;
    padding: 48px;
}
@media (min-width: 901px) {
    .auth-visual-panel { display: flex; }
}
.auth-visual-particle {
    position: absolute;
    border-radius: 50%;
    background: color-mix(in srgb, var(--color-accent, #38D9E8) 70%, white);
    opacity: 0.5;
    filter: blur(0.5px);
    animation: auth-particle-float 6s ease-in-out infinite;
}
@keyframes auth-particle-float {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.35; }
    50% { transform: translateY(-18px) scale(1.15); opacity: 0.7; }
}
.auth-visual-mark {
    width: 180px;
    height: 180px;
    position: relative;
    z-index: 2;
    animation: qelvo-breathe 2.6s cubic-bezier(0.45, 0, 0.55, 1) infinite;
    filter: drop-shadow(0 12px 34px color-mix(in srgb, var(--color-accent, #38D9E8) 55%, transparent));
}
.auth-visual-mark svg { width: 100%; height: 100%; overflow: visible; }
.auth-visual-mark .qelvo-mark-orbit {
    transform-origin: 50px 50px;
    animation: qelvo-orbit-spin 5s cubic-bezier(0.65, 0, 0.35, 1) infinite;
}
.auth-visual-tagline {
    position: absolute;
    bottom: 56px;
    left: 48px;
    right: 48px;
    z-index: 2;
    color: rgba(255,255,255,0.92);
}
.auth-visual-tagline h2 {
    font-size: 26px;
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 8px;
}
.auth-visual-tagline p {
    font-size: 13px;
    color: rgba(255,255,255,0.55);
    max-width: 360px;
}
.auth-form-panel {
    flex: 1 1 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    background:
        radial-gradient(circle at 90% 0%, color-mix(in srgb, var(--color-accent, #38D9E8) 10%, transparent), transparent 55%),
        var(--color-bg, #f4f6fb);
    min-height: 100vh;
}
.auth-form-card {
    width: 100%;
    max-width: 420px;
    background: color-mix(in srgb, var(--color-card, #ffffff) 92%, transparent);
    backdrop-filter: blur(18px) saturate(160%);
    -webkit-backdrop-filter: blur(18px) saturate(160%);
    border: 1px solid var(--border-color, rgba(0,0,0,0.06));
    border-radius: 28px;
    box-shadow: 0 20px 50px rgba(20,30,60,0.10);
    padding: 36px 30px;
}
@media (max-width: 480px) {
    .auth-form-card { padding: 28px 20px; border-radius: 22px; box-shadow: none; border: none; background: transparent; backdrop-filter: none; }
    .auth-form-panel { padding: 24px 16px; }
}
CSSEOF

cat > resources/views/layouts/auth.blade.php << 'VIEWEOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo - @yield('title', 'Account')</title>
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
<body class="auth-body" style="--user-accent: #38D9E8;">
    @include('partials.preloader')
    <div class="auth-split-shell">
        <div class="auth-visual-panel">
            <div class="auth-visual-particle" style="width:8px;height:8px;top:22%;left:30%;animation-delay:0s;"></div>
            <div class="auth-visual-particle" style="width:5px;height:5px;top:65%;left:70%;animation-delay:1.2s;"></div>
            <div class="auth-visual-particle" style="width:6px;height:6px;top:40%;left:80%;animation-delay:2.4s;"></div>
            <div class="auth-visual-particle" style="width:4px;height:4px;top:75%;left:20%;animation-delay:3.1s;"></div>
            <div class="auth-visual-particle" style="width:7px;height:7px;top:15%;left:65%;animation-delay:0.6s;"></div>

            <div class="auth-visual-mark">
                <svg viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="authMarkGrad" x1="0" y1="1" x2="0" y2="0">
                            <stop offset="0%" stop-color="#38D9E8" stop-opacity="0.08"></stop>
                            <stop offset="100%" stop-color="#38D9E8" stop-opacity="1"></stop>
                        </linearGradient>
                    </defs>
                    <g class="qelvo-mark-orbit">
                        <g transform="rotate(0 50 50)">
                            <rect x="46" y="10" width="8" height="38" rx="4" fill="url(#authMarkGrad)"></rect>
                            <circle cx="50" cy="10" r="7" fill="#38D9E8"></circle>
                        </g>
                        <g transform="rotate(120 50 50)">
                            <rect x="46" y="10" width="8" height="38" rx="4" fill="url(#authMarkGrad)"></rect>
                            <circle cx="50" cy="10" r="7" fill="#38D9E8"></circle>
                        </g>
                        <g transform="rotate(240 50 50)">
                            <rect x="46" y="10" width="8" height="38" rx="4" fill="url(#authMarkGrad)"></rect>
                            <circle cx="50" cy="10" r="7" fill="#38D9E8"></circle>
                        </g>
                    </g>
                </svg>
            </div>

            <div class="auth-visual-tagline">
                <h2>Work. Sell. Get Paid.</h2>
                <p>Qelvo connects gig workers, sellers, and buyers on one trusted platform — verified, secured, and built for speed.</p>
            </div>
        </div>

        <div class="auth-form-panel">
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
VIEWEOF

php artisan view:clear
php artisan cache:clear
