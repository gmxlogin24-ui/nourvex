@php
    $lgdH = 64;
    $lgdK = .18;
    $lgdScale = round($lgdK * $lgdH / .48, 1);
    $lgdLo = (int) round(255 * .02);
    $lgdHi = (int) round(255 * .98);
    $lgdMap = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'><stop offset='0' stop-color='rgb(0,128,0)'/><stop offset='.3' stop-color='rgb(0,{$lgdLo},0)'/><stop offset='.7' stop-color='rgb(0,{$lgdHi},0)'/><stop offset='1' stop-color='rgb(0,128,0)'/></linearGradient></defs><rect width='100' height='100' fill='rgb(128,0,0)'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <filter id="authLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
        <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($lgdMap) }}" preserveAspectRatio="none" result="m"/>
        <feDisplacementMap in="SourceGraphic" in2="m" scale="{{ $lgdScale }}" xChannelSelector="R" yChannelSelector="G"/>
    </filter>
</svg>
