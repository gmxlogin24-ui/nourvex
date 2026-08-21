@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-6xl mx-auto p-6">
    <a href="{{ route('marketplace.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Campus Market
    </a>
    <h1 class="text-2xl font-semibold mb-6">My Favorites</h1>

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
        @if(count($listings) === 0)
        <p class="text-muted text-sm col-span-full">No favorites yet — tap the heart on any listing to save it here.</p>
        @endif
    </div>
</main>
@endsection
