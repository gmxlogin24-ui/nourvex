@extends('layouts.app-authenticated')
@section('content')
<link rel="stylesheet" href="{{ asset('css/marketplace-neu.css') }}?v={{ time() }}">


<main class="max-w-4xl mx-auto p-6">
    <a href="{{ route('marketplace.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Marketplace
    </a>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="glass-card overflow-hidden reveal-on-scroll" style="position:relative;">
            @if($listing->image_url)
            <img src="{{ $listing->image_url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
            @else
            <div class="listing-placeholder" style="aspect-ratio:4/3;"><i data-lucide="image" class="w-12 h-12"></i></div>
            @endif
            <span class="type-badge type-{{ $listing->listing_type }}">{{ ucfirst($listing->listing_type) }}</span>
        </div>

        <div>
            @if($listing->store_name)
            <p class="text-sm text-accent font-medium">{{ $listing->store_name }}</p>
            @endif
            <p class="text-3xl font-bold text-accent">₦{{ number_format($listing->amount) }}</p>
            <h1 class="text-xl font-semibold mt-2">{{ $listing->title }}</h1>
            <div class="flex items-center gap-3 mt-2 text-muted text-sm">
                <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $listing->location }}</span>
                <span class="flex items-center gap-1"><i data-lucide="tag" class="w-3 h-3"></i> {{ $listing->condition }}</span>
                @if($avgRating)
                <span class="flex items-center gap-1"><i data-lucide="star" class="w-3 h-3 text-amber-500"></i> {{ number_format($avgRating, 1) }} ({{ $reviews->count() }})</span>
                @endif
            </div>

            <a href="{{ $seller ? route('profile.index') : '#' }}" class="glass-card p-4 mt-6 flex items-center gap-3" style="text-decoration:none;color:inherit;">
                <div class="w-10 h-10 rounded-full bg-card flex items-center justify-center font-semibold">
                    {{ $seller ? strtoupper(substr($seller->business_name ?? $seller->name, 0, 1)) : '?' }}
                </div>
                <div class="flex-1">
                    <p class="font-medium text-sm">{{ $listing->seller_name }}</p>
                    @if($listing->is_verified_seller)
                    <p class="text-xs text-accent flex items-center gap-1"><i data-lucide="badge-check" class="w-3 h-3"></i> Verified seller</p>
                    @else
                    <p class="text-xs text-muted">Student seller</p>
                    @endif
                    @if($seller)
                    <p class="text-xs text-muted mt-0.5">Member since {{ $seller->created_at->format('M Y') }}</p>
                    @endif
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-muted"></i>
            </a>

            @if($listing->whatsapp || $listing->phone)
            <div class="flex gap-3 mt-4">
                @if($listing->whatsapp)
                <a href="https://wa.me/234{{ ltrim($listing->whatsapp, '0') }}" target="_blank" class="btn-primary flex-1 justify-center" style="background:#25D366;">
                    <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
                </a>
                @endif
                @if($listing->phone)
                <a href="tel:{{ $listing->phone }}" class="btn-primary flex-1 justify-center">
                    <i data-lucide="phone" class="w-4 h-4"></i> Call
                </a>
                @endif
            </div>
            @endif

            <p class="mt-6 text-sm">{{ $listing->description }}</p>

            @if ($errors->any())
            <p class="text-red-500 text-sm mt-4">{{ $errors->first() }}</p>
            @endif

            @auth
                @if($listing->status === 'sold' && $listing->buyer_id === auth()->id())
                <form method="POST" action="{{ route('marketplace.confirm', $listing->id) }}">
                    @csrf
                    <button type="submit" class="btn-primary mt-6 w-full justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Receipt — Release Payment
                    </button>
                </form>
                <p class="text-muted text-xs mt-2 text-center">Funds are held in escrow until you confirm you received this.</p>
                @elseif($listing->status === 'completed')
                <p class="text-accent text-sm mt-6 text-center font-medium">✓ Purchase completed</p>
                @elseif($listing->status !== 'available')
                <p class="text-muted text-sm mt-6 text-center">This listing is no longer available.</p>
                @else
                <div class="flex gap-3 mt-6">
                    <form method="POST" action="{{ route('cart.add', $listing->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-secondary w-full justify-center">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
                        </button>
                    </form>
                    <form method="POST" action="{{ route('marketplace.purchase', $listing->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Buy Now — ₦{{ number_format($listing->amount) }}
                        </button>
                    </form>
                </div>
                @endif
            @else
            <a href="{{ route('register') }}" class="btn-primary mt-6 w-full justify-center">
                <i data-lucide="lock" class="w-4 h-4"></i> Sign up to Buy — ₦{{ number_format($listing->amount) }}
            </a>
            @endif
        </div>
    </div>

    <!-- Reviews -->
    <div class="glass-card p-6 mt-8 reveal-on-scroll">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold">Reviews {{ $reviews->count() ? '(' . $reviews->count() . ')' : '' }}</h2>
            @if($avgRating)
            <span class="flex items-center gap-1 text-sm"><i data-lucide="star" class="w-4 h-4 text-amber-500"></i> {{ number_format($avgRating, 1) }} average</span>
            @endif
        </div>

        @auth
        <form method="POST" action="{{ route('marketplace.review', $listing->id) }}" class="mb-6 space-y-2">
            @csrf
            <select name="rating" required class="input-field !mt-0">
                <option value="">Rate this listing</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Okay</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Bad, report an issue</option>
            </select>
            <textarea name="comment" rows="2" placeholder="Share your experience or report a problem with this listing..." class="input-field !mt-0"></textarea>
            <button type="submit" class="btn-secondary text-sm">Submit Review</button>
        </form>
        @else
        <p class="text-muted text-sm mb-6">
            <a href="{{ route('login') }}" class="text-accent">Log in</a> to leave a review.
        </p>
        @endauth

        @forelse($reviews as $review)
        <div class="tx-table-row" style="display:block;">
            <div class="flex justify-between items-start">
                <p class="font-medium text-sm">{{ $review->reviewer->name ?? 'User' }}</p>
                <span class="text-xs text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
            </div>
            @if($review->comment)
            <p class="text-muted text-sm mt-1">{{ $review->comment }}</p>
            @endif
            <p class="text-muted text-xs mt-1">{{ $review->created_at->diffForHumans() }}</p>
        </div>
        @empty
        <p class="text-muted text-sm">No reviews yet — be the first to share your experience.</p>
        @endforelse
    </div>
</main>
@endsection
