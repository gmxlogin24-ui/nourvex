@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-6">Wallet</h1>

    <div class="balance-card mb-6">
        <p class="balance-label">Total Balance</p>
        <p class="balance-amount">₦{{ number_format($balance, 2) }}</p>
        <div class="balance-actions">
            <button onclick="document.getElementById('withdrawModal').classList.add('open')" class="balance-chip primary"><i data-lucide="arrow-down-to-line" class="w-4 h-4"></i> Withdraw</button>
            <button onclick="document.getElementById('topupModal').classList.add('open')" class="balance-chip"><i data-lucide="plus" class="w-4 h-4"></i> Top Up</button>
            <button onclick="document.getElementById('sendModal').classList.add('open')" class="balance-chip"><i data-lucide="send" class="w-4 h-4"></i> Send</button>
        </div>
    </div>

    <a href="{{ route('wallet.virtual-account') }}" class="glass-card p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="icon-badge"><i data-lucide="landmark" class="w-5 h-5"></i></div>
            <div>
                <p class="text-sm font-medium">Get Your Virtual Account</p>
                <p class="text-muted text-xs">Fund your wallet by bank transfer, anytime</p>
            </div>
        </div>
        <i data-lucide="chevron-right" class="w-4 h-4 text-muted"></i>
    </a>

    <div class="glass-card p-6">
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
            <input type="number" name="amount" min="100" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Continue to Payment</button>
        </form>
        <button onclick="document.getElementById('topupModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>

<div id="sendModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Send Money</h3>
        <form method="POST" action="{{ route('wallet.send') }}">
            @csrf
            <input type="text" name="recipient_identifier" placeholder="Recipient email" required class="input-field !mt-0 mb-3">
            <input type="number" name="amount" min="1" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <input type="text" name="note" placeholder="Note (optional)" class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Send Money</button>
        </form>
        <button onclick="document.getElementById('sendModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>

<div id="withdrawModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Withdraw Funds</h3>
        <form method="POST" action="{{ route('wallet.withdraw') }}">
            @csrf
            <input type="number" name="amount" min="100" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Confirm Withdraw</button>
        </form>
        <button onclick="document.getElementById('withdrawModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>
@endsection
