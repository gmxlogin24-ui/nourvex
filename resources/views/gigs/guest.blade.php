@extends('layouts.landing')

@section('content')
<link rel="stylesheet" href="{{ asset('css/gigs-hero.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/sponsor-stack.css') }}?v={{ time() }}">
<script src="{{ asset('js/sponsor-stack.js') }}?v={{ time() }}" defer></script>

@php
    $openCount = $gigs->where('status', 'open')->count();

    // TODO: replace with a real sponsored-gigs query once that controller/method is built.
    $sponsoredItems = collect([
        ['title' => 'Sponsored Spot 1', 'image_url' => null, 'video_url' => null],
        ['title' => 'Sponsored Spot 2', 'image_url' => null, 'video_url' => null],
        ['title' => 'Sponsored Spot 3', 'image_url' => null, 'video_url' => null],
        ['title' => 'Sponsored Spot 4', 'image_url' => null, 'video_url' => null],
    ]);

    $categoryIcons = [
        'Design' => 'palette', 'Writing' => 'pen-line', 'Tech' => 'cpu',
        'Delivery' => 'truck', 'Tutoring' => 'graduation-cap', 'Events' => 'calendar',
    ];
    $badgeColors = ['#16181d', '#2563eb', '#f97316', '#16a34a', '#7c3aed', '#0ea5e9'];

    // Default sample gigs shown until real ones are posted — purely visual, not saved anywhere.
    $displayGigs = $gigs->isNotEmpty() ? $gigs : collect([
        (object)['category' => 'Design', 'title' => 'Design a flyer for campus event', 'status' => 'open', 'amount' => 3000, 'poster_name' => 'Sample Student', 'created_at' => now()],
        (object)['category' => 'Writing', 'title' => 'Write a 500-word assignment summary', 'status' => 'open', 'amount' => 2000, 'poster_name' => 'Sample Student', 'created_at' => now()],
        (object)['category' => 'Tech', 'title' => 'Fix laptop WiFi issue', 'status' => 'open', 'amount' => 2500, 'poster_name' => 'Sample Student', 'created_at' => now()],
        (object)['category' => 'Delivery', 'title' => 'Deliver package across campus', 'status' => 'open', 'amount' => 1500, 'poster_name' => 'Sample Student', 'created_at' => now()],
    ]);
@endphp

<section class="gigs-hero-band">
    <div class="max-w-6xl mx-auto gigs-hero-inner">
        <div>
            <h1 class="gigs-hero-title">Find hustles that pay,<br>posted by students like you</h1>
            <p class="gigs-hero-sub">{{ $openCount }} open hustles right now — sign up to apply or post your own.</p>
            <form method="GET" class="gigs-hero-search">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search hustles...">
                <button type="submit"><i data-lucide="search" class="w-4 h-4"></i></button>
            </form>
            <div class="gigs-hero-tags">
                @foreach($categoryIcons as $cat => $icon)
                <a href="{{ route('gigs.index', ['category' => $cat]) }}" class="gigs-hero-tag">{{ $cat }}</a>
                @endforeach
            </div>
        </div>
        <div>
            @include('partials.sponsor-stack', ['items' => $sponsoredItems])
        </div>
    </div>
</section>
<div class="gigs-hero-curve"></div>

<main class="max-w-6xl mx-auto p-6">

    {{-- Search by Category --}}
    <div class="gigs-section-head">
        <div>
            <p class="gigs-section-title">Search by Category</p>
            <p class="gigs-section-sub">Find hustles by what you're good at</p>
        </div>
        <a href="{{ route('gigs.index') }}" class="gigs-section-link">All Categories <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></a>
    </div>
    <div class="category-grid">
        @foreach($categoryIcons as $cat => $icon)
        @php $count = $gigs->where('category', $cat)->count(); @endphp
        <a href="{{ route('gigs.index', ['category' => $cat]) }}" class="category-card">
            <div class="category-card-icon">
                <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
            </div>
            <p class="category-card-label">{{ $cat }}</p>
            <p class="category-card-count">{{ $count > 0 ? $count . ' open gigs' : 'Be the first to post' }}</p>
        </a>
        @endforeach
    </div>

    {{-- Featured Gigs --}}
    <div class="gigs-section-head">
        <div>
            <p class="gigs-section-title">Featured Gigs</p>
            <p class="gigs-section-sub">Sign up to apply — new gigs added daily</p>
        </div>
        <a href="{{ route('register') }}" class="gigs-section-link">All Gigs <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></a>
    </div>
    <div class="featured-gigs-wrap">
        <div class="grid md:grid-cols-4 gap-4">
            @foreach($displayGigs as $i => $gig)
            <a href="{{ $gigs->isNotEmpty() ? route('gigs.show', $gig->id) : route('register') }}" class="jobster-card">
                <div class="jc-top">
                    <span class="jc-cat"><i data-lucide="{{ $categoryIcons[$gig->category] ?? 'briefcase' }}" class="w-3.5 h-3.5"></i> {{ $gig->category }}</span>
                    <span class="jc-badge" style="background:{{ $badgeColors[$i % count($badgeColors)] }};">
                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="jc-title">{{ $gig->title }}</p>
                <p class="jc-meta">
                    <i data-lucide="{{ $gig->status === 'open' ? 'circle-check' : 'circle-x' }}" class="w-3.5 h-3.5"></i>
                    {{ $gig->status === 'open' ? 'Open' : 'Taken' }} · ₦{{ number_format($gig->amount) }}
                </p>
                <div class="jc-footer">
                    <span class="jc-date">{{ $gig->created_at->format('M j, Y') }} by</span>
                    <span class="jc-poster">{{ $gig->poster_name }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</main>
@endsection
