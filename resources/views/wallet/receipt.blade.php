@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-md mx-auto p-6">
    <a href="{{ route('wallet.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Wallet
    </a>

    <div class="glass-card p-6 text-center">
        <div class="receipt-check">
            <i data-lucide="check" class="w-6 h-6"></i>
        </div>
        <p class="text-muted text-sm mt-3">{{ $tx->amount >= 0 ? 'Money Received' : 'Money Sent' }}</p>
        <p class="text-2xl font-bold {{ $tx->amount >= 0 ? 'text-accent' : '' }}">
            {{ $tx->amount >= 0 ? '+' : '-' }}₦{{ number_format(abs($tx->amount), 2) }}
        </p>
        <p class="text-xs text-muted mt-1 capitalize">{{ $tx->status ?? 'successful' }}</p>
    </div>

    <div class="glass-card p-6 mt-4">
        <div class="flex items-center justify-between py-2 border-b" style="border-color:rgba(255,255,255,0.06);">
            <span class="text-muted text-sm">Reference</span>
            <span class="font-mono text-sm">{{ $tx->reference ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between py-2 border-b" style="border-color:rgba(255,255,255,0.06);">
            <span class="text-muted text-sm">{{ $tx->amount >= 0 ? 'From' : 'To' }}</span>
            <span class="text-sm font-medium">{{ $tx->relatedUser->name ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between py-2 border-b" style="border-color:rgba(255,255,255,0.06);">
            <span class="text-muted text-sm">Description</span>
            <span class="text-sm text-right">{{ $tx->description }}</span>
        </div>
        <div class="flex items-center justify-between py-2">
            <span class="text-muted text-sm">Date</span>
            <span class="text-sm">{{ $tx->created_at->format('d M Y, h:ia') }}</span>
        </div>
    </div>

    <button type="button" id="shareReceiptBtn" class="btn-primary w-full justify-center mt-4">Share Receipt</button>
</main>

<style>
.receipt-check{
  width:56px; height:56px; border-radius:50%; margin:0 auto;
  background: var(--color-accent, #0F9D6E); color:#fff;
  display:flex; align-items:center; justify-content:center;
}
</style>

<script>
(function(){
    var btn = document.getElementById('shareReceiptBtn');
    if (!btn) return;
    btn.addEventListener('click', function(){
        var text = @json("NAURVEX Pay Receipt\nRef: " . ($tx->reference ?? '-') . "\n" . $tx->description . "\nAmount: ₦" . number_format(abs($tx->amount), 2) . "\nStatus: " . ($tx->status ?? 'successful') . "\nDate: " . $tx->created_at->format('d M Y, h:ia'));
        if (navigator.share) {
            navigator.share({ text: text }).catch(function(){});
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function(){
                var o = btn.textContent; btn.textContent = 'Copied!';
                setTimeout(function(){ btn.textContent = o; }, 1500);
            });
        }
    });
})();
</script>
@endsection
