@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-3xl mx-auto p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;' : '' }}">
    <h1 class="text-2xl font-semibold mb-2">Your Referrals</h1>
    <p class="text-muted text-sm mb-6">Earn a share when people you invite complete gigs or upgrade their tier</p>

    <div class="hero-blob p-6 mb-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-hero' : '' }}">
        <p class="text-white/80 text-sm">Your referral code</p>
        <p class="text-2xl font-bold text-white mt-1">{{ $code }}</p>
        <p class="text-white/70 text-xs mt-2">Share this with friends when they sign up</p>
    </div>

    <div class="bg-card card-rounded p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <h2 class="font-semibold mb-4">Referred Students ({{ count($referrals) }})</h2>
        @if(count($referrals) === 0)
        <p class="text-muted text-sm">No referrals yet — share your code above to start earning.</p>
        @else
        <div class="space-y-3">
            @foreach($referrals as $r)
            <div class="flex items-center justify-between text-sm border-b pb-3" style="border-color:var(--border-color);">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-card"></div>
                    <span>{{ $r->name }}</span>
                </div>
                <span class="text-xs text-muted">{{ strtoupper($r->tier) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</main>
@endsection
