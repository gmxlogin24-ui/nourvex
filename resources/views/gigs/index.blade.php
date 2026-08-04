@extends('layouts.app-authenticated')

@section('content')

<main class="max-w-6xl mx-auto p-6">
    @php
        $openCount = $gigs->where('status', 'open')->count();
        $avgReward = $gigs->count() ? $gigs->avg('amount') : 0;
        $myPosted = $gigs->where('user_id', auth()->id())->count();
    @endphp

    <div class="flex justify-between items-center mb-4 flex-wrap gap-3 reveal-on-scroll">
        <div>
            <h1 class="text-2xl font-bold hoverfade-text">Available Gigs</h1>
            <p class="text-muted text-sm">Sorted for you — gigs you open most stay near the top</p>
        </div>
        @auth<a href="{{ route('gigs.create') }}" class="btn-primary text-sm"><i data-lucide="plus" class="w-4 h-4"></i> Post a Gig</a>@else<a href="{{ route('register') }}" class="btn-primary text-sm"><i data-lucide="plus" class="w-4 h-4"></i> Sign Up to Post</a>@endauth
    </div>

    <form method="GET" class="reveal-on-scroll" style="margin-bottom:0.75rem;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search gigs..." class="input-field" style="max-width:320px;">
    </form>

    <div class="flex flex-wrap items-center justify-between gap-3 reveal-on-scroll" style="margin-bottom:0.75rem;">
        <div class="gig-filter-bar" style="margin:0;">
            <a href="{{ route('gigs.index') }}" class="gig-filter-chip {{ !request('category') ? 'active' : '' }}">All</a>
            @foreach($categories as $cat)
            <a href="{{ route('gigs.index', ['category' => $cat]) }}" class="gig-filter-chip {{ request('category') === $cat ? 'active' : '' }}">{{ $cat }}</a>
            @endforeach
        </div>
        <select onchange="window.location.href=this.value" class="input-field" style="width:auto;max-width:160px;">
            <option value="{{ request()->fullUrlWithQuery(['sort' => null]) }}" {{ !request('sort') ? 'selected' : '' }}>Relevance</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'highest']) }}" {{ request('sort') === 'highest' ? 'selected' : '' }}>Highest Pay</option>
        </select>
    </div>

    <div class="kpi-row reveal-on-scroll">
        <div class="kpi-tile featured">
            <div class="kpi-tile-top"><div class="kpi-tile-icon" data-face-cursor><i data-lucide="briefcase" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value">{{ $openCount }}</p>
            <p class="kpi-tile-label">Open Gigs</p>
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top"><div class="kpi-tile-icon" data-face-cursor><i data-lucide="banknote" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value">₦{{ number_format($avgReward) }}</p>
            <p class="kpi-tile-label">Average Reward</p>
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top"><div class="kpi-tile-icon" data-face-cursor><i data-lucide="user" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value">{{ $myPosted }}</p>
            <p class="kpi-tile-label">Posted by You</p>
        </div>
    </div>


    <div class="grid md:grid-cols-3 gap-4 reveal-on-scroll">
        @forelse($gigs as $gig)
        <a href="{{ route('gigs.show', $gig->id) }}" class="gig-card-v2 reveal-on-scroll" style="{{ $gig->status !== 'open' ? 'opacity:0.6;' : '' }}">
            <div class="gig-card-v2-top">
                <span class="gig-card-category">{{ $gig->category }}</span>
                <span class="gig-card-status {{ $gig->status === 'open' ? 'open' : 'taken' }}">{{ $gig->status === 'open' ? 'Open' : 'Taken' }}</span>
            </div>
            <p class="gig-card-title">{{ $gig->title }}</p>
            @if($gig->description)
            <p class="text-muted text-xs" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $gig->description }}</p>
            @endif
            <div class="gig-card-poster">
                <span class="gig-card-poster-avatar">{{ substr($gig->poster_name, 0, 1) }}</span>
                {{ $gig->poster_name }}
                @if($gig->poster_verified)
                <i data-lucide="badge-check" class="w-3.5 h-3.5" style="color:#4f7bff;"></i>
                @endif
                @if($gig->poster_level && $gig->poster_level['key'] !== 'new')
                <span class="text-xs text-accent">· {{ $gig->poster_level['label'] }}</span>
                @endif
                @if($gig->poster_badge_tier === 'bronze')
                <span class="member-badge member-badge-bronze" style="padding:1px 7px 1px 5px;font-size:0.6rem;"><i data-lucide="medal" style="width:9px;height:9px;"></i></span>
                @endif
                <span>· {{ $gig->created_at->diffForHumans() }}</span>
            </div>
            @if($gig->avg_rating)
            <div class="gig-card-rating">
                <span class="stars">★</span> {{ $gig->avg_rating }} <span class="text-muted">({{ $gig->review_count }})</span>
            </div>
            @endif
            @if($gig->status !== 'open' && $gig->accepted_by_name)
            <p class="text-xs text-muted">Taken by {{ $gig->accepted_by_name }}</p>
            @endif
            <div class="gig-card-footer">
                <span class="gig-card-price">₦{{ number_format($gig->amount) }}</span>
                @if($gig->views_count)
                <span class="gig-card-views"><i data-lucide="eye" class="w-3.5 h-3.5"></i> {{ $gig->views_count }}</span>
                @endif
            </div>
        </a>
        @empty
        <p class="text-muted text-sm col-span-full">No gigs match — try a different search or category.</p>
        @endforelse
    </div>
</main>

@endsection
