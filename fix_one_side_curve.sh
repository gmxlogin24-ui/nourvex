set -e

cat > maintenance_curve_fix.php << 'PHPEOF'
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
EOD;
$new = <<<'EOD'
@media (max-width: 900px) {
    .auth-form-panel {
        margin-top: -40px;
        border-radius: 60px 0 0 0;
        box-shadow: 0 -14px 34px rgba(20,30,60,0.12);
        padding: 36px 20px 40px;
        min-height: calc(100vh - 180px);
        position: relative;
        z-index: 3;
    }
}
EOD;
edit('public/css/auth-split.css', $old, $new, 'single-corner curve instead of both');

echo "\nDone.\n";
PHPEOF

php maintenance_curve_fix.php
rm maintenance_curve_fix.php

php artisan view:clear
php artisan cache:clear
