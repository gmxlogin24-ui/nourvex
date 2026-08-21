@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Admin Overview</h1>
        <div class="flex gap-2 flex-wrap text-sm">
            <a href="{{ route('admin.users') }}" class="balance-chip">Users</a>
            <a href="{{ route('admin.gigs') }}" class="balance-chip">Hustles</a>
            <a href="{{ route('admin.listings') }}" class="balance-chip">Listings</a>
            <a href="{{ route('admin.transactions') }}" class="balance-chip">Transactions</a>
            <a href="{{ route('admin.subscriptions') }}" class="balance-chip">Subscriptions</a>
            <a href="{{ route('admin.news') }}" class="balance-chip">News</a>
            <a href="{{ route('admin.blog') }}" class="balance-chip">Blog</a>
        </div>
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

    <div class="grid md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card"><p class="text-xs text-muted">Subscriptions Active</p><p class="text-2xl font-bold">{{ $subActive }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Pending Payment</p><p class="text-2xl font-bold">{{ $subPending }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Expired</p><p class="text-2xl font-bold">{{ $subExpired }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Cancelled</p><p class="text-2xl font-bold">{{ $subCancelled }}</p></div>
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

    <div class="stat-card mb-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold">Recent Users</h2>
            <a href="{{ route('admin.users') }}" class="text-accent text-sm">View all →</a>
        </div>
        @foreach($recentUsers as $u)
        <div class="flex justify-between items-center text-sm py-2 border-b" style="border-color:var(--border-color);">
            <span>{{ $u->name }} <span class="text-muted">({{ $u->email }})</span></span>
            <span class="text-muted">{{ $u->tier }}{{ $u->is_banned ? ' · banned' : '' }}</span>
        </div>
        @endforeach
    </div>

</div>
@endsection
