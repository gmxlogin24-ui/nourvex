@extends('layouts.app')
@section('content')


<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1">Subscriptions</h1>
    <p class="text-muted text-sm mb-6">Post gigs and marketplace listings, earn bigger referral bonuses — billed yearly</p>

    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-6">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-6">{{ session('status') }}</div>
    @endif

    @if ($current)
    <div class="glass-card p-4 mb-6 flex items-center justify-between reveal-on-scroll">
        <div>
            <p class="text-sm text-muted">Current plan</p>
            <p class="font-semibold">{{ $plans[$current->plan]['label'] }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-muted">Renews / expires</p>
            <p class="font-semibold {{ $current->isExpiringSoon() ? 'text-red-500' : 'text-accent' }}">{{ $current->expires_at->format('M j, Y') }}</p>
        </div>
    </div>
    @endif

    <div class="grid md:grid-cols-3 gap-4">
        <div class="glass-card p-5 reveal-on-scroll">
            <p class="text-sm text-muted">Starter</p>
            <h2 class="text-xl font-semibold mt-1">Free</h2>
            <p class="text-2xl font-bold text-accent mt-2">₦0</p>
            <ul class="text-sm text-muted mt-4 space-y-2">
                <li>✓ Browse gigs & marketplace</li>
                <li>✓ Buy items & accept gigs</li>
                <li>✓ Wallet & earnings tracking</li>
                <li class="opacity-40">✗ Post gigs or listings</li>
                <li class="opacity-40">✗ Referral bonuses</li>
            </ul>
            @if ($user->tier === 'free')
            <p class="text-xs text-accent mt-5">✓ Current plan</p>
            @endif
        </div>

        <div class="glass-card p-5 reveal-on-scroll">
            <p class="text-sm text-muted">For active sellers</p>
            <h2 class="text-xl font-semibold mt-1">Plus</h2>
            <p class="text-2xl font-bold text-accent mt-2">₦{{ number_format($plans['tier1']['price']) }} <span class="text-xs text-muted font-normal">/ year</span></p>
            <ul class="text-sm text-muted mt-4 space-y-2">
                <li>✓ Everything in Free</li>
                <li>✓ Post gigs & marketplace listings</li>
                <li>✓ Earn 10% referral bonus</li>
                <li>✓ Up to 5 active posts</li>
            </ul>
            @if (!$current || $current->plan !== 'tier1')
            <form method="POST" action="{{ route('subscriptions.subscribe') }}" class="mt-5 space-y-2">
                @csrf
                <input type="hidden" name="target_tier" value="tier1">
                <select name="payment_method" class="input-field !mt-0 text-sm">
                    <option value="wallet">Pay from Wallet (₦{{ number_format($user->wallet_balance) }} available)</option>
                    <option value="flutterwave">Pay by Card/Bank</option>
                </select>
                <button type="submit" class="btn-primary w-full justify-center">Subscribe to Plus</button>
            </form>
            @else
            <p class="text-xs text-accent mt-5">✓ Active until {{ $current->expires_at->format('M j, Y') }}</p>
            @endif
        </div>

        <div class="glass-card p-5 reveal-on-scroll" style="position:relative;">
            <span class="tx-status done" style="position:absolute;top:1rem;right:1rem;">Best Value</span>
            <p class="text-sm text-muted">For power sellers</p>
            <h2 class="text-xl font-semibold mt-1">Pro</h2>
            <p class="text-2xl font-bold text-accent mt-2">₦{{ number_format($plans['tier2']['price']) }} <span class="text-xs text-muted font-normal">/ year, from Plus</span></p>
            <ul class="text-sm text-muted mt-4 space-y-2">
                <li>✓ Everything in Plus</li>
                <li>✓ Verified badge</li>
                <li>✓ Featured placement</li>
                <li>✓ 15% referral bonus</li>
                <li>✓ Unlimited active posts</li>
            </ul>
            @if ($current && $current->plan === 'tier1')
            <form method="POST" action="{{ route('subscriptions.subscribe') }}" class="mt-5 space-y-2">
                @csrf
                <input type="hidden" name="target_tier" value="tier2">
                <select name="payment_method" class="input-field !mt-0 text-sm">
                    <option value="wallet">Pay from Wallet (₦{{ number_format($user->wallet_balance) }} available)</option>
                    <option value="flutterwave">Pay by Card/Bank</option>
                </select>
                <button type="submit" class="btn-primary w-full justify-center">Subscribe to Pro</button>
            </form>
            @elseif ($current && $current->plan === 'tier2')
            <p class="text-xs text-accent mt-5">✓ Active until {{ $current->expires_at->format('M j, Y') }}</p>
            @else
            <p class="text-xs text-muted mt-5">Requires an active Plus subscription first</p>
            @endif
        </div>
    </div>
</main>
@endsection
