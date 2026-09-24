set -e

cat > maintenance_logo_preloader.php << 'PHPEOF'
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

// ---- Replace preloader partial markup with the orbiting-capsule mark ----
$partialOld = <<<'EOD'
<div id="qelvoPreloader" class="qelvo-preloader" aria-hidden="true" style="opacity:0;visibility:hidden;">
    <div class="qelvo-preloader-ring">
        <svg viewBox="0 0 48 48">
            <circle class="qelvo-preloader-track" cx="24" cy="24" r="20"></circle>
            <circle class="qelvo-preloader-arc" cx="24" cy="24" r="20"></circle>
        </svg>
    </div>
</div>
EOD;

$partialNew = <<<'EOD'
<div id="qelvoPreloader" class="qelvo-preloader" aria-hidden="true" style="opacity:0;visibility:hidden;">
    <div class="qelvo-preloader-mark">
        <svg viewBox="0 0 100 100">
            <defs>
                <linearGradient id="qelvoCapGrad" x1="0" y1="1" x2="0" y2="0">
                    <stop offset="0%" stop-color="var(--color-accent, #38D9E8)" stop-opacity="0.1"></stop>
                    <stop offset="100%" stop-color="var(--color-accent, #38D9E8)" stop-opacity="1"></stop>
                </linearGradient>
            </defs>
            <g class="qelvo-mark-orbit">
                <g transform="rotate(0 50 50)">
                    <rect x="46" y="12" width="8" height="36" rx="4" fill="url(#qelvoCapGrad)"></rect>
                    <circle cx="50" cy="12" r="6.5" fill="var(--color-accent, #38D9E8)"></circle>
                </g>
                <g transform="rotate(120 50 50)">
                    <rect x="46" y="12" width="8" height="36" rx="4" fill="url(#qelvoCapGrad)"></rect>
                    <circle cx="50" cy="12" r="6.5" fill="var(--color-accent, #38D9E8)"></circle>
                </g>
                <g transform="rotate(240 50 50)">
                    <rect x="46" y="12" width="8" height="36" rx="4" fill="url(#qelvoCapGrad)"></rect>
                    <circle cx="50" cy="12" r="6.5" fill="var(--color-accent, #38D9E8)"></circle>
                </g>
            </g>
        </svg>
    </div>
</div>
EOD;

edit('resources/views/partials/preloader.blade.php', $partialOld, $partialNew, 'orbiting capsule mark markup');

// ---- Replace old ring CSS rules with the new mark's animation rules ----
$cssOld = <<<'EOD'
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
EOD;

$cssNew = <<<'EOD'
.qelvo-preloader-mark {
  width: 64px;
  height: 64px;
  animation: qelvo-breathe 2.2s cubic-bezier(0.45, 0, 0.55, 1) infinite;
  filter: drop-shadow(0 6px 18px color-mix(in srgb, var(--color-accent, #38D9E8) 45%, transparent));
}
.qelvo-preloader-mark svg {
  width: 100%;
  height: 100%;
  overflow: visible;
}
.qelvo-mark-orbit {
  transform-origin: 50px 50px;
  animation: qelvo-orbit-spin 2.4s cubic-bezier(0.65, 0, 0.35, 1) infinite;
}
@keyframes qelvo-orbit-spin {
  to { transform: rotate(360deg); }
}
@keyframes qelvo-breathe {
  0%, 100% { transform: scale(1); opacity: 0.92; }
  50% { transform: scale(1.1); opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
  .qelvo-preloader-mark, .qelvo-mark-orbit { animation: none; }
}
EOD;

edit('public/css/preloader.css', $cssOld, $cssNew, 'orbiting capsule animation styles');

echo "\nDone.\n";
PHPEOF

php maintenance_logo_preloader.php
rm maintenance_logo_preloader.php

php artisan view:clear
php artisan cache:clear
