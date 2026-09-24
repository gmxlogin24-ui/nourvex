set -e

cat > maintenance_final_fixes.php << 'PHPEOF'
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

// ---- Add logout button to nav-rail.blade.php ----
$old = <<<'EOD'
    <button type="button" class="d2-rail-link d2-rail-theme-btn" title="Theme" style="margin-top:auto;"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
</aside>
EOD;
$new = <<<'EOD'
    <button type="button" class="d2-rail-link d2-rail-theme-btn" title="Theme" style="margin-top:auto;"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="d2-rail-link" title="Log Out"><i data-lucide="log-out" class="w-4 h-4"></i></button>
    </form>
</aside>
EOD;
edit('resources/views/partials/nav-rail.blade.php', $old, $new, 'add logout button to rail sidebar');

// ---- Fix the actual auth-split-shell height rule ----
$old2 = <<<'EOD'
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
@media (min-width: 901px) {
    .auth-split-shell { padding: 40px; }
}
EOD;
$new2 = <<<'EOD'
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
@media (min-width: 901px) {
    .auth-split-shell {
        height: 100vh;
        max-height: 100vh;
        padding: 40px;
        overflow: hidden;
    }
}
EOD;
edit('public/css/auth-split.css', $old2, $new2, 'lock shell to viewport height on desktop');

echo "\nDone.\n";
PHPEOF

php maintenance_final_fixes.php
rm maintenance_final_fixes.php

php artisan view:clear
php artisan cache:clear
