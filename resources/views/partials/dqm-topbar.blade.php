@php
    $tbH = 68;
    $tbScale = round(.18 * $tbH / .48, 1);
    $tbLo = (int) round(255 * .02);
    $tbHi = (int) round(255 * .98);
    $tbMap = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'><stop offset='0' stop-color='rgb(0,128,0)'/><stop offset='.3' stop-color='rgb(0,{$tbLo},0)'/><stop offset='.7' stop-color='rgb(0,{$tbHi},0)'/><stop offset='1' stop-color='rgb(0,128,0)'/></linearGradient></defs><rect width='100' height='100' fill='rgb(128,0,0)'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <filter id="tbLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
        <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($tbMap) }}" preserveAspectRatio="none" result="m"/>
        <feDisplacementMap in="SourceGraphic" in2="m" scale="{{ $tbScale }}" xChannelSelector="R" yChannelSelector="G"/>
    </filter>
</svg>
@php
    $dqmTbName = auth()->user()?->name ?? 'Guest';
    $dqmTbInitial = strtoupper(substr($dqmTbName, 0, 1));
    $dqmTbHour = now()->hour;
    $dqmTbGreeting = $dqmTbHour < 12 ? 'Good morning' : ($dqmTbHour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<button type="button" class="dqm-topbar-tab" id="dqmTopbarTab" aria-label="Show top bar">
    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
</button>
<div class="dqm-topbar dqm-topbar-fixed">
    <a href="{{ route('profile.index') }}" class="dqm-topbar-user">
        <div class="dqm-topbar-avatar">
            @if($navAvatar ?? null)<img src="{{ $navAvatar }}" alt="">@else<span>{{ $navInitial ?? $dqmTbInitial }}</span>@endif
        </div>
        <div>
            <p class="dqm-topbar-greeting">{{ $dqmTbGreeting }}</p>
            <p class="dqm-topbar-name">{{ explode(' ', $dqmTbName)[0] }}</p>
        </div>
    </a>
    <div class="dqm-topbar-actions">
        <a href="{{ Route::has('investment.index') ? route('investment.index') : '#' }}" class="dqm-icon-btn" title="Invest">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
        </a>
        <a href="{{ Route::has('referrals.index') ? route('referrals.index') : '#' }}" class="dqm-icon-btn" title="Invite friends"><i data-lucide="user-plus" class="w-4 h-4"></i></a>
        <a href="{{ Route::has('qcoin.index') ? route('qcoin.index') : '#' }}" class="dqm-topbar-wallet">
            <span class="dqm-topbar-wallet-dot"></span>
            <span>{{ number_format($navQCoinBalance ?? 0) }} Q</span>
        </a>
        <a href="{{ Route::has('chat.index') ? route('chat.index') : '#' }}" class="dqm-icon-btn" title="Chat">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            @if(($navUnreadChats ?? 0) > 0)<span class="dqm-badge">{{ $navUnreadChats }}</span>@endif
        </a>
<button type="button" class="dqm-icon-btn" data-theme-btn title="Light or dark mode"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
    </div>
</div>

<script>
(function () {
    var bar = document.querySelector('.dqm-topbar-fixed');
    if (!bar) return;
    var SEEN = sessionStorage.getItem('dqmTopbarSeen');
    sessionStorage.setItem('dqmTopbarSeen', '1');
    var HIDE_DELAY = 15000, GAP = 24, tab = document.getElementById('dqmTopbarTab');
    var hideTimer = null, lastReveal = 0, dragged = false;
    var active = false, startY = 0, baseY = 0, curY = 0, h = 0;

    function isHidden() { return bar.classList.contains('dqm-topbar-hidden'); }
    function hide() {
        clearTimeout(hideTimer);
        bar.classList.add('dqm-topbar-hidden'); if (tab) tab.classList.add('visible');
        document.body.classList.remove('dqm-topbar-open');
    }
    function reveal() {
        lastReveal = Date.now();
        bar.classList.remove('dqm-topbar-hidden'); if (tab) tab.classList.remove('visible');
        document.body.classList.add('dqm-topbar-open');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(hide, HIDE_DELAY);
    }

    document.body.classList.add('dqm-topbar-open');
    if (SEEN) { hide(); } else { hideTimer = setTimeout(hide, HIDE_DELAY); }

    // scrolling hides it
    window.addEventListener('scroll', function () {
        if (Date.now() - lastReveal < 800 || isHidden()) return;
        hide();
    }, true);

    // drag down to pull out, push up to hide
    bar.addEventListener('pointerdown', function (e) {
        active = true; dragged = false; startY = e.clientY;
        h = bar.offsetHeight;
        baseY = isHidden() ? -(h + GAP) : 0;
        curY = baseY;
    });
    window.addEventListener('pointermove', function (e) {
        if (!active) return;
        var dy = e.clientY - startY;
        if (!dragged && Math.abs(dy) < 8) return;
        dragged = true;
        clearTimeout(hideTimer);
        curY = Math.max(-(h + GAP), Math.min(0, baseY + dy));
        bar.style.setProperty('transition', 'none');
        bar.style.setProperty('transform', 'translateY(' + curY + 'px)');
    });
    window.addEventListener('pointerup', function (e) {
        if (!active) return;
        active = false;
        if (dragged) {
            bar.style.removeProperty('transition');
            bar.style.removeProperty('transform');
            if (curY > -h * 0.5) { reveal(); } else { hide(); }
            setTimeout(function () { dragged = false; }, 50);
            return;
        }
        // plain tap: hidden -> open, open -> hide (taps on links/buttons still work)
        if (isHidden()) { reveal(); }
        else if (!e.target.closest('a, button')) { hide(); }
    });
    // don't follow a link if the finger was dragging
    bar.addEventListener('click', function (e) {
        if (dragged) { e.preventDefault(); e.stopPropagation(); }
    }, true);

    if (tab) tab.addEventListener('click', reveal);
    if (window.lucide) lucide.createIcons();
})();
</script>
