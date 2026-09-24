<div class="dqm-page">
    <div class="dqm-card-head" style="margin-bottom:4px;"><span style="font-size:18px;">Q Coin</span></div>
    <p style="font-size:12px;color:var(--dqm-text-2);margin:0 0 14px;">Earn coins daily, build a streak, and redeem for rewards.</p>

    @if(session('success'))<div class="dqm-card" style="margin-bottom:12px;color:#2ba84a;font-size:12px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="dqm-card" style="margin-bottom:12px;color:#ff375f;font-size:12px;">{{ session('error') }}</div>@endif

    <div class="dqm-hero" style="margin-bottom:16px;flex-direction:column;align-items:flex-start;">
        <p class="dqm-hero-label">Balance</p>
        <p class="dqm-hero-value">{{ number_format($balance) }} <span style="font-size:16px;">Q</span></p>
        <p style="font-size:11px;color:rgba(255,255,255,0.6);margin:4px 0 0;">{{ $streakDays }} day streak</p>
    </div>

    <form method="POST" action="{{ route('qcoin.claim') }}" style="margin-bottom:16px;">
        @csrf
        <button type="submit" class="btn-primary" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;" {{ $canClaimToday ? '' : 'disabled' }}>
            <i data-lucide="gift" class="w-4 h-4"></i>
            {{ $canClaimToday ? 'Claim Daily Bonus (+' . (50 + $nextStreakBonus) . ' Q)' : 'Already Claimed Today' }}
        </button>
    </form>

    <div class="dqm-stat-row" style="max-width:none;grid-template-columns:repeat(3,1fr);margin-bottom:16px;">
        <div class="dqm-stat dqm-tint-green"><span class="v">{{ number_format($todayEarned) }}</span><span class="l">Today</span></div>
        <div class="dqm-stat dqm-tint-blue"><span class="v">{{ number_format($totalEarned) }}</span><span class="l">Total Earned</span></div>
        <div class="dqm-stat dqm-tint-orange"><span class="v">{{ number_format($totalSpent) }}</span><span class="l">Total Spent</span></div>
    </div>

    <div class="dqm-card-head" style="margin-bottom:10px;">History</div>
    <div class="dqm-grid" style="grid-template-columns:1fr;gap:8px;">
        @forelse($history as $h)
        <div class="dqm-card" style="display:flex;align-items:center;justify-content:space-between;padding:12px;">
            <div>
                <p style="font-size:13px;font-weight:600;color:var(--dqm-text);margin:0;">{{ $h->description ?? ucfirst(str_replace('_', ' ', $h->type)) }}</p>
                <p style="font-size:10.5px;color:var(--dqm-text-3);margin:2px 0 0;">{{ $h->created_at->diffForHumans() }}</p>
            </div>
            <span style="font-size:13px;font-weight:700;color:{{ $h->amount >= 0 ? '#2ba84a' : '#ff375f' }};">{{ $h->amount >= 0 ? '+' : '' }}{{ number_format($h->amount) }}</span>
        </div>
        @empty
        <p class="dqm-empty" style="text-align:center;padding:32px 0;">No Q Coin activity yet.</p>
        @endforelse
    </div>
</div>
