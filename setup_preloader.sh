set -e

echo "=== Writing preloader.css ==="
cat > public/css/preloader.css << 'CSSEOF'
.qelvo-preloader {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: color-mix(in srgb, var(--color-bg, #fafafa) 88%, transparent);
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.45s cubic-bezier(0.22, 1, 0.36, 1), visibility 0.45s;
}
.qelvo-preloader.is-visible {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}
.qelvo-preloader-ring {
  width: 52px;
  height: 52px;
  animation: qelvo-breathe 1.8s cubic-bezier(0.45, 0, 0.55, 1) infinite;
}
.qelvo-preloader-ring svg {
  width: 100%;
  height: 100%;
  animation: qelvo-spin 1s cubic-bezier(0.65, 0, 0.35, 1) infinite;
  transform-origin: 50% 50%;
}
.qelvo-preloader-track {
  fill: none;
  stroke: var(--border-color, rgba(0,0,0,0.08));
  stroke-width: 3;
}
.qelvo-preloader-arc {
  fill: none;
  stroke: var(--color-accent, #38D9E8);
  stroke-width: 3;
  stroke-linecap: round;
  stroke-dasharray: 90 126;
  transform-origin: 50% 50%;
}
@keyframes qelvo-spin {
  to { transform: rotate(360deg); }
}
@keyframes qelvo-breathe {
  0%, 100% { transform: scale(1); opacity: 0.9; }
  50% { transform: scale(1.08); opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
  .qelvo-preloader-ring, .qelvo-preloader-ring svg { animation: none; }
  .qelvo-preloader-arc { opacity: 0.8; }
}
CSSEOF

echo "=== Writing preloader.js ==="
cat > public/js/preloader.js << 'JSEOF'
(function () {
  var el = document.getElementById('qelvoPreloader');
  if (!el) return;

  var shown = false;
  var minVisibleUntil = 0;
  var revealTimer = null;

  function reveal() {
    if (shown) return;
    shown = true;
    el.classList.add('is-visible');
    minVisibleUntil = Date.now() + 300;
  }

  function hide() {
    if (!shown) return;
    var wait = Math.max(0, minVisibleUntil - Date.now());
    setTimeout(function () {
      el.classList.remove('is-visible');
      shown = false;
    }, wait);
  }

  revealTimer = setTimeout(reveal, 250);

  window.addEventListener('load', function () {
    clearTimeout(revealTimer);
    hide();
  });

  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      clearTimeout(revealTimer);
      el.classList.remove('is-visible');
      shown = false;
    }
  });

  function shouldSkip(a) {
    if (!a || !a.getAttribute('href')) return true;
    var href = a.getAttribute('href');
    if (a.target === '_blank') return true;
    if (a.hasAttribute('download')) return true;
    if (href.startsWith('#')) return true;
    if (href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return true;
    if (a.hasAttribute('data-no-preloader')) return true;
    if (a.origin && a.origin !== window.location.origin) return true;
    return false;
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest('a');
    if (shouldSkip(a)) return;
    setTimeout(reveal, 250);
  }, true);

  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.hasAttribute('data-no-preloader') || f.target === '_blank') return;
    setTimeout(reveal, 250);
  }, true);
})();
JSEOF

echo "=== Writing preloader partial ==="
cat > resources/views/partials/preloader.blade.php << 'VIEWEOF'
<div id="qelvoPreloader" class="qelvo-preloader" aria-hidden="true" style="opacity:0;visibility:hidden;">
    <div class="qelvo-preloader-ring">
        <svg viewBox="0 0 48 48">
            <circle class="qelvo-preloader-track" cx="24" cy="24" r="20"></circle>
            <circle class="qelvo-preloader-arc" cx="24" cy="24" r="20"></circle>
        </svg>
    </div>
</div>
VIEWEOF

echo "=== Applying edits to the 4 layouts ==="
cat > maintenance_preloader.php << 'PHPEOF'
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

$cssTag = "    <link rel=\"stylesheet\" href=\"{{ asset('css/preloader.css') }}?v={{ time() }}\">\n";
$jsTag  = "    <script src=\"{{ asset('js/preloader.js') }}?v={{ time() }}\"></script>\n";

// ---- app-admin.blade.php ----
edit('resources/views/layouts/app-admin.blade.php',
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/split-view.css') }}?v={{ time() }}\">\n",
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/split-view.css') }}?v={{ time() }}\">\n" . $cssTag,
    'css link');

edit('resources/views/layouts/app-admin.blade.php',
    "    @include('partials.nav-admin', ['activeRoute' => \$routeName])",
    "    @include('partials.preloader')\n    @include('partials.nav-admin', ['activeRoute' => \$routeName])",
    'partial include');

edit('resources/views/layouts/app-admin.blade.php',
    "    <script src=\"{{ asset('js/lazy-mode.js') }}?v={{ time() }}\"></script>\n    @yield('scripts')",
    "    <script src=\"{{ asset('js/lazy-mode.js') }}?v={{ time() }}\"></script>\n" . $jsTag . "    @yield('scripts')",
    'js tag');

// ---- app-authenticated.blade.php ----
edit('resources/views/layouts/app-authenticated.blade.php',
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/levi.css') }}?v={{ time() }}\">",
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/levi.css') }}?v={{ time() }}\">\n" . $cssTag,
    'css link');

edit('resources/views/layouts/app-authenticated.blade.php',
    '<div class="app-shell-flex">',
    "@include('partials.preloader')\n<div class=\"app-shell-flex\">",
    'partial include');

edit('resources/views/layouts/app-authenticated.blade.php',
    "@yield('scripts')",
    $jsTag . "    @yield('scripts')",
    'js tag');

// ---- auth.blade.php ----
edit('resources/views/layouts/auth.blade.php',
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/app-v2.css') }}?v={{ time() }}\">",
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/app-v2.css') }}?v={{ time() }}\">\n" . $cssTag,
    'css link');

edit('resources/views/layouts/auth.blade.php',
    '<div class="auth-stage min-h-screen flex items-center justify-center p-4">',
    "@include('partials.preloader')\n    <div class=\"auth-stage min-h-screen flex items-center justify-center p-4\">",
    'partial include');

edit('resources/views/layouts/auth.blade.php',
    "@yield('scripts')",
    $jsTag . "    @yield('scripts')",
    'js tag');

// ---- landing.blade.php ----
edit('resources/views/layouts/landing.blade.php',
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/site-nav.css') }}?v={{ time() }}\">",
    "    <link rel=\"stylesheet\" href=\"{{ asset('css/site-nav.css') }}?v={{ time() }}\">\n" . $cssTag,
    'css link');

edit('resources/views/layouts/landing.blade.php',
    "    @include('partials.site-nav')",
    "    @include('partials.preloader')\n    @include('partials.site-nav')",
    'partial include');

edit('resources/views/layouts/landing.blade.php',
    "@yield('scripts')",
    $jsTag . "    @yield('scripts')",
    'js tag');

echo "\nDone.\n";
PHPEOF

php maintenance_preloader.php
rm maintenance_preloader.php

echo ""
echo "=== Clearing caches ==="
php artisan view:clear
php artisan cache:clear
