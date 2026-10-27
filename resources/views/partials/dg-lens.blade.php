@php
  $dgR = ''; $dgG = '';
  for ($i = 0; $i <= 16; $i++) {
    $t = $i / 16; $x = 2 * $t - 1;
    $v = (int) round(255 * max(0, min(1, .5 + 1.299 * ($x ** 3 - $x))));
    $o = round($t * 100, 2);
    $dgR .= "<stop offset='{$o}%' stop-color='rgb({$v},0,0)'/>";
    $dgG .= "<stop offset='{$o}%' stop-color='rgb(0,{$v},0)'/>";
  }
  $dgMap = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='r' x1='0' x2='1' y1='0' y2='0'>{$dgR}</linearGradient><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'>{$dgG}</linearGradient></defs><rect width='100' height='100' fill='#000'/><rect width='100' height='100' fill='url(#r)' style='mix-blend-mode:screen'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <filter id="dgLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
    <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($dgMap) }}" preserveAspectRatio="none" result="m"/>
    <feDisplacementMap in="SourceGraphic" in2="m" scale="18" xChannelSelector="R" yChannelSelector="G"/>
  </filter>
</svg>
