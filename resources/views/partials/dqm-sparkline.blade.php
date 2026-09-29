@php
    $dqmSparkColor = $color ?? '#7c5cf0';
    $dqmSparkSeed = max(1, (int) ($seed ?? 1));
    $dqmSparkPts = [];
    for ($i = 0; $i <= 8; $i++) {
        $n = sin($i * 1.1 + $dqmSparkSeed) * 8 + cos($i * 0.6 + $dqmSparkSeed) * 5;
        $dqmSparkPts[] = round(20 - $n, 1);
    }
    $dqmSparkPath = '';
    foreach ($dqmSparkPts as $i => $y) {
        $x = $i * 10;
        $dqmSparkPath .= ($i === 0 ? "M{$x},{$y} " : "L{$x},{$y} ");
    }
@endphp
<svg class="dqm-sparkline" viewBox="0 0 80 28" preserveAspectRatio="none">
    <path d="{{ $dqmSparkPath }}" fill="none" stroke="{{ $dqmSparkColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
