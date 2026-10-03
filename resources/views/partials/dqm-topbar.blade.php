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
        <a href="{{ Route::has('chat.index') ? route('chat.index') : '#' }}" class="dqm-icon-btn" title="Chat">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            @if(($navUnreadChats ?? 0) > 0)<span class="dqm-badge">{{ $navUnreadChats }}</span>@endif
        </a>
        <a href="{{ Route::has('qcoin.index') ? route('qcoin.index') : '#' }}" class="dqm-topbar-wallet">
            <span class="dqm-topbar-wallet-dot"></span>
            <span>{{ number_format($navQCoinBalance ?? 0) }} Q</span>
        </a>
        <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="dqm-icon-btn" title="Notifications">
            <i data-lucide="bell" class="w-4 h-4"></i>
            @if(($navUnreadNotifications ?? 0) > 0)<span class="dqm-badge">{{ $navUnreadNotifications }}</span>@endif
        </a>
    </div>
</div>

<script>
(function () {
    var bar = document.querySelector('.dqm-topbar-fixed');
    if (!bar) return;
    var SEEN = sessionStorage.getItem('dqmTopbarSeen');
    sessionStorage.setItem('dqmTopbarSeen', '1');
    var HIDE_DELAY = 15000, PEEK = 0.95;
    var hideTimer = null, lastReveal = 0, dragged = false;
    var active = false, startY = 0, baseY = 0, curY = 0, h = 0;

    function isHidden() { return bar.classList.contains('dqm-topbar-hidden'); }
    function hide() {
        clearTimeout(hideTimer);
        bar.classList.add('dqm-topbar-hidden');
        document.body.classList.remove('dqm-topbar-open');
    }
    function reveal() {
        lastReveal = Date.now();
        bar.classList.remove('dqm-topbar-hidden');
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
        baseY = isHidden() ? -h * PEEK : 0;
        curY = baseY;
    });
    window.addEventListener('pointermove', function (e) {
        if (!active) return;
        var dy = e.clientY - startY;
        if (!dragged && Math.abs(dy) < 8) return;
        dragged = true;
        clearTimeout(hideTimer);
        curY = Math.max(-h * PEEK, Math.min(0, baseY + dy));
        bar.style.setProperty('transition', 'none', 'important');
        bar.style.setProperty('transform', 'translateY(' + curY + 'px)', 'important');
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

    if (window.lucide) lucide.createIcons();
})();
</script>
