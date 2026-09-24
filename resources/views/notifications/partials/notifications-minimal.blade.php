<div class="dqm-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
            <p style="font-size:18px;font-weight:800;color:var(--dqm-text);margin:0;">Notifications</p>
            <p style="font-size:11.5px;color:var(--dqm-text-2);margin:2px 0 0;">{{ $unreadCount > 0 ? $unreadCount . ' unread' : "You're all caught up" }}</p>
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" style="background:var(--dqm-card);color:var(--dqm-text);border:none;border-radius:999px;padding:8px 14px;font-size:11px;font-weight:700;box-shadow:0 5px 14px -8px rgba(0,0,0,0.12);">Mark all read</button>
        </form>
        @endif
    </div>

    <div class="dqm-service-row" style="margin-bottom:14px;">
        <a href="{{ route('notifications.index') }}" class="dqm-service {{ $filter === 'all' ? 'dqm-selected' : '' }}"><span>All</span></a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="dqm-service {{ $filter === 'unread' ? 'dqm-selected' : '' }}"><span>Unread</span></a>
    </div>

    <div class="dqm-grid" style="grid-template-columns:1fr;gap:8px;">
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
            <div class="dqm-card" style="display:flex;align-items:center;gap:10px;padding:12px;{{ $isUnread ? 'box-shadow:0 0 0 1.5px var(--user-accent, #0a84ff) inset;' : '' }}">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--dqm-bg);display:flex;align-items:center;justify-content:center;color:var(--dqm-text);flex-shrink:0;">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                </div>
                <a href="{{ $link ?? '#' }}" style="flex:1;min-width:0;text-decoration:none;" @if($isUnread) onclick="markNotifRead(event, '{{ $n->id }}', {{ $link ? 'true' : 'false' }})" @endif>
                    <p style="font-size:13px;color:var(--dqm-text);margin:0;">{{ $n->data['message'] ?? $type }}</p>
                    <p style="font-size:10.5px;color:var(--dqm-text-3);margin:2px 0 0;">{{ $n->created_at->diffForHumans() }}</p>
                </a>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    @if($isUnread)<span style="width:7px;height:7px;border-radius:50%;background:var(--user-accent, #0a84ff);"></span>@endif
                    <form method="POST" action="{{ route('notifications.destroy', $n->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:var(--dqm-text-3);"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:40px 0;color:var(--dqm-text-3);">
                <i data-lucide="bell-off" class="w-8 h-8" style="margin:0 auto 8px;"></i>
                <p style="margin:0;">Nothing here yet</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div style="margin-top:16px;">{{ $notifications->links() }}</div>
    @endif
</div>

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
            const card = e.currentTarget.closest('.dqm-card');
            if (card) card.style.boxShadow = '0 5px 14px -8px rgba(0,0,0,0.12)';
        }
    });
}
</script>
