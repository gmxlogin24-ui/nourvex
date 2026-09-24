set -e

mkdir -p public/images

cat > public/images/auth-bg-abstract.svg << 'SVGEOF'
<svg viewBox="0 0 800 1200" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg1" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0b0f1a"/>
      <stop offset="100%" stop-color="#131a2b"/>
    </linearGradient>
    <linearGradient id="wave1" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#38D9E8" stop-opacity="0.9"/>
      <stop offset="100%" stop-color="#1e6ee8" stop-opacity="0.2"/>
    </linearGradient>
    <linearGradient id="wave2" x1="1" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#7c6cf0" stop-opacity="0.85"/>
      <stop offset="100%" stop-color="#38D9E8" stop-opacity="0.1"/>
    </linearGradient>
    <linearGradient id="wave3" x1="0" y1="1" x2="1" y2="0">
      <stop offset="0%" stop-color="#38D9E8" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#0b0f1a" stop-opacity="0"/>
    </linearGradient>
    <filter id="soften"><feGaussianBlur stdDeviation="18"/></filter>
  </defs>

  <rect width="800" height="1200" fill="url(#bg1)"/>

  <path d="M -100,300 C 150,180 300,420 550,260 C 750,140 900,320 950,180 L 950,-100 L -100,-100 Z"
        fill="url(#wave1)" filter="url(#soften)" opacity="0.55"/>

  <path d="M -100,650 C 120,520 380,760 600,580 C 800,430 900,600 980,500 L 980,900 C 700,760 500,980 250,820 C 80,710 -60,830 -100,760 Z"
        fill="url(#wave2)" filter="url(#soften)" opacity="0.5"/>

  <path d="M -100,1050 C 180,900 420,1150 700,980 C 850,890 950,1000 980,950 L 980,1300 L -100,1300 Z"
        fill="url(#wave3)" filter="url(#soften)" opacity="0.6"/>

  <circle cx="620" cy="220" r="3" fill="#ffffff" opacity="0.5"/>
  <circle cx="180" cy="460" r="2.5" fill="#38D9E8" opacity="0.6"/>
  <circle cx="720" cy="700" r="2" fill="#ffffff" opacity="0.4"/>
  <circle cx="340" cy="900" r="3" fill="#7c6cf0" opacity="0.5"/>
  <circle cx="80" cy="1100" r="2.5" fill="#38D9E8" opacity="0.55"/>
  <circle cx="600" cy="1050" r="2" fill="#ffffff" opacity="0.4"/>
</svg>
SVGEOF

cat > maintenance_abstract_bg.php << 'PHPEOF'
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
    min-height: 100vh;
    background-image: url('/images/auth-bg-abstract.svg');
    background-size: cover;
    background-position: center;
}
EOD;

edit('public/css/auth-split.css', $old, $new, 'swap glow blobs for abstract SVG background image');

$old2 = <<<'EOD'
.auth-form-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    background: color-mix(in srgb, var(--color-card, #ffffff) 55%, transparent);
EOD;

$new2 = <<<'EOD'
.auth-form-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    background: color-mix(in srgb, #ffffff 22%, transparent);
EOD;

edit('public/css/auth-split.css', $old2, $new2, 'lighten card fill so the image shows through the blur');

echo "\nDone.\n";
PHPEOF

php maintenance_abstract_bg.php
rm maintenance_abstract_bg.php

php artisan view:clear
php artisan cache:clear
