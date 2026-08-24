@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Customer Care</h1>
        <div class="flex gap-2 flex-wrap text-sm">
            <a href="{{ route('customer-care.orders') }}" class="balance-chip">Orders</a>
            <a href="{{ route('customer-care.gig-disputes') }}" class="balance-chip">Gig Disputes</a>
            <a href="{{ route('customer-care.listing-disputes') }}" class="balance-chip">Market Disputes</a>
            <a href="{{ route('customer-care.user-lookup') }}" class="balance-chip">User Lookup</a>
        </div>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <div class="grid md:grid-cols-4 gap-4">
        <div class="stat-card"><p class="text-xs text-muted">Pending Deliveries</p><p class="text-2xl font-bold">{{ $pendingDeliveries }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Open Gig Disputes</p><p class="text-2xl font-bold">{{ $openGigDisputes }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Open Market Disputes</p><p class="text-2xl font-bold">{{ $openListingDisputes }}</p></div>
        <div class="stat-card"><p class="text-xs text-muted">Open Flags</p><p class="text-2xl font-bold">{{ $openFlags }}</p></div>
    </div>
</div>
@endsection
