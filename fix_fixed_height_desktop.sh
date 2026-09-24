set -e

cat > maintenance_fixed_height.php << 'PHPEOF'
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
.auth-split-shell {
    min-height: 100vh;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    background: #eef1f6;
}
@media (min-width: 901px) {
    .auth-split-shell { padding: 40px; }
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
@media (min-width: 901px) {
    .auth-split-shell {
        height: 100vh;
        max-height: 100vh;
        padding: 40px;
        overflow: hidden;
    }
}
EOD;
edit('public/css/auth-split.css', $old, $new, 'lock shell to viewport height on desktop');

$old2 = <<<'EOD'
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
EOD;
$new2 = <<<'EOD'
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
        height: 100%;
        max-height: 100%;
        overflow-y: auto;
        align-items: flex-start;
        padding-top: 48px;
        padding-bottom: 48px;
    }
    .auth-form-panel::-webkit-scrollbar { width: 6px; }
    .auth-form-panel::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 999px; }
}
EOD;
edit('public/css/auth-split.css', $old2, $new2, 'form panel internal scroll, fixed to viewport height');

$old3 = <<<'EOD'
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
EOD;
$new3 = <<<'EOD'
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
        height: 100%;
        max-height: 100%;
        border-radius: 32px;
        transform: rotate(-2.5deg);
        box-shadow: 0 30px 70px rgba(10,15,30,0.35);
        z-index: 2;
    }
}
EOD;
edit('public/css/auth-split.css', $old3, $new3, 'visual panel locked to viewport height too');

echo "\nDone.\n";
PHPEOF

php maintenance_fixed_height.php
rm maintenance_fixed_height.php

php artisan view:clear
php artisan cache:clear
