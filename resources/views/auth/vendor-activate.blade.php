@extends('layouts.app')
@section('content')
<div class="max-w-sm mx-auto mt-24 p-6 reveal-on-scroll">
    <div class="glass-card p-8 text-center">
        <div class="icon-badge mx-auto mb-4"><i data-lucide="store" class="w-6 h-6"></i></div>
        <h1 class="text-xl font-semibold mb-1">One Last Step</h1>
        <p class="text-muted text-sm mb-6">Pay ₦5,000 to activate your merchant account and start posting gigs and listings.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('vendor.pay') }}">
            @csrf
            <button type="submit" class="btn-primary w-full justify-center">Pay ₦5,000 & Activate</button>
        </form>
    </div>
</div>
@endsection
