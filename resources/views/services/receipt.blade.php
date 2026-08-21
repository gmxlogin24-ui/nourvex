@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-md mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Receipt</h1>

    <div class="stat-card p-6">
        <p class="text-sm text-muted mb-1">Reference</p>
        <p class="font-mono mb-4">{{ $purchase->provider_reference }}</p>

        <p class="text-sm text-muted mb-1">Service</p>
        <p class="mb-4">{{ ucfirst($purchase->service_type) }} — {{ strtoupper($purchase->network) }}</p>

        <p class="text-sm text-muted mb-1">Recipient</p>
        <p class="mb-4">{{ $purchase->phone }}</p>

        <p class="text-sm text-muted mb-1">Amount</p>
        <p class="mb-4">₦{{ number_format($purchase->amount, 2) }}</p>

        <p class="text-sm text-muted mb-1">Status</p>
        <p class="mb-4 capitalize">{{ $purchase->status }}</p>

        <p class="text-sm text-muted mb-1">Date</p>
        <p>{{ $purchase->created_at->format('d M Y, h:ia') }}</p>
    </div>

    <a href="{{ route('services.index') }}" class="btn-primary w-full block text-center mt-4">Back to Services</a>
</main>
@endsection
