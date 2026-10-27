@php
    $cartCount = count(session('cart', []));
    $gvUrl = Route::has('golfvex.index') ? route('golfvex.index') : (Route::has('golfvex') ? route('golfvex') : url('/golfvex'));
    $lgdH = 64;
    $lgdK = .18;
    $lgdScale = round($lgdK * $lgdH / .48, 1);
    $lgdLo = (int) round(255 * .02);
    $lgdHi = (int) round(255 * .98);
    $lgdMap = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'><stop offset='0' stop-color='rgb(0,128,0)'/><stop offset='.3' stop-color='rgb(0,{$lgdLo},0)'/><stop offset='.7' stop-color='rgb(0,{$lgdHi},0)'/><stop offset='1' stop-color='rgb(0,128,0)'/></linearGradient></defs><rect width='100' height='100' fill='rgb(128,0,0)'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <filter id="lgdLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
        <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($lgdMap) }}" preserveAspectRatio="none" result="m"/>
        <feDisplacementMap in="SourceGraphic" in2="m" scale="{{ $lgdScale }}" xChannelSelector="R" yChannelSelector="G"/>
    </filter>
</svg>
<nav class="lgd" id="mnavBar">
    <a href="{{ route('dashboard') }}" class="lgd-item {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
        <i class="bi bi-house-door-fill lgd-icon"></i>
        <span class="lgd-label">Home</span>
    </a>
    <a href="{{ $gvUrl }}" class="lgd-item {{ (($active ?? '') == 'golfvex' || request()->is('golfvex*')) ? 'active' : '' }}">
        <i class="bi bi-flag-fill lgd-icon"></i>
        <span class="lgd-label">NaGolf</span>
    </a>
    <a href="{{ route('profile.index') }}" class="lgd-item {{ ($active ?? '') == 'profile' ? 'active' : '' }}">
        <i class="bi bi-person-circle lgd-icon"></i>
        <span class="lgd-label">Profile</span>
    </a>
</nav>
