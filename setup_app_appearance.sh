set -e

echo "=== Rewriting SiteSettingsController ==="
cat > app/Http/Controllers/Admin/SiteSettingsController.php << 'CTRLEOF'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        return view('admin.settings.appearance', [
            'currentBg' => AppSetting::get('auth_background_image'),
            'animatedEnabled' => AppSetting::get('auth_animated_elements', '1') === '1',
        ]);
    }

    public function updateBackground(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate(['image' => 'required|image|max:5120']);

        $path = $request->file('image')->store('branding', 'public');
        AppSetting::set('auth_background_image', Storage::url($path));

        return back()->with('status', 'Background image updated.');
    }

    public function resetBackground()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        AppSetting::set('auth_background_image', null);
        return back()->with('status', 'Reverted to default background.');
    }

    public function updateAnimations(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        AppSetting::set('auth_animated_elements', $request->boolean('enabled') ? '1' : '0');
        return back()->with('status', 'Animation setting updated.');
    }
}
CTRLEOF

echo ""
echo "=== Writing new App Appearance view ==="
cat > resources/views/admin/settings/appearance.blade.php << 'VIEWEOF'
@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">App Appearance</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
    <div class="stat-card mb-4" style="border-color:#ef4444;"><p>{{ $errors->first() }}</p></div>
    @endif

    <div class="stat-card mb-4">
        <h2 class="font-semibold mb-3">Login / Register Background</h2>
        <p class="text-sm text-muted mb-2">Current background:</p>
        @if($currentBg)
            <img src="{{ $currentBg }}" style="width:100%;max-width:400px;border-radius:12px;margin-bottom:0.75rem;">
        @else
            <p class="text-sm text-muted mb-3">No custom image set — using the default abstract background.</p>
        @endif

        <form method="POST" action="{{ route('admin.settings.appearance.background') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
            @csrf
            <input type="file" name="image" accept="image/*" required>
            <button class="balance-chip primary" style="align-self:flex-start;">Upload New Background</button>
        </form>
        @if($currentBg)
        <form method="POST" action="{{ route('admin.settings.appearance.background.reset') }}" class="mt-2">
            @csrf
            <button class="balance-chip" style="font-size:0.8rem;">Revert to Default</button>
        </form>
        @endif
    </div>

    <div class="stat-card">
        <h2 class="font-semibold mb-3">Login / Register Card Animation</h2>
        <p class="text-sm text-muted mb-3">Floating decorative elements drifting behind the login/register form.</p>
        <form method="POST" action="{{ route('admin.settings.appearance.animations') }}">
            @csrf
            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="enabled" value="1" @checked($animatedEnabled)>
                Enable floating elements
            </label>
            <button class="balance-chip primary">Save</button>
        </form>
    </div>
</div>
@endsection
VIEWEOF

echo ""
echo "=== Applying edits ==="
cat > maintenance_app_appearance.php << 'PHPEOF'
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

