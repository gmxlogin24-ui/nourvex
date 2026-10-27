@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Admin Overview</h1>
        <a href="{{ route('admin.invites') }}" class="balance-chip primary">+ Invites</a>
        <a href="{{ route('admin.announcements.index') }}" class="balance-chip">Announcements</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <div class="grid md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card"><p class="text-xs text-muted">Users</p><p class="text-2xl font-bold">{{ $userCount }}</p><p class="text-xs text-muted">{{ $bannedCount }} banned</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Wallet Balance (all)</p><p class="text-2xl font-bold">₦{{ number_format($totalWalletBalance, 2) }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Open Gigs</p><p class="text-2xl font-bold">{{ $openGigCount }}</p><p class="text-xs text-muted">{{ $gigCount }} total</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Listings</p><p class="text-2xl font-bold">{{ $listingCount }}</p></div>
    </div>

    <div class="split-view-wrap mb-6">
        <div class="split-view-list-card">
            <div class="split-view-list-header">
                <span>Recent Users</span>
                <a href="{{ route('admin.users') }}" class="text-accent text-xs">View all →</a>
            </div>
            @foreach($recentUsers as $i => $u)
            <div class="split-list-row {{ $i === 0 ? 'active' : '' }}" data-target="user-detail-{{ $u->id }}" data-name="{{ $u->name }}" data-tier="{{ $u->tier }}">
                <div class="split-list-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                <div class="split-list-meta">
                    <p class="split-list-name">{{ $u->name }}</p>
                    <p class="split-list-sub">{{ $u->tier }}{{ $u->is_banned ? ' · banned' : '' }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="split-view-detail-card">
            @foreach($recentUsers as $i => $u)
            @php $level = $u->workerLevel(); @endphp
            <div class="split-detail-panel {{ $i === 0 ? 'active' : '' }}" id="user-detail-{{ $u->id }}">
                <div class="split-detail-header">
                    <div class="split-detail-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                    <div>
                        <p class="split-detail-name">{{ $u->name }}</p>
                        <p class="split-detail-email">{{ $u->email }}</p>
                    </div>
                    <div class="split-detail-badge" data-badge="{{ $level['key'] }}">
                        <i data-lucide="{{ $level['icon'] }}"></i>
                        <span>{{ $level['label'] }}</span>
                    </div>
                </div>

                <div class="split-detail-stats">
                    <div class="split-detail-stat">
                        <i data-lucide="briefcase"></i>
                        <div>
                            <p class="split-detail-stat-value">{{ $u->completedGigsCount() }}</p>
                            <p class="split-detail-stat-label">Gigs Done</p>
                        </div>
                    </div>
                    <div class="split-detail-stat">
                        <i data-lucide="star"></i>
                        <div>
                            <p class="split-detail-stat-value">{{ $u->averageRatingReceived() ? number_format($u->averageRatingReceived(), 1) : '—' }}</p>
                            <p class="split-detail-stat-label">Avg Rating</p>
                        </div>
                    </div>
                    <div class="split-detail-stat">
                        <i data-lucide="message-square"></i>
                        <div>
                            <p class="split-detail-stat-value">{{ $u->reviewsReceivedCount() }}</p>
                            <p class="split-detail-stat-label">Reviews</p>
                        </div>
                    </div>
                    <div class="split-detail-stat">
                        <i data-lucide="flame"></i>
                        <div>
                            <p class="split-detail-stat-value">{{ $u->streak_days ?? 0 }}</p>
                            <p class="split-detail-stat-label">Day Streak</p>
                        </div>
                    </div>
                </div>

                <div class="split-detail-chips">
                    <div class="split-detail-chip">
                        <p class="text-xs text-muted">Tier</p>
                        <p class="font-semibold">{{ $u->tier }}</p>
                    </div>
                    <div class="split-detail-chip">
                        <p class="text-xs text-muted">Status</p>
                        <p class="font-semibold">{{ $u->is_banned ? 'Banned' : 'Active' }}</p>
                    </div>
                    <div class="split-detail-chip">
                        <p class="text-xs text-muted">Role</p>
                        <p class="font-semibold">{{ $u->is_admin ? 'Admin' : ($u->role === 'customer_care' ? 'Customer Care' : 'User') }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.users') }}?search={{ urlencode($u->email) }}" class="split-detail-action">Manage in Users →</a>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="stat-card">
            <h2 class="font-semibold mb-3">Subscriptions Expiring Soon (14d)</h2>
            @forelse($expiringSoon as $s)
            <div class="flex justify-between text-sm py-2 border-b" style="border-color:var(--border-color);">
                <span>{{ $s->user->name ?? 'Unknown' }}</span>
                <span class="text-muted">{{ $s->expires_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-sm text-muted">None expiring soon.</p>
            @endforelse
        </div>
        <div class="stat-card">
            <h2 class="font-semibold mb-3">Stuck Pending Payments (&gt;2h)</h2>
            @forelse($stuckPending as $s)
            <div class="flex justify-between text-sm py-2 border-b" style="border-color:var(--border-color);">
                <span>{{ $s->user->name ?? 'Unknown' }}</span>
                <span class="text-muted">₦{{ number_format($s->amount) }}</span>
            </div>
            @empty
            <p class="text-sm text-muted">None stuck.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateTopbarSelectedUser(name, tier) {
    var slot = document.getElementById('admin-topbar-selected-user');
    if (!slot) return;
    var avatarEl = slot.querySelector('.admin-topbar-selected-avatar');
    if (avatarEl) avatarEl.textContent = name ? name.charAt(0).toUpperCase() : '—';
}

document.querySelectorAll('.split-list-row').forEach(function(row) {
    row.addEventListener('click', function() {
        var targetId = row.getAttribute('data-target');
        document.querySelectorAll('.split-list-row').forEach(function(r) { r.classList.remove('active'); });
        document.querySelectorAll('.split-detail-panel').forEach(function(p) { p.classList.remove('active'); });
        row.classList.add('active');
        document.getElementById(targetId).classList.add('active');
        updateTopbarSelectedUser(row.getAttribute('data-name'), row.getAttribute('data-tier'));
    });
});

// Initialize topbar with the default-active row (first user) on page load
document.addEventListener('DOMContentLoaded', function() {
    var firstRow = document.querySelector('.split-list-row.active');
    if (firstRow) updateTopbarSelectedUser(firstRow.getAttribute('data-name'), firstRow.getAttribute('data-tier'));
});
</script>
@endsection
