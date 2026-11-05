@extends('layouts.landing')
@section('content')
<div class="max-w-sm mx-auto mt-6 p-6 reveal-on-scroll">
    <div class="glass-card p-8">
        <a href="{{ route('register') }}" class="text-muted text-xs flex items-center gap-1 mb-4"><i data-lucide="arrow-left" class="w-3 h-3"></i> Back</a>

        <div class="icon-badge icon-badge-gigs mb-4"><i data-lucide="store" class="w-5 h-5"></i></div>
        <h1 class="text-xl font-bold">Set up your storefront</h1>
        <p class="text-muted text-sm mt-1 mb-4">₦5,000 one-time registration. You'll be posting gigs and products within minutes.</p>

        <div class="w-full h-2 rounded-full mb-5" style="background:var(--color-surface);">
            <div id="vendorProgress" class="h-2 rounded-full" style="width:20%;background:linear-gradient(90deg,var(--color-accent-light),var(--color-accent));transition:width 0.3s ease;"></div>
        </div>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.vendor.store') }}" enctype="multipart/form-data" class="space-y-4" id="vendorForm">
            @csrf

            <p class="text-xs font-semibold text-accent uppercase tracking-wide">Your Details</p>
            <div>
                <label class="text-xs text-muted font-medium">Full Name</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Chidinma Okafor" class="input-field !mt-1 vendor-field">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="input-field !mt-1 vendor-field">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Password</label>
                <input type="password" name="password" required placeholder="At least 6 characters" class="input-field !mt-1 vendor-field">
            </div>

            <p class="text-xs font-semibold text-accent uppercase tracking-wide pt-2">Your Business</p>
            <div>
                <label class="text-xs text-muted font-medium">Business / Store Name</label>
                <input type="text" name="business_name" required value="{{ old('business_name') }}" placeholder="e.g. Chi's Bakes & Bites" class="input-field !mt-1 vendor-field">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Business WhatsApp</label>
                <input type="text" name="business_whatsapp" required value="{{ old('business_whatsapp') }}" placeholder="e.g. 08012345678" class="input-field !mt-1 vendor-field">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Business Logo <span class="text-muted font-normal">(optional, adds trust)</span></label>
                <input type="file" name="business_logo" accept="image/*" class="input-field !mt-1 vendor-field-optional">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Referral Code <span class="text-muted font-normal">(optional)</span></label>
                <input type="text" name="referral_code" value="{{ old('referral_code') }}" placeholder="Got one from a friend?" class="input-field !mt-1">
            </div>

            <button type="submit" class="btn-primary w-full justify-center">Continue to Payment — ₦5,000</button>
        </form>

        <p class="text-xs text-muted text-center mt-5 flex items-center justify-center gap-1">
            <i data-lucide="lock" class="w-3.5 h-3.5"></i> Secure payment via Paystack. One-time fee, no subscriptions.
        </p>

        <p class="text-sm text-center text-muted mt-4">Already have an account? <a href="{{ route('login') }}" class="text-accent font-medium">Log in</a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fields = document.querySelectorAll('.vendor-field');
    const bar = document.getElementById('vendorProgress');
    const base = 20;
    function update() {
        const filled = Array.from(fields).filter(f => f.value.trim().length > 0).length;
        const pct = base + Math.round((filled / fields.length) * (100 - base));
        bar.style.width = pct + '%';
    }
    fields.forEach(f => f.addEventListener('input', update));
});
</script>

<script>
// ref-prefill: fill the referral code from ?ref= in the link
(function(){
  var f = document.querySelector('input[name="referral_code"]');
  if (!f || f.value) return;
  var r = new URLSearchParams(location.search).get('ref');
  if (r) f.value = r.trim().toUpperCase();
})();
</script>
@endsection
