@extends('layouts.app-authenticated')
@section('content')
<link rel="stylesheet" href="{{ asset('css/marketplace-neu.css') }}?v={{ time() }}">

@if((auth()->user()->theme_style ?? 'glass') === 'plain')
    @include('marketplace.partials.marketplace-minimal')
@elseif((auth()->user()->theme_style ?? 'glass') === 'aurora')
    @include('marketplace.partials.marketplace-aurora')
@else

<main class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-semibold hoverfade-text">Campus Market</h1>
            <p class="text-muted text-sm">Buy, sell, rent, or hire other students</p>
        </div>
        @auth<a href="{{ route('marketplace.create') }}" class="btn-primary text-sm"><i data-lucide="plus" class="w-4 h-4"></i> Post to Campus Market</a>@else<a href="{{ route('register') }}" class="btn-primary text-sm"><i data-lucide="plus" class="w-4 h-4"></i> Sign Up to Sell</a>@endauth
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('marketplace.index') }}" class="mb-4 reveal-on-scroll">
        @if($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
        @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
        <div class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Search listings..." class="input-field !mt-0 flex-1">
            <button type="submit" class="btn-primary" style="padding:0.6rem 1rem;"><i data-lucide="search" class="w-4 h-4"></i></button>
        </div>
    </form>

    <!-- Type filter pills -->
    <div class="cat-scroll mb-3 reveal-on-scroll">
        <a href="{{ route('marketplace.index') }}" class="cat-pill {{ !$activeType ? 'active' : '' }}">All Types</a>
        @foreach(['item' => 'Items', 'service' => 'Services', 'rent' => 'Rentals', 'store' => 'Stores'] as $val => $label)
        <a href="{{ route('marketplace.index', ['type' => $val]) }}" class="cat-pill {{ $activeType == $val ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <!-- Horizontal category pills, scrollable, never pushes layout -->
    <div class="cat-scroll mb-6 reveal-on-scroll">
        <a href="{{ route('marketplace.index') }}" class="cat-pill {{ !$activeCategory ? 'active' : '' }}">All Categories</a>
        @foreach(['Fashion', 'Electronics', 'Books', 'Food', 'Services', 'Rentals', 'Other'] as $cat)
        <a href="{{ route('marketplace.index', ['category' => $cat]) }}" class="cat-pill {{ $activeCategory == $cat ? 'active' : '' }}">{{ $cat }}</a>
        @endforeach
    </div>

    <div class="glass-card p-4 mb-6 flex items-center gap-3" id="marketAdSlot" data-face-cursor style="border:1px dashed var(--border-color);">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:var(--color-card);">
            <i data-lucide="megaphone" class="w-5 h-5 text-muted"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">Sponsored</p>
            <p class="text-muted text-xs">Ad space — nothing running here yet</p>
        </div>
    </div>
    @if(($recommended ?? collect())->isNotEmpty())
    <div class="mb-6">
        <h2 class="font-semibold mb-3">Recommended for You</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($recommended as $item)
            <a href="{{ route('marketplace.show', $item->id) }}" class="listing-card reveal-on-scroll">
                <div class="listing-img">
                    @if($item->image_url)
                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                    @else
                    <div class="listing-placeholder"><i data-lucide="image" class="w-8 h-8"></i></div>
                    @endif
                    <span class="type-badge type-{{ $item->listing_type }}">{{ ucfirst($item->listing_type) }}</span>
                </div>
                <div class="p-3">
                    <p class="font-semibold text-accent">₦{{ number_format($item->amount) }}</p>
                    <p class="text-sm mt-1 line-clamp-1">{{ $item->title }}</p>
                    <p class="text-muted text-xs mt-1 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i> {{ $item->location }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($listings as $item)
        <a href="{{ route('marketplace.show', $item->id) }}" class="listing-card reveal-on-scroll">
            <div class="listing-img">
                @if($item->image_url)
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                @else
                <div class="listing-placeholder"><i data-lucide="image" class="w-8 h-8"></i></div>
                @endif
                <span class="type-badge type-{{ $item->listing_type }}">{{ ucfirst($item->listing_type) }}</span>
                @if($item->is_verified_seller)
                <span class="verified-badge"><i data-lucide="badge-check" class="w-3 h-3"></i> Verified</span>
                @endif
            </div>
            <div class="p-3">
                @if($item->store_name)
                <p class="text-xs text-accent font-medium mb-0.5">{{ $item->store_name }}</p>
                @endif
                <p class="font-semibold text-accent">₦{{ number_format($item->amount) }}</p>
                <p class="text-sm mt-1 line-clamp-1">{{ $item->title }}</p>
                <p class="text-muted text-xs mt-1 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-3 h-3"></i> {{ $item->location }}
                </p>
            </div>
        </a>
        @endforeach
        @if(count($listings) === 0)
        <p class="text-muted text-sm col-span-full">No listings in this category yet.</p>
        @endif
    </div>
</main>
@endif
@endsection
