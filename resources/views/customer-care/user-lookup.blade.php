@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">User Lookup</h1>
        <a href="{{ route('customer-care.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    <form method="GET" class="flex gap-2 mb-6">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email" class="glass-input" style="flex:1;">
        <button class="balance-chip primary">Search</button>
    </form>

    @if($search && !$user)
        <p class="text-sm text-muted">No user found.</p>
    @endif

    @if($user)
    <div class="stat-card">
        <p class="font-semibold text-lg">{{ $user->name }}</p>
        <p class="text-sm text-muted mb-3">{{ $user->email }}</p>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="text-muted">Tier:</span> {{ $user->tier }}</div>
            <div><span class="text-muted">Banned:</span> {{ $user->is_banned ? 'Yes' : 'No' }}</div>
            <div><span class="text-muted">BVN Verified:</span> {{ $user->virtual_account_number ? 'Yes' : 'No' }}</div>
            <div><span class="text-muted">Wallet Restricted:</span> {{ $user->wallet_restricted ? 'Yes' : 'No' }}</div>
        </div>
        <form method="POST" action="{{ route('customer-care.flag') }}" class="mt-4 flex gap-2">
            @csrf
            <input type="hidden" name="flaggable_type" value="user">
            <input type="hidden" name="flaggable_id" value="{{ $user->id }}">
            <input type="text" name="reason" required placeholder="Reason for flagging" class="glass-input" style="flex:1;">
            <button class="balance-chip" style="font-size:0.75rem;">Flag for Admin</button>
        </form>
    </div>
    @endif
</div>
@endsection
