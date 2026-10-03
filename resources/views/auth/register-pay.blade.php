@extends('layouts.auth')
@section('content')
<div class="max-w-sm mx-auto mt-24 p-6 reveal-on-scroll">
    <div class="glass-card p-8 text-center">
        <div class="icon-badge mx-auto mb-4"><i data-lucide="sparkles" class="w-6 h-6"></i></div>
        <h1 class="text-xl font-semibold mb-1">Almost there, {{ $pendingName }}</h1>
        <p class="text-muted text-sm mb-6">Pay ₦{{ number_format($planPrice) }} for your {{ $planLabel }} plan to activate your account and start using Campulsy. Your account is created the moment this payment is confirmed.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <a href="{{ route('register.pay.initiate') }}" class="btn-primary w-full justify-center">Pay ₦{{ number_format($planPrice) }} & Activate {{ $planLabel }}</a>
    </div>
</div>
@endsection
