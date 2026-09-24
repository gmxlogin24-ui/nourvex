set -e

echo "=== Migration: settings table ==="
php artisan make:migration create_settings_table
MIGRATION_FILE=$(ls -t database/migrations/*create_settings_table.php | head -1)
cat > "$MIGRATION_FILE" << 'MIGEOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
MIGEOF
echo "Migration written: $MIGRATION_FILE"

echo ""
echo "=== AppSetting model ==="
cat > app/Models/AppSetting.php << 'MODELEOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
MODELEOF

echo ""
echo "=== SiteSettingsController ==="
mkdir -p app/Http/Controllers/Admin
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
    public function editAuthBackground()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        return view('admin.settings.auth-background', [
            'current' => AppSetting::get('auth_background_image'),
        ]);
    }

    public function updateAuthBackground(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store('branding', 'public');
        AppSetting::set('auth_background_image', Storage::url($path));

        return back()->with('status', 'Background image updated.');
    }

    public function resetAuthBackground()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        AppSetting::set('auth_background_image', null);
        return back()->with('status', 'Reverted to default background.');
    }
}
CTRLEOF

echo ""
echo "=== Admin settings view ==="
mkdir -p resources/views/admin/settings
cat > resources/views/admin/settings/auth-background.blade.php << 'VIEWEOF'
@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Login/Register Background</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
    <div class="stat-card mb-4" style="border-color:#ef4444;"><p>{{ $errors->first() }}</p></div>
    @endif

    <div class="stat-card mb-4">
        <p class="text-sm text-muted mb-2">Current background:</p>
        @if($current)
            <img src="{{ $current }}" style="width:100%;max-width:400px;border-radius:12px;">
        @else
            <p class="text-sm text-muted">No custom image set — using the default abstract background.</p>
        @endif
    </div>

    <div class="stat-card">
        <form method="POST" action="{{ route('admin.settings.auth-background.update') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
            @csrf
            <input type="file" name="image" accept="image/*" required>
            <button class="balance-chip primary" style="align-self:flex-start;">Upload New Background</button>
        </form>
        @if($current)
        <form method="POST" action="{{ route('admin.settings.auth-background.reset') }}" class="mt-3">
            @csrf
            <button class="balance-chip" style="font-size:0.8rem;">Revert to Default</button>
        </form>
        @endif
    </div>
</div>
@endsection
VIEWEOF

echo ""
echo "=== Applying route + nav edits ==="
cat > maintenance_auth_redesign.php << 'PHPEOF'
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
        Route::put('/feature-gates/{featureGate}', [App\Http\Controllers\Admin\FeatureGateController::class, 'update'])->name('feature-gates.update');
EOD;
$new = <<<'EOD'
        Route::put('/feature-gates/{featureGate}', [App\Http\Controllers\Admin\FeatureGateController::class, 'update'])->name('feature-gates.update');

        Route::get('/settings/auth-background', [App\Http\Controllers\Admin\SiteSettingsController::class, 'editAuthBackground'])->name('settings.auth-background');
        Route::post('/settings/auth-background', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateAuthBackground'])->name('settings.auth-background.update');
        Route::post('/settings/auth-background/reset', [App\Http\Controllers\Admin\SiteSettingsController::class, 'resetAuthBackground'])->name('settings.auth-background.reset');
EOD;
edit('routes/web.php', $old, $new, 'settings routes');

$old2 = <<<'EOD'
        <a href="{{ route('admin.subscriptions') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.subscriptions' ? 'active' : '' }}" title="Subscriptions">
            <i data-lucide="credit-card"></i><span>Subs</span>
        </a>
EOD;
$new2 = <<<'EOD'
        <a href="{{ route('admin.subscriptions') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.subscriptions' ? 'active' : '' }}" title="Subscriptions">
            <i data-lucide="credit-card"></i><span>Subs</span>
        </a>
        <a href="{{ route('admin.settings.auth-background') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.settings.auth-background' ? 'active' : '' }}" title="Login Background">
            <i data-lucide="image"></i><span>Bg Img</span>
        </a>
EOD;
edit('resources/views/partials/nav-admin.blade.php', $old2, $new2, 'nav link (sidebar variant)');

echo "\nDone.\n";
PHPEOF

php maintenance_auth_redesign.php
rm maintenance_auth_redesign.php

echo ""
echo "=== Ensuring storage symlink exists ==="
if [ ! -L public/storage ]; then
  php artisan storage:link
else
  echo "storage symlink already exists, skipping"
fi

echo ""
echo "=== Rewriting auth-split.css (full redesign) ==="
cat > public/css/auth-split.css << 'CSSEOF'
.auth-split-shell {
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    background: #eef1f6;
}
@media (min-width: 901px) {
    .auth-split-shell { padding: 40px; }
}

.auth-visual-panel {
    position: relative;
    overflow: hidden;
    background-image: var(--auth-bg-image);
    background-size: cover;
    background-position: center;
}
@media (min-width: 901px) {
    .auth-visual-panel {
        flex: 0 0 44%;
        border-radius: 32px;
        transform: rotate(-2.5deg);
        box-shadow: 0 30px 70px rgba(10,15,30,0.35);
        z-index: 2;
    }
}
@media (max-width: 900px) {
    .auth-visual-panel { width: 100%; height: 220px; flex: none; }
    .auth-visual-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(5,10,20,0.15), rgba(5,10,20,0.68));
    }
}

.auth-visual-topnav {
    position: absolute;
    top: 22px; left: 22px; right: 22px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.auth-visual-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-weight: 700;
    font-size: 15px;
}
.auth-visual-nav-pills { display: flex; gap: 8px; }
.auth-visual-nav-pills a {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.5);
    color: #fff;
    transition: all 0.2s ease;
}
.auth-visual-nav-pills a.active { background: #fff; color: #111; border-color: #fff; }

.auth-visual-tagline {
    position: absolute;
    left: 22px; right: 22px; bottom: 22px;
    z-index: 2;
    color: #fff;
}
.auth-visual-tagline h2 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.auth-visual-tagline p { font-size: 12px; opacity: 0.75; max-width: 320px; }
@media (max-width: 900px) {
    .auth-visual-tagline h2 { font-size: 22px; }
    .auth-visual-tagline p { display: none; }
}

.auth-form-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    position: relative;
}
@media (min-width: 901px) {
    .auth-form-panel {
        margin-left: -60px;
        padding-left: 100px;
        border-radius: 32px;
        box-shadow: 0 20px 50px rgba(20,30,60,0.10);
        z-index: 1;
    }
}
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

/* Restyle existing shared form controls (login.blade.php + register.blade.php) */
.auth-form-panel input[type="email"],
.auth-form-panel input[type="password"],
.auth-form-panel input[type="text"],
.auth-form-panel input.glass-input,
.auth-form-panel select.glass-input,
.auth-form-panel select {
    border-radius: 14px !important;
    border: 1.5px solid #e5e7eb !important;
    background: #f9fafb !important;
}
.auth-form-panel input:focus,
.auth-form-panel select:focus {
    border-color: var(--color-accent, #38D9E8) !important;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-accent, #38D9E8) 15%, transparent) !important;
    outline: none !important;
}
.auth-form-panel a[href*="auth/google"] { border-radius: 14px !important; }
.auth-form-panel button[type="submit"],
.auth-form-panel .glass-btn {
    border-radius: 14px !important;
    background: var(--color-accent, #38D9E8) !important;
    border-color: transparent !important;
}
.auth-form-panel h1 { font-size: 26px !important; font-weight: 800 !important; }
CSSEOF

echo ""
echo "=== Rewriting layouts/auth.blade.php ==="
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
@php
    $authBg = \App\Models\AppSetting::get('auth_background_image', asset('images/auth-bg-abstract.svg'));
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

echo ""
echo "=== Running migration + clearing caches ==="
php artisan migrate --force
php artisan view:clear
php artisan cache:clear
php artisan route:clear

echo ""
echo "=== Verifying new routes ==="
php artisan route:list | grep -i "settings.auth-background"
