@extends('layouts.app-admin')
@section('content')
@php $M = \App\Support\Money::class; @endphp
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Prices and fees</h1>
        <a href="{{ route('admin.finance.profit') }}" class="text-accent text-sm">← Profit and pool</a>
    </div>
    @if(session('status'))<div class="stat-card mb-4">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="stat-card mb-4" style="color:#ff6b6b;">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.finance.pricing.save') }}">
        @csrf
        <div class="stat-card mb-6">
            <h2 class="font-semibold mb-1">Ad prices (paid in Q coin)</h2>
            <p class="text-muted text-sm mb-3">What a user pays to post an ad. 10 Q equals ₦1 at today's rate.</p>
            <div class="grid gap-3" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
                @foreach($ads as $days => $price)
                <label class="text-sm">{{ $days }} days (Q)<input type="number" step="1" min="0" name="ad_{{ $days }}" value="{{ $price }}" class="input-field !mt-1">
                    <span class="text-muted">≈ {{ $M::naira($M::toNaira($price)) }}</span></label>
                @endforeach
            </div>
        </div>

        <div class="stat-card mb-6">
            <h2 class="font-semibold mb-1">Withdrawal fee</h2>
            <p class="text-muted text-sm mb-3">Charged to accounts without a virtual account. Verified accounts withdraw free.</p>
            <label class="text-sm block" style="max-width:240px">Fee %<input type="number" step="0.01" min="0" max="10" name="withdrawal_fee_percent" value="{{ $wfee }}" class="input-field !mt-1"></label>
        </div>
        <button class="balance-chip primary mb-8">Save prices</button>
    </form>

    <div class="stat-card">
        <h2 class="font-semibold mb-1">Subscription tiers (not editable yet)</h2>
        <p class="text-muted text-sm mb-3">These are still fixed in code. They become editable once every page and payment path that shows or charges them reads from here, so a page can never show one price while charging another.</p>
        <table class="w-full text-sm">
            @foreach($tiers as $k => $p)
            <tr class="border-b" style="border-color:var(--border-color);"><td class="py-2">{{ $p['label'] }} ({{ $k }})</td><td class="py-2 text-right">{!! $M::coin($p['price']) !!}</td></tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
