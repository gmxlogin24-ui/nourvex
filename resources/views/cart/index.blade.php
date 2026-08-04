@extends('layouts.app')
@section('content')


<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="shopping-cart" class="w-5 h-5"></i> Your Cart
    </h1>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
    @endif

    @forelse($listings as $listing)
    <div class="glass-card p-4 mb-3 flex items-center gap-4">
        @if($listing->image_url)
        <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="w-16 h-16 object-cover rounded">
        @else
        <div class="listing-placeholder w-16 h-16 flex items-center justify-center rounded"><i data-lucide="image" class="w-6 h-6"></i></div>
        @endif

        <div class="flex-1">
            <a href="{{ route('marketplace.show', $listing->id) }}" class="font-medium text-sm">{{ $listing->title }}</a>
            <p class="text-accent font-semibold">₦{{ number_format($listing->amount) }}</p>
        </div>

        <form method="POST" action="{{ route('cart.remove', $listing->id) }}">
            @csrf
            <button type="submit" class="text-muted" title="Remove">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
    @empty
    <p class="text-muted text-sm">Your cart is empty. <a href="{{ route('marketplace.index') }}" class="text-accent">Browse the marketplace</a>.</p>
    @endforelse

    @if($listings->isNotEmpty())
    <div class="glass-card p-4 mt-4 flex items-center justify-between">
        <span class="font-semibold">Total</span>
        <span class="font-bold text-accent">₦{{ number_format($total) }}</span>
    </div>

    <form method="POST" action="{{ route('cart.checkout') }}">
        @csrf
        <button type="submit" class="btn-primary w-full justify-center mt-4">
            <i data-lucide="lock" class="w-4 h-4"></i> Checkout via Wallet — ₦{{ number_format($total) }}
        </button>
    </form>
    @endif
</main>
@endsection
