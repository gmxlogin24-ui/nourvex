@extends('layouts.app-authenticated')

@section('content')
@if((auth()->user()->theme_style ?? 'glass') === 'plain')
@include('notifications.partials.notifications-minimal')
@else
<main class="max-w-2xl mx-auto p-6 notif-page">
    <div class="notif-header">
        <div>
            <h1 class="notif-title"><i data-lucide="bell" class="w-5 h-5"></i> Notifications</h1>
            @if($unreadCount > 0)
                <p class="notif-subtitle">{{ $unreadCount }} unread</p>
            @else
                <p class="notif-subtitle">You're all caught up</p>
            @endif
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="notif-mark-all">Mark all as read</button>
        </form>
        @endif
    </div>

    <div class="notif-tabs">
        <a href="{{ route('notifications.index') }}" class="notif-tab {{ $filter === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="notif-tab {{ $filter === 'unread' ? 'active' : '' }}">Unread</a>
    </div>

    <div class="notif-list">
        @forelse($notifications as $n)
            @php
                $type = class_basename($n->type);
                $isUnread = is_null($n->read_at);
                $icon = match(true) {
                    str_contains($type, 'Gig') => 'briefcase',
                    str_contains($type, 'Listing') => 'shopping-bag',
                    str_contains($type, 'Dispute') => 'alert-triangle',
                    default => 'bell',
                };
                $link = null;
                if (isset($n->data['gig_id']) && \Illuminate\Support\Facades\Route::has('gigs.show')) {
                    $link = route('gigs.show', $n->data['gig_id']);
                } elseif (isset($n->data['listing_id']) && \Illuminate\Support\Facades\Route::has('marketplace.show')) {
                    $link = route('marketplace.show', $n->data['listing_id']);
                }
            @endphp
            <div class="notif-card {{ $isUnread ? 'is-unread' : '' }}">
                <div class="notif-icon"><i data-lucide="{{ $icon }}" class="w-4 h-4"></i></div>
                <a href="{{ $link ?? '#' }}" class="notif-body" @if($isUnread) onclick="markNotifRead(event, '{{ $n->id }}', {{ $link ? 'true' : 'false' }})" @endif>
                    <p class="notif-message">{{ $n->data['message'] ?? $type }}</p>
                    <p class="notif-time">{{ $n->created_at->diffForHumans() }}</p>
                </a>
                <div class="notif-actions">
                    @if($isUnread)<span class="notif-dot"></span>@endif
                    <form method="POST" action="{{ route('notifications.destroy', $n->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="notif-close" title="Dismiss"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="notif-empty">
                <i data-lucide="bell-off" class="w-8 h-8"></i>
                <p>Nothing here yet</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="notif-pagination">{{ $notifications->links() }}</div>
    @endif
</main>
@endif

<script>
function markNotifRead(e, id, hasLink) {
    if (!hasLink) e.preventDefault();
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    }).then(() => {
        if (!hasLink) {
            const card = e.currentTarget.closest('.notif-card');
            if (card) {
                card.classList.remove('is-unread');
                const dot = card.querySelector('.notif-dot');
                if (dot) dot.remove();
            }
        }
    });
}
</script>
@endsection
