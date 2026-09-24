<div class="dqm-page">
    <div class="dqm-card-head" style="margin-bottom:14px;"><span style="font-size:18px;">Levels &amp; Badges</span></div>

    @if($currentLevel)
    <div class="dqm-hero" style="margin-bottom:16px;">
        <div class="dqm-hero-left">
            <p class="dqm-hero-label">Current Level</p>
            <p class="dqm-hero-value" style="font-size:26px;">{{ $currentLevel->name }}</p>
            <p style="font-size:11px;color:rgba(255,255,255,0.6);margin:0;">Level {{ $currentLevel->rank }} User</p>
        </div>
        <div style="font-size:40px;">⭐</div>
    </div>
    @if($currentLevel->benefits)
    <div class="dqm-card" style="margin-bottom:16px;">
        <p style="font-size:11px;font-weight:700;color:var(--dqm-text-2);text-transform:uppercase;letter-spacing:.04em;margin:0 0 10px;">Benefits</p>
        <div class="dqm-grid" style="grid-template-columns:repeat(2,1fr);gap:6px;">
            @foreach($currentLevel->benefits as $benefit)
            <p style="font-size:12px;color:var(--dqm-text);margin:0;display:flex;align-items:center;gap:6px;"><i data-lucide="check" class="w-3.5 h-3.5" style="color:var(--user-accent, #0a84ff);flex-shrink:0;"></i>{{ $benefit }}</p>
            @endforeach
        </div>
    </div>
    @endif
    @else
    <div class="dqm-card" style="margin-bottom:16px;text-align:center;">
        <p class="dqm-empty" style="margin:0;">Complete activities to unlock your first level!</p>
    </div>
    @endif

    @if($nextLevel && count($levelProgress))
    <div class="dqm-card" style="margin-bottom:16px;">
        <p class="dqm-card-head" style="margin-bottom:14px;">Progress to {{ $nextLevel->name }}</p>
        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach($levelProgress as $row)
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:12px;font-weight:600;color:var(--dqm-text);">{{ $row['label'] }}</span>
                    <span style="font-size:11px;color:var(--dqm-text-2);">{{ $row['current'] }} / {{ $row['target'] }}</span>
                </div>
                <div class="dqm-flow-track">
                    <div class="dqm-flow-fill" style="width:{{ $row['percent'] }}%;{{ $row['met'] ? 'background:#2ba84a;' : '' }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="text-align:center;margin-top:16px;padding-top:16px;border-top:1px solid var(--dqm-border);">
            <p style="font-size:11px;color:var(--dqm-text-2);margin:0 0 4px;">Overall Progress</p>
            <p style="font-size:28px;font-weight:800;color:var(--user-accent, #0a84ff);margin:0;">{{ count($levelProgress) ? (int) round(collect($levelProgress)->avg('percent')) : 0 }}%</p>
        </div>
    </div>
    @endif

    <div class="dqm-card">
        <p class="dqm-card-head" style="margin-bottom:14px;">Badges: {{ $badgesEarned }} / {{ $totalBadges }}</p>
        @if($badges->count() > 0)
        <div class="dqm-grid" style="grid-template-columns:repeat(3,1fr);gap:8px;">
            @foreach($badges as $item)
            <div style="text-align:center;padding:12px 6px;background:var(--dqm-bg);border-radius:14px;{{ !$item['owned'] ? 'opacity:0.45;' : '' }}">
                <i data-lucide="{{ $item['badge']->icon ?? 'award' }}" class="w-6 h-6" style="color:var(--user-accent, #0a84ff);margin-bottom:6px;"></i>
                <p style="font-size:11px;font-weight:700;color:var(--dqm-text);margin:0;">{{ $item['badge']->name }}</p>
                <p style="font-size:9.5px;color:var(--dqm-text-3);margin:2px 0 0;">{{ $item['badge']->description }}</p>
            </div>
            @endforeach
        </div>
        <a href="{{ route('levels-badges.badges') }}" style="display:block;text-align:center;margin-top:14px;font-size:12px;font-weight:600;color:var(--user-accent, #0a84ff);text-decoration:none;">View all badges →</a>
        @else
        <p class="dqm-empty">Complete activities to earn badges!</p>
        @endif
    </div>
</div>
