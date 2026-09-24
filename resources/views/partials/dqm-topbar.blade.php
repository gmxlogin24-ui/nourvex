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
    var tab = document.getElementById('dqmTopbarTab');
    if (!bar || !tab) return;
    var SEEN_KEY = 'dqmTopbarSeen';
    var HIDE_DELAY = sessionStorage.getItem(SEEN_KEY) ? 4000 : 8000;
    sessionStorage.setItem(SEEN_KEY, '1');
    var hideTimer = null;

    function hide() {
        bar.classList.add('dqm-topbar-hidden');
        tab.classList.add('visible');
        document.body.classList.remove('dqm-topbar-open');
    }
    function scheduleHide() {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(hide, HIDE_DELAY);
    }
    function reveal() {
        bar.classList.remove('dqm-topbar-hidden');
        tab.classList.remove('visible');
        document.body.classList.add('dqm-topbar-open');
        scheduleHide();
    }

    document.body.classList.add('dqm-topbar-open');
    scheduleHide();

    tab.addEventListener('click', reveal);
    bar.addEventListener('pointerenter', function () { clearTimeout(hideTimer); });
    bar.addEventListener('pointerleave', function () { scheduleHide(); });

    if (window.lucide) lucide.createIcons();
})();
</script>
