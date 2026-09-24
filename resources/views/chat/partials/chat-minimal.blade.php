<div class="dqm-page">
    <div class="dqm-card-head" style="margin-bottom:14px;"><span style="font-size:18px;">Messages</span></div>
    <div class="dqm-grid" style="grid-template-columns:1fr;gap:8px;">
        @forelse($conversations as $c)
        <a href="{{ $c['link'] }}" class="dqm-card" style="display:flex;align-items:center;gap:12px;padding:12px;text-decoration:none;">
            <div class="dqm-topbar-avatar" style="width:44px;height:44px;flex-shrink:0;">
                @if($c['other_avatar'])<img src="{{ $c['other_avatar'] }}" alt="">@else<span>{{ strtoupper(substr($c['other_name'], 0, 1)) }}</span>@endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;font-weight:700;color:var(--dqm-text);">{{ $c['other_name'] }}</span>
                    @if($c['last_at'])<span style="font-size:10px;color:var(--dqm-text-3);">{{ $c['last_at']->diffForHumans() }}</span>@endif
                </div>
                <p style="font-size:10.5px;color:var(--dqm-text-3);margin:1px 0;">{{ $c['subject_type'] }} #{{ $c['subject_id'] }}</p>
                <p style="font-size:12px;color:var(--dqm-text-2);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c['last_body'] }}</p>
            </div>
            @if($c['unread'] > 0)
            <span style="flex-shrink:0;background:var(--user-accent, #0a84ff);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;">{{ $c['unread'] }}</span>
            @endif
        </a>
        @empty
        <p class="dqm-empty" style="text-align:center;padding:32px 0;">No conversations yet.</p>
        @endforelse
    </div>
</div>
