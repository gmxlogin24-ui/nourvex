set -e

cat > maintenance_glass_fix.php << 'PHPEOF'
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
EOD;

$new = <<<'EOD'
.auth-form-panel {
    flex: 1 1 50%;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    background: var(--color-bg, #f4f6fb);
    min-height: 100vh;
}
.auth-form-panel::before,
.auth-form-panel::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    z-index: 0;
    pointer-events: none;
}
.auth-form-panel::before {
    width: 320px;
    height: 320px;
    top: -80px;
    right: -60px;
    background: color-mix(in srgb, var(--color-accent, #38D9E8) 45%, transparent);
    opacity: 0.5;
}
.auth-form-panel::after {
    width: 260px;
    height: 260px;
    bottom: -60px;
    left: -60px;
    background: color-mix(in srgb, var(--color-accent, #38D9E8) 30%, #7c6cf0);
    opacity: 0.35;
}
.auth-form-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    background: color-mix(in srgb, var(--color-card, #ffffff) 55%, transparent);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 28px;
    box-shadow:
        0 1px 0 rgba(255,255,255,0.6) inset,
        0 0 0 1px color-mix(in srgb, var(--color-accent, #38D9E8) 12%, transparent),
        0 20px 50px rgba(20,30,60,0.16);
    padding: 36px 30px;
}
EOD;

edit('public/css/auth-split.css', $old, $new, 'visible glass card with glow blobs');

echo "\nDone.\n";
PHPEOF

php maintenance_glass_fix.php
rm maintenance_glass_fix.php

php artisan view:clear
php artisan cache:clear
