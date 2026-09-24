set -e

cat > maintenance_hero_glass.php << 'PHPEOF'
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
.profile-hero-card {
    position: relative;
    border-radius: 28px;
    padding: 32px 24px 44px;
    overflow: hidden;
    background: linear-gradient(160deg, var(--user-accent, #38D9E8), color-mix(in srgb, var(--user-accent, #38D9E8) 40%, #1a1a2e));
    color: #fff;
    text-align: center;
    box-shadow: 0 16px 34px rgba(20,30,60,0.22);
}
.profile-hero-top { position: relative; z-index: 1; }
EOD;

$new = <<<'EOD'
.profile-hero-card {
    position: relative;
    border-radius: 28px;
    padding: 32px 24px 44px;
    overflow: hidden;
    background-image:
        linear-gradient(180deg, rgba(10,14,24,0.05) 0%, rgba(10,14,24,0.55) 100%),
        var(--hero-photo, none),
        linear-gradient(160deg, var(--user-accent, #38D9E8), color-mix(in srgb, var(--user-accent, #38D9E8) 40%, #1a1a2e));
    background-size: cover, cover, cover;
    background-position: center, center, center;
    color: #fff;
    text-align: center;
    box-shadow: 0 16px 34px rgba(20,30,60,0.22);
}
/* Fluted / reeded glass overlay — tall thin stripes near-zero opacity,
   blended over the photo to create the rippled glass-panel look. */
.profile-hero-card::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background-image: repeating-linear-gradient(
        90deg,
        rgba(255,255,255,0.24) 0px,
        rgba(255,255,255,0.03) 3px,
        rgba(0,0,0,0.12) 6px,
        rgba(255,255,255,0.08) 9px,
        rgba(0,0,0,0.05) 12px,
        rgba(255,255,255,0.24) 15px
    );
    mix-blend-mode: overlay;
    opacity: 0.85;
}
.profile-hero-card::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background: radial-gradient(circle at 30% 15%, rgba(255,255,255,0.18), transparent 55%);
    mix-blend-mode: soft-light;
}
.profile-hero-top { position: relative; z-index: 2; }
EOD;

edit('public/css/profile-glass.css', $old, $new, 'hero card photo + fluted glass overlay');

$old2 = <<<'EOD'
.profile-hero-stats {
    position: relative;
    z-index: 1;
EOD;
$new2 = <<<'EOD'
.profile-hero-stats {
    position: relative;
    z-index: 2;
EOD;
edit('public/css/profile-glass.css', $old2, $new2, 'stats z-index bump');

$old3 = <<<'EOD'
.profile-hero-curve {
    position: absolute;
    left: 0; right: 0; bottom: -1px;
    height: 40px;
    line-height: 0;
}
EOD;
$new3 = <<<'EOD'
.profile-hero-curve {
    position: absolute;
    left: 0; right: 0; bottom: -1px;
    height: 40px;
    line-height: 0;
    z-index: 2;
}
EOD;
edit('public/css/profile-glass.css', $old3, $new3, 'curve z-index bump');

echo "\nDone.\n";
PHPEOF

php maintenance_hero_glass.php
rm maintenance_hero_glass.php

php artisan view:clear
php artisan cache:clear
