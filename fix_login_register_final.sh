set -e

echo "=== Full rewrite: login.blade.php ==="
cat > resources/views/auth/login.blade.php << 'VIEWEOF'
@extends('layouts.auth')
@section('content')
    <h1 class="text-lg font-semibold mb-1">Hi, Welcome Back</h1>
    <p class="text-sm mb-6 text-gray-500">Log in to manage your gigs, sales, and earnings.</p>

    @if ($errors->any())
    <div class="bg-red-50 text-red-600 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
    <div class="bg-green-50 text-green-600 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-xs text-gray-500">Email</label>
            <input type="email" name="email" required placeholder="e-mail address"
                class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-black outline-none text-sm">
        </div>
        <div>
            <div class="flex justify-between items-center">
                <label class="text-xs text-gray-500">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-gray-500">Forgot?</a>
            </div>
            <input type="password" name="password" required placeholder="password"
                class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-black outline-none text-sm">
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('auth.google') }}" class="auth-google-circle" style="flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
            </a>
            <button type="submit" class="flex-1 py-3 rounded-full bg-black text-white font-semibold text-sm">Log In</button>
        </div>
    </form>

    <p class="text-xs text-gray-400 leading-relaxed mt-6">
        🔒 Your data and payments are protected with verified identity checks and licensed payment partners. We never ask for your PIN outside the app.
    </p>

    <p class="text-sm text-center mt-4 text-gray-500">
        New here? <a href="{{ route('register') }}" class="font-medium" style="color:#111827;">Create an account</a>
    </p>
@endsection
VIEWEOF

echo ""
echo "=== Patching register.blade.php ==="
cat > maintenance_reg_final.php << 'PHPEOF'
<?php
function edit($file, $old, $new, $label) {
    if (!file_exists($file)) { echo "[MISSING FILE] $file ($label)\n"; return; }
    $content = file_get_contents($file);
    $count = substr_count($content, $old);
    if ($count === 0) { echo "[ANCHOR NOT FOUND] $file :: $label\n"; return; }
    if ($count > 1) { echo "[ANCHOR NOT UNIQUE ($count)] $file :: $label\n"; return; }
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "[OK] $file :: $label\n";
}

// Remove the top-positioned google icon + divider
$old1 = <<<'EOD'
    <div class="flex justify-center mb-4">
        <a href="{{ route('auth.google') }}" class="auth-google-circle">
            <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
        </a>
    </div>
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs" style="opacity:0.5;">or continue with email</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
EOD;
$new1 = <<<'EOD'
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
EOD;
edit('resources/views/auth/register.blade.php', $old1, $new1, 'remove top google icon block');

// Replace bottom submit button with side-by-side google + submit
$old2 = <<<'EOD'
        <div class="glass-btn-wrap mt-2">
            <button type="submit" class="glass-btn">Create Account</button>
        </div>
    </form>
EOD;
$new2 = <<<'EOD'
        <div class="flex items-center gap-3 mt-2">
            <a href="{{ route('auth.google') }}" class="auth-google-circle" style="flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
            </a>
            <div class="glass-btn-wrap flex-1">
                <button type="submit" class="glass-btn" style="width:100%;">Create Account</button>
            </div>
        </div>
    </form>
EOD;
edit('resources/views/auth/register.blade.php', $old2, $new2, 'side-by-side google + submit at bottom');

echo "\nDone.\n";
PHPEOF

php maintenance_reg_final.php
rm maintenance_reg_final.php

echo ""
echo "=== Ensuring auth-google-circle CSS class exists (idempotent) ==="
if grep -q "auth-google-circle" public/css/auth-split.css; then
    echo "auth-google-circle CSS already present, skipping"
else
    cat >> public/css/auth-split.css << 'CSSEOF'

.auth-google-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 1.5px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: border-color 0.2s ease, transform 0.15s ease;
}
.auth-google-circle:hover { border-color: #111827; transform: translateY(-1px); }
CSSEOF
    echo "auth-google-circle CSS appended"
fi

php artisan view:clear
php artisan cache:clear

echo ""
echo "=== Verifying final login.blade.php ==="
cat resources/views/auth/login.blade.php
echo ""
echo "=== Verifying final register.blade.php (relevant sections) ==="
grep -n -B 1 -A 3 "auth-google-circle\|glass-btn\b" resources/views/auth/register.blade.php
