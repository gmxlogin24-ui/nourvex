@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-md mx-auto p-6">
    <a href="{{ route('wallet.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Wallet
    </a>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="glass-card p-6 text-center">
        <p class="text-muted text-sm mb-2">Your NAURVEX Pay Number</p>
        <p class="text-2xl font-bold text-accent">{{ $user->virtual_account_number }}</p>
        <p class="text-muted text-sm mt-1">{{ $user->virtual_account_bank }}</p>
        <button type="button" id="vaCopyBtn" data-account="{{ $user->virtual_account_number }}" class="btn-primary mt-4" style="padding:8px 20px;">Copy Account Number</button>
        <p class="text-xs text-muted mt-4">Transfer any amount to this account to fund your wallet automatically. It may take a few minutes to reflect.</p>
    </div>

    <script>
    (function(){
        var btn = document.getElementById('vaCopyBtn');
        if (!btn) return;
        btn.addEventListener('click', function(){
            navigator.clipboard.writeText(btn.dataset.account).then(function(){
                var original = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function(){ btn.textContent = original; }, 1500);
            });
        });
    })();
    </script>
</main>
@endsection
