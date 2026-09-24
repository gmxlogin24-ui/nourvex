set -e

cat > maintenance_auth_fix2.php << 'PHPEOF'
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

// ---- CSS: fix stacking, strengthen curve, force readable text ----
$old = <<<'EOD'
.auth-split-shell {
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    background: #eef1f6;
}
EOD;
$new = <<<'EOD'
.auth-split-shell {
    min-height: 100vh;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    background: #eef1f6;
}
@media (max-width: 900px) {
    .auth-split-shell { flex-direction: column; }
}
EOD;
edit('public/css/auth-split.css', $old, $new, 'explicit column stacking on mobile');

$old2 = <<<'EOD'
@media (max-width: 900px) {
    .auth-form-panel {
        margin-top: -26px;
        border-radius: 28px 28px 0 0;
        box-shadow: 0 -10px 30px rgba(20,30,60,0.08);
        padding: 32px 20px 40px;
        min-height: calc(100vh - 194px);
    }
}

.auth-form-card { width: 100%; max-width: 400px; margin: 0 auto; }
EOD;
$new2 = <<<'EOD'
@media (max-width: 900px) {
    .auth-form-panel {
        margin-top: -40px;
        border-radius: 36px 36px 0 0;
        box-shadow: 0 -14px 34px rgba(20,30,60,0.12);
        padding: 36px 20px 40px;
        min-height: calc(100vh - 180px);
        position: relative;
        z-index: 3;
    }
}

.auth-form-card { width: 100%; max-width: 400px; margin: 0 auto; }

/* Force readable dark text regardless of any global dark-theme rules,
   since this panel is always a light/white surface. */
.auth-form-panel,
.auth-form-panel h1,
.auth-form-panel p,
.auth-form-panel label,
.auth-form-panel span {
    color: #111827 !important;
}
.auth-form-panel .text-gray-400,
.auth-form-panel [style*="opacity:0.5"],
.auth-form-panel [style*="opacity:0.55"] {
    color: #9ca3af !important;
}
.auth-form-panel input::placeholder { color: #9ca3af !important; opacity: 1; }
EOD;
edit('public/css/auth-split.css', $old2, $new2, 'stronger curve + forced readable text');

// ---- CSS: black buttons, icon-only google circle ----
$old3 = <<<'EOD'
.auth-form-panel button[type="submit"],
.auth-form-panel .glass-btn {
    border-radius: 14px !important;
    background: var(--color-accent, #38D9E8) !important;
    border-color: transparent !important;
}
EOD;
$new3 = <<<'EOD'
.auth-form-panel button[type="submit"],
.auth-form-panel .glass-btn {
    border-radius: 14px !important;
    background: #111111 !important;
    border-color: transparent !important;
    color: #fff !important;
}
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
EOD;
edit('public/css/auth-split.css', $old3, $new3, 'black buttons + google circle style');

// ---- login.blade.php: reorder + restyle ----
$loginOld = <<<'EOD'
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">C</div>
        <h1 class="text-lg font-semibold">Qelvo</h1>
    </div>

    <p class="text-sm mb-6 text-gray-500">Welcome back. Log in to manage your gigs, sales, and earnings.</p>

    @if ($errors->any())
    <div class="bg-red-50 text-red-600 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
    <div class="bg-green-50 text-green-600 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <a href="{{ route('auth.google') }}" class="w-full py-3 rounded-full border border-gray-300 text-sm font-medium flex items-center justify-center gap-2 mb-4" style="text-decoration:none;color:inherit;">
        <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
        Continue with Google
    </a>
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs text-gray-400">or</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-xs text-gray-500">Email</label>
            <input type="email" name="email" required placeholder="e-mail address"
            class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-indigo-500 outline-none text-sm">
        </div>
        <div>
            <div class="flex justify-between items-center">
                <label class="text-xs text-gray-500">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-gray-500">Forgot?</a>
            </div>
            <input type="password" name="password" required placeholder="password"
            class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-indigo-500 outline-none text-sm">
        </div>
        <button type="submit" class="w-full py-3 rounded-full bg-indigo-600 text-white font-semibold text-sm">Log In</button>
    </form>

    <p class="text-xs text-gray-400 leading-relaxed mt-6">
        🔒 Your data and payments are protected with verified identity checks and licensed payment partners. We never ask for your PIN outside the app.
    </p>

    <p class="text-sm text-center mt-4 text-gray-500">
        New here? <a href="{{ route('register') }}" class="text-indigo-600 font-medium">Create an account</a>
    </p>
@endsection
EOD;

$loginNew = <<<'EOD'
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
            class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-indigo-500 outline-none text-sm">
        </div>
        <div>
            <div class="flex justify-between items-center">
                <label class="text-xs text-gray-500">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-gray-500">Forgot?</a>
            </div>
            <input type="password" name="password" required placeholder="password"
            class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-indigo-500 outline-none text-sm">
        </div>
        <button type="submit" class="w-full py-3 rounded-full bg-indigo-600 text-white font-semibold text-sm">Log In</button>
    </form>

    <div class="flex items-center gap-3 my-5">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs text-gray-400">or login with</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>

    <div class="flex justify-center">
        <a href="{{ route('auth.google') }}" class="auth-google-circle">
            <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
        </a>
    </div>

    <p class="text-xs text-gray-400 leading-relaxed mt-6">
        🔒 Your data and payments are protected with verified identity checks and licensed payment partners. We never ask for your PIN outside the app.
    </p>

    <p class="text-sm text-center mt-4 text-gray-500">
        New here? <a href="{{ route('register') }}" class="font-medium" style="color:#111827;">Create an account</a>
    </p>
@endsection
EOD;

edit('resources/views/auth/login.blade.php', $loginOld, $loginNew, 'full reorder + icon google + black button');

// ---- register.blade.php: icon-style google + color fix (order kept, file is long) ----
$regOld = <<<'EOD'
    <a href="{{ route('auth.google') }}" class="w-full py-3 rounded-full border border-gray-300 text-sm font-medium flex items-center justify-center gap-2 mb-4" style="text-decoration:none;color:inherit;">
        <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
        Continue with Google
    </a>
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs" style="opacity:0.5;">or</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>
EOD;

$regNew = <<<'EOD'
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
EOD;

edit('resources/views/auth/register.blade.php', $regOld, $regNew, 'icon-style google circle');

echo "\nDone.\n";
PHPEOF

php maintenance_auth_fix2.php
rm maintenance_auth_fix2.php

php artisan view:clear
php artisan cache:clear

echo ""
echo "=== Pulling rest of register.blade.php so we can finish its full reorder next (Google icon after submit, like login) ==="
tail -n 60 resources/views/auth/register.blade.php
