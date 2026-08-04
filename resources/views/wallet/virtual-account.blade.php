@extends('layouts.app')
@section('content')


<main class="max-w-md mx-auto p-6">
    <a href="{{ route('wallet.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Wallet
    </a>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="glass-card p-6 text-center">
        <p class="text-muted text-sm mb-2">Your Virtual Account</p>
        <p class="text-2xl font-bold text-accent">{{ $user->virtual_account_number }}</p>
        <p class="text-muted text-sm mt-1">{{ $user->virtual_account_bank }}</p>
        <p class="text-xs text-muted mt-4">Transfer any amount to this account to fund your wallet automatically. It may take a few minutes to reflect.</p>
    </div>
</main>
@endsection
