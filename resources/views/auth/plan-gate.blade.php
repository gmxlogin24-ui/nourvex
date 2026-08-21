@extends('layouts.landing')
@section('content')
<div class="auth-scene-clean">
    <div class="max-w-4xl mx-auto px-4" style="width:100%;">
        <div class="text-center mb-10">
            <div class="logo-badge mx-auto mb-4"></div>
            <h1 class="text-2xl font-semibold mb-2">Like what you see?</h1>
            <p class="text-sm" style="opacity:0.75;">Create an account to unlock this. Choose the plan that fits you best.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            <div class="plan-tile">
                <p class="text-xs text-muted uppercase mb-1">{{ $plans['tier1']['label'] }}</p>
                <p class="text-2xl font-bold mb-3">₦{{ number_format($plans['tier1']['price']) }}<span class="text-xs font-normal">/yr</span></p>
                <ul class="text-sm space-y-2 mb-6" style="opacity:0.8;">
                    <li>Browse hustles & campus market</li>
                    <li>Wallet & referrals</li>
                    <li>Post up to 5 gigs/listings</li>
                </ul>
                <a href="{{ route('register', ['plan' => 'tier1']) }}" class="btn-secondary w-full justify-center">Get {{ $plans['tier1']['label'] }}</a>
            </div>

            <div class="plan-tile featured">
                <p class="text-xs uppercase mb-1" style="color:var(--color-accent);">{{ $plans['tier2']['label'] }}</p>
                <p class="text-2xl font-bold mb-3">₦{{ number_format($plans['tier2']['price']) }}<span class="text-xs font-normal">/yr</span></p>
                <ul class="text-sm space-y-2 mb-6" style="opacity:0.8;">
                    <li>Everything in {{ $plans['tier1']['label'] }}</li>
                    <li>Unlimited posts</li>
                    <li>Money Tracker unlocked</li>
                    <li>Verified seller badge</li>
                </ul>
                <a href="{{ route('register', ['plan' => 'tier2']) }}" class="glass-btn w-full justify-center" style="display:flex;">Get {{ $plans['tier2']['label'] }}</a>
            </div>

            <div class="plan-tile">
                <p class="text-xs text-muted uppercase mb-1">{{ $plans['tier3']['label'] }}</p>
                <p class="text-2xl font-bold mb-3">₦{{ number_format($plans['tier3']['price']) }}<span class="text-xs font-normal">/yr</span></p>
                <ul class="text-sm space-y-2 mb-6" style="opacity:0.8;">
                    <li>Everything in {{ $plans['tier2']['label'] }}</li>
                    <li>Highest referral bonus rate</li>
                    <li>Priority support</li>
                </ul>
                <a href="{{ route('register', ['plan' => 'tier3']) }}" class="btn-secondary w-full justify-center">Get {{ $plans['tier3']['label'] }}</a>
            </div>
        </div>

        <p class="text-sm text-center mt-8" style="opacity:0.75;">
            Already have an account? <a href="{{ route('login') }}" class="text-accent">Log in</a>
        </p>
    </div>
</div>
<script src="{{ asset('js/auth-reveal.js') }}"></script>
@endsection
