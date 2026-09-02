@auth
<div class="d2-topbar-icons d2-nav-icons-global">
    <a href="{{ Route::has('chat.index') ? route('chat.index') : '#' }}" class="d2-topbar-icon-btn" title="Chat">
        <i data-lucide="message-circle" class="w-5 h-5"></i>
        @if(($navUnreadChats ?? 0) > 0)<span class="d2-topbar-badge">{{ $navUnreadChats }}</span>@endif
    </a>
    <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="d2-topbar-icon-btn" title="Notifications">
        <i data-lucide="bell" class="w-5 h-5"></i>
        @if(($navUnreadNotifications ?? 0) > 0)<span class="d2-topbar-badge">{{ $navUnreadNotifications }}</span>@endif
    </a>
    <a href="{{ Route::has('qcoin.index') ? route('qcoin.index') : '#' }}" class="d2-topbar-wallet">
        <i data-lucide="gem" class="w-4 h-4"></i>
        <span>{{ number_format($navQCoinBalance ?? 0) }} Q</span>
    </a>
    <a href="{{ route('profile.index') }}" class="d2-topbar-avatar">
        @if($navAvatar)<img src="{{ $navAvatar }}" alt="">@else<span>{{ $navInitial }}</span>@endif
    </a>
</div>
@endauth
