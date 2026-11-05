@extends('layouts.landing')
@section('content')
<div class="max-w-sm mx-auto mt-8 p-6 reveal-on-scroll">
    <div class="glass-card p-8">
        <a href="{{ route('register') }}" class="text-muted text-xs flex items-center gap-1 mb-4"><i data-lucide="arrow-left" class="w-3 h-3"></i> Back</a>

        <div class="icon-badge mb-4"><i data-lucide="user" class="w-5 h-5"></i></div>
        <h1 class="text-xl font-bold">Let's get you earning</h1>
        <p class="text-muted text-sm mt-1 mb-5">Free forever. Takes less than a minute — you'll be browsing gigs right after.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs text-muted font-medium">Full Name</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Chidinma Okafor" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Password</label>
                <input type="password" name="password" required placeholder="At least 6 characters" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-xs text-muted font-medium">Referral Code <span class="text-muted font-normal">(optional)</span></label>
                <input type="text" name="referral_code" value="{{ old('referral_code') }}" placeholder="Got one from a friend?" class="input-field !mt-1">
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Create My Free Account</button>
        </form>

        <p class="text-xs text-muted text-center mt-5 flex items-center justify-center gap-1">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Your info is safe with us. No spam, no hidden charges.
        </p>

        <p class="text-sm text-center text-muted mt-4">Already have an account? <a href="{{ route('login') }}" class="text-accent font-medium">Log in</a></p>
    </div>
</div>

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
