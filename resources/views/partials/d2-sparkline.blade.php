@php
    $sparkColor = $color ?? '#8b5cf6';
    $sparkSeed = max(1, (int) ($seed ?? 1));
    $sparkPts = [];
    for ($i = 0; $i <= 8; $i++) {
        $n = sin($i * 1.1 + $sparkSeed) * 8 + cos($i * 0.6 + $sparkSeed) * 5;
        $sparkPts[] = round(20 - $n, 1);
    }
    $sparkPath = '';
    foreach ($sparkPts as $i => $y) {
        $x = $i * 10;
        $sparkPath .= ($i === 0 ? "M{$x},{$y} " : "L{$x},{$y} ");
    }
@endphp
<svg class="d2-sparkline" viewBox="0 0 80 28" preserveAspectRatio="none">
    <path d="{{ $sparkPath }}" fill="none" stroke="{{ $sparkColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
