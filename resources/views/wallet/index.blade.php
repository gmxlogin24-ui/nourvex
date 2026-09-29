@extends('layouts.app-authenticated')
@section('content')

@if((auth()->user()->theme_style ?? 'glass') === 'plain')
<div class="dqm-page" style="padding-top:0 !important;">
@include('partials.dqm-balance-deck')
</div>
@endif

<main class="max-w-3xl mx-auto p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;' : '' }}">
    <h1 class="text-2xl font-semibold mb-6">Wallet</h1>

    <div class="balance-card mb-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <p class="balance-label">Total Balance</p>
        <p class="balance-amount">₦{{ number_format($balance, 2) }}</p>
        <div class="balance-actions">
            <button onclick="document.getElementById('withdrawModal').classList.add('open')" class="balance-chip primary"><i data-lucide="arrow-down-to-line" class="w-4 h-4"></i> Withdraw</button>
            <button onclick="document.getElementById('topupModal').classList.add('open')" class="balance-chip"><i data-lucide="plus" class="w-4 h-4"></i> Top Up</button>
            <a href="{{ route('wallet.send.form') }}" class="balance-chip"><i data-lucide="send" class="w-4 h-4"></i> Send</a>
        </div>
    </div>

    <a href="{{ route('wallet.virtual-account') }}" class="glass-card p-4 mb-3 flex items-center justify-between {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <div class="flex items-center gap-3">
            <div class="icon-badge"><i data-lucide="landmark" class="w-5 h-5"></i></div>
            <div>
                <p class="text-sm font-medium">Get Your Virtual Account</p>
                <p class="text-muted text-xs">Fund your wallet by bank transfer, anytime</p>
            </div>
        </div>
        <i data-lucide="chevron-right" class="w-4 h-4 text-muted"></i>
    </a>

    <a href="{{ route('wallet.bank-account.edit') }}" class="glass-card p-4 mb-6 flex items-center justify-between {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <div class="flex items-center gap-3">
            <div class="icon-badge"><i data-lucide="building-2" class="w-5 h-5"></i></div>
            <div>
                <p class="text-sm font-medium">{{ $hasBankAccount ? 'Payout Bank Account' : 'Add Payout Bank Account' }}</p>
                <p class="text-muted text-xs">{{ $hasBankAccount ? 'Where your withdrawals are sent' : 'Required before you can withdraw' }}</p>
            </div>
        </div>
        <i data-lucide="chevron-right" class="w-4 h-4 text-muted"></i>
    </a>

    @if(!($isVerified ?? true))
    <div class="glass-card p-4 mb-6" style="border:1px solid var(--color-accent);">
        <p class="text-sm font-medium">Unverified wallet limit</p>
        <p class="text-muted text-xs mt-1">You can hold up to ₦{{ number_format($walletCapRemaining ?? 0) }} more before you need to verify your BVN. Earnings from gigs, sales, and referrals aren't affected.</p>
    </div>
    @endif

    <div class="glass-card p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <h2 class="font-semibold mb-4">Transaction History</h2>
        @if(session('status'))
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-accent">{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-red-500">{{ $errors->first() }}</div>
    @endif
    @if(count($transactions) === 0)
        <p class="text-muted text-sm">No transactions yet. Earnings from gigs, sales, and referrals will show up here.</p>
        @else
        <div class="space-y-3">
            @foreach($transactions as $tx)
            <div class="list-row">
                <div>
                    <p class="text-sm font-medium">{{ $tx->description }}</p>
                    <p class="text-muted text-xs">{{ $tx->created_at->diffForHumans() }}</p>
                </div>
                <p class="font-semibold text-sm {{ $tx->amount >= 0 ? 'text-accent' : '' }}">
                    {{ $tx->amount >= 0 ? '+' : '' }}₦{{ number_format($tx->amount, 2) }}
                </p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</main>

<div id="topupModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Top Up Wallet</h3>
        <form method="POST" action="{{ route('flutterwave.initiate') }}">
            @csrf
            <input type="number" name="amount" min="100" max="1000000" step="0.01" inputmode="decimal" autocomplete="off" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Continue to Payment</button>
        </form>
        <button onclick="document.getElementById('topupModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>

<div id="withdrawModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Withdraw Funds</h3>
        @unless($hasPin ?? false)
        <p class="text-sm text-muted mb-3">You need a transaction PIN before withdrawing. <a href="{{ route('wallet.pin.edit') }}" class="underline font-semibold">Set one now</a>.</p>
        @endunless
        @unless($hasBankAccount ?? false)
        <p class="text-sm text-muted mb-3">Add a payout bank account before withdrawing. <a href="{{ route('wallet.bank-account.edit') }}" class="underline font-semibold">Add one now</a>.</p>
        @endunless
        <form method="POST" action="{{ route('wallet.withdraw') }}" onsubmit="return confirm('Withdraw this amount to your bank account? This cannot be reversed once submitted.');">
            @csrf
            <input type="number" name="amount" min="100" max="1000000" step="0.01" inputmode="decimal" autocomplete="off" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <input type="password" name="pin" inputmode="numeric" maxlength="6" placeholder="Transaction PIN" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center" {{ (($hasPin ?? false) && ($hasBankAccount ?? false)) ? '' : 'disabled' }}>Confirm Withdraw</button>
        </form>
        <button onclick="document.getElementById('withdrawModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>
@endsection