// ---- routes: replace old auth-background routes with unified appearance routes ----
$old = <<<'EOD'
        Route::get('/settings/auth-background', [App\Http\Controllers\Admin\SiteSettingsController::class, 'editAuthBackground'])->name('settings.auth-background');
        Route::post('/settings/auth-background', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateAuthBackground'])->name('settings.auth-background.update');
        Route::post('/settings/auth-background/reset', [App\Http\Controllers\Admin\SiteSettingsController::class, 'resetAuthBackground'])->name('settings.auth-background.reset');
EOD;
$new = <<<'EOD'
        Route::get('/settings/appearance', [App\Http\Controllers\Admin\SiteSettingsController::class, 'index'])->name('settings.appearance');
        Route::post('/settings/appearance/background', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateBackground'])->name('settings.appearance.background');
        Route::post('/settings/appearance/background/reset', [App\Http\Controllers\Admin\SiteSettingsController::class, 'resetBackground'])->name('settings.appearance.background.reset');
        Route::post('/settings/appearance/animations', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateAnimations'])->name('settings.appearance.animations');
EOD;
edit('routes/web.php', $old, $new, 'unified appearance routes');

// ---- nav-admin.blade.php: rename link ----
$old2 = <<<'EOD'
        <a href="{{ route('admin.settings.auth-background') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.settings.auth-background' ? 'active' : '' }}" title="Login Background">
            <i data-lucide="image"></i><span>Bg Img</span>
        </a>
EOD;
$new2 = <<<'EOD'
        <a href="{{ route('admin.settings.appearance') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.settings.appearance' ? 'active' : '' }}" title="App Appearance">
            <i data-lucide="paintbrush"></i><span>Appear.</span>
        </a>
EOD;
edit('resources/views/partials/nav-admin.blade.php', $old2, $new2, 'App Appearance nav link');

// ---- login.blade.php: side-by-side google + login button ----
$loginOld = <<<'EOD'
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
EOD;

$loginNew = <<<'EOD'
        <div class="flex items-center gap-3">
            <a href="{{ route('auth.google') }}" class="auth-google-circle" style="flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
            </a>
            <button type="submit" class="flex-1 py-3 rounded-full bg-indigo-600 text-white font-semibold text-sm">Log In</button>
        </div>
    </form>
EOD;

edit('resources/views/auth/login.blade.php', $loginOld, $loginNew, 'side-by-side google + login button');

echo "\nDone.\n";
PHPEOF

php maintenance_app_appearance.php
rm maintenance_app_appearance.php

echo ""
echo "=== Adding floating elements CSS ==="
cat >> public/css/auth-split.css << 'CSSEOF'

.auth-form-float {
    position: absolute;
    border-radius: 50%;
    background: color-mix(in srgb, var(--color-accent, #38D9E8) 55%, transparent);
    filter: blur(1px);
    opacity: 0.4;
    z-index: 0;
    pointer-events: none;
    animation: auth-form-float-move 7s ease-in-out infinite;
}
@keyframes auth-form-float-move {
    0%, 100% { transform: translateY(0) translateX(0) scale(1); opacity: 0.3; }
    50% { transform: translateY(-16px) translateX(6px) scale(1.2); opacity: 0.55; }
}
CSSEOF

echo ""
echo "=== Updating layouts/auth.blade.php with animated floats + new setting ==="
cat > maintenance_auth_layout.php << 'PHPEOF'
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

$old = <<<'EOD'
@php
    $authBg = \App\Models\AppSetting::get('auth_background_image', asset('images/auth-bg-abstract.svg'));
@endphp
EOD;
$new = <<<'EOD'
@php
    $authBg = \App\Models\AppSetting::get('auth_background_image', asset('images/auth-bg-abstract.svg'));
    $authAnimated = \App\Models\AppSetting::get('auth_animated_elements', '1') === '1';
@endphp
EOD;
edit('resources/views/layouts/auth.blade.php', $old, $new, 'animated setting variable');

$old2 = <<<'EOD'
        <div class="auth-form-panel">
            <div class="auth-form-card">
EOD;
$new2 = <<<'EOD'
        <div class="auth-form-panel">
            @if($authAnimated)
                <div class="auth-form-float" style="width:14px;height:14px;top:12%;left:15%;animation-delay:0s;"></div>
                <div class="auth-form-float" style="width:9px;height:9px;top:70%;left:80%;animation-delay:1.4s;"></div>
                <div class="auth-form-float" style="width:11px;height:11px;top:85%;left:20%;animation-delay:2.6s;"></div>
                <div class="auth-form-float" style="width:7px;height:7px;top:20%;left:85%;animation-delay:0.8s;"></div>
            @endif
            <div class="auth-form-card">
EOD;
edit('resources/views/layouts/auth.blade.php', $old2, $new2, 'floating elements markup');

echo "\nDone.\n";
PHPEOF

php maintenance_auth_layout.php
rm maintenance_auth_layout.php

php artisan view:clear
php artisan cache:clear
php artisan route:clear

echo ""
echo "=== Verifying routes ==="
php artisan route:list | grep -i "settings.appearance"

echo ""
echo "=== Pulling current register.blade.php so I can match its side-by-side buttons in the next pass ==="
cat resources/views/auth/register.blade.php
