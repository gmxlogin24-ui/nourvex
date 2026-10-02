@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-md mx-auto p-6">
    <a href="{{ route('wallet.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Wallet
    </a>

    <div class="glass-card p-6">
        <h1 class="text-xl font-semibold mb-1">Get Your Virtual Account</h1>
        <p class="text-muted text-sm mb-6">Get a personal bank account number. Any transfer to it automatically funds your wallet.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('wallet.virtual-account.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-muted">Phone Number</label>
                <input type="text" name="phonenumber" required placeholder="08012345678" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">BVN</label>
                <input type="text" name="bvn" required maxlength="11" placeholder="11-digit BVN" class="input-field !mt-1">
                <p class="text-xs text-muted mt-1">Required by Paystack for permanent accounts. We never store your BVN.</p>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Create Virtual Account</button>
        </form>
    </div>
</main>
@endsection
