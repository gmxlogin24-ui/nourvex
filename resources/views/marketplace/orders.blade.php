@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-2xl mx-auto p-6">
    <a href="{{ route('marketplace.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Campus Market
    </a>
    <h1 class="text-2xl font-semibold mb-6">My Orders</h1>

    @if(session('status'))
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-accent">{{ session('status') }}</div>
    @endif

    <div class="space-y-3">
        @foreach($orders as $order)
        <div class="glass-card p-4 flex items-center justify-between gap-3">
            <div>
                <p class="font-medium text-sm">{{ $order->listing->title ?? 'Listing removed' }}</p>
                <p class="text-muted text-xs mt-1">₦{{ number_format($order->amount) }} · {{ $order->created_at->diffForHumans() }}</p>
            </div>
            @if($order->status === 'paid')
            <form method="POST" action="{{ route('marketplace.order.confirm', $order->id) }}" onsubmit="return confirm('Confirm you received this item? This releases payment to the seller.');">
                @csrf
                <button type="submit" class="btn-primary text-sm">Confirm Receipt</button>
            </form>
            @else
            <span class="text-accent text-sm font-medium">✓ Completed</span>
            @endif
        </div>
        @endforeach
        @if(count($orders) === 0)
        <p class="text-muted text-sm">No orders yet.</p>
        @endif
    </div>
</main>
@endsection
