@extends('layouts.app-authenticated')
@section('content')
@if((auth()->user()->theme_style ?? 'glass') === 'plain')
@include('q-coin.partials.q-coin-minimal')
@else
<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1 flex items-center gap-2"><i data-lucide="gem" class="w-5 h-5"></i> Q Coin</h1>
    <p class="text-muted text-sm mb-6">Earn coins daily, build a streak, and redeem for rewards.</p>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <div class="stat-card p-6 mb-4 text-center" style="background:linear-gradient(135deg,#9b7cf0,#7c5cf0); color:#fff;">
        <p class="text-xs opacity-80 uppercase tracking-wide font-semibold">Balance</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($balance) }} <span class="text-lg font-medium">Q</span></p>
        <p class="text-xs opacity-80 mt-2">{{ $streakDays }} day streak</p>
    </div>

    <form method="POST" action="{{ route('qcoin.claim') }}" class="mb-6">
        @csrf
        <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2" {{ $canClaimToday ? '' : 'disabled' }}>
            <i data-lucide="gift" class="w-4 h-4"></i>
            {{ $canClaimToday ? 'Claim Daily Bonus (+' . (50 + $nextStreakBonus) . ' Q)' : 'Already Claimed Today' }}
        </button>
    </form>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="stat-card p-4 text-center">
            <p class="text-xs text-muted">Today</p>
            <p class="text-lg font-semibold">{{ number_format($todayEarned) }}</p>
        </div>
        <div class="stat-card p-4 text-center">
            <p class="text-xs text-muted">Total Earned</p>
            <p class="text-lg font-semibold">{{ number_format($totalEarned) }}</p>
        </div>
        <div class="stat-card p-4 text-center">
            <p class="text-xs text-muted">Total Spent</p>
            <p class="text-lg font-semibold">{{ number_format($totalSpent) }}</p>
        </div>
    </div>

    <h2 class="text-sm font-semibold mb-3 text-muted uppercase tracking-wide">History</h2>
    <div class="flex flex-col gap-2">
        @forelse($history as $h)
            <div class="stat-card p-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">{{ $h->description ?? ucfirst(str_replace('_', ' ', $h->type)) }}</p>
                    <p class="text-xs text-muted">{{ $h->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-sm font-semibold" style="color:{{ $h->amount >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $h->amount >= 0 ? '+' : '' }}{{ number_format($h->amount) }}
                </span>
            </div>
        @empty
            <p class="text-muted text-sm text-center py-8">No Q Coin activity yet.</p>
        @endforelse
    </div>
</main>
@endif
@endsection
