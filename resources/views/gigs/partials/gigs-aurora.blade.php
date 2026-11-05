@php
    $gaAvatar = auth()->user()?->avatar_url;
    $gaInitial = strtoupper(substr(auth()->user()->name ?? 'U', 0, 1));
    $gaOpenCount = $gigs->where('status', 'open')->count();
    $gaAvgReward = $gigs->count() ? $gigs->avg('amount') : 0;
    $gaMyPosted = $gigs->where('user_id', auth()->id())->count();
    $gaCategoryIcons = [
        'Design' => 'bi-palette-fill', 'Writing' => 'bi-pencil-fill', 'Tech' => 'bi-cpu-fill',
        'Errands & Delivery' => 'bi-truck', 'Tutoring' => 'bi-mortarboard-fill', 'Photography' => 'bi-camera-fill',
    ];
    $gaVibrant = ['au-vibrant-green', 'au-vibrant-blue', 'au-vibrant-orange', 'au-vibrant-purple'];
@endphp

<div class="au-page">
    <div class="au-topbar">
        <a href="{{ route('profile.index') }}" class="au-avatar-btn">
            @if($gaAvatar)<img src="{{ $gaAvatar }}" alt="">@else<span>{{ $gaInitial }}</span>@endif
        </a>
        <a href="{{ Route::has('inbox.hustles') ? route('inbox.hustles') : '#' }}" class="au-icon-btn" title="Chat"><i class="bi bi-chat-dots-fill"></i></a>
        <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="au-icon-btn" title="Notifications"><i class="bi bi-bell-fill"></i></a>
    </div>

    <div class="au-greeting">
        <span class="au-greeting-welcome">Browse</span>
        <span class="au-greeting-name">Find Hustles</span>
    </div>

    <div class="au-toolbar-card">
        <form method="GET" class="au-search-bar">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search hustles...">
        </form>

        <div class="au-chip-scroll">
            <a href="{{ route('gigs.index') }}" class="au-chip {{ !request('category') ? 'active' : '' }}">All</a>
            @foreach($gaCategoryIcons as $cat => $icon)
            <a href="{{ route('gigs.index', ['category' => $cat]) }}" class="au-chip {{ request('category') === $cat ? 'active' : '' }}"><i class="bi {{ $icon }}"></i> {{ $cat }}</a>
            @endforeach
        </div>
    </div>

    <div class="au-kpi-row">
        <div class="au-kpi-card">
            <span class="au-kpi-icon au-vibrant-green"><i class="bi bi-briefcase-fill"></i></span>
            <span class="au-kpi-value">{{ $gaOpenCount }}</span>
            <span class="au-kpi-label">Open Hustles</span>
        </div>
        <div class="au-kpi-card">
            <span class="au-kpi-icon au-vibrant-blue"><i class="bi bi-cash-coin"></i></span>
            <span class="au-kpi-value">₦{{ number_format($gaAvgReward) }}</span>
            <span class="au-kpi-label">Avg Reward</span>
        </div>
        <div class="au-kpi-card">
            <span class="au-kpi-icon au-vibrant-purple"><i class="bi bi-person-fill"></i></span>
            <span class="au-kpi-value">{{ $gaMyPosted }}</span>
            <span class="au-kpi-label">Posted by You</span>
        </div>
    </div>

    <div class="au-page-section">
        <div class="au-section-header au-section-header-loose">
            <span>Available Hustles</span>
            @auth@if(auth()->user()->planAllows('gigs_post'))<a href="{{ route('gigs.create') }}">Post Hustle</a>@endif@else<a href="{{ route('register') }}">Sign Up</a>@endauth
        </div>
        <div class="au-card-grid">
            @forelse($gigs as $i => $gig)
            <a href="{{ route('gigs.show', $gig->id) }}" class="au-gig-card" style="{{ $gig->status !== 'open' ? 'opacity:0.6;' : '' }}">
                <div class="au-gig-card-top">
                    <span class="au-gig-category">{{ $gig->category }}</span>
                    <span class="au-gig-badge {{ $gaVibrant[$i % 4] }}"><i class="bi {{ $gaCategoryIcons[$gig->category] ?? 'bi-briefcase-fill' }}"></i></span>
                </div>
                <p class="au-gig-title">{{ $gig->title }}</p>
                <div class="au-gig-poster">
                    <span class="au-gig-poster-avatar">{{ substr($gig->poster_name, 0, 1) }}</span>
                    {{ $gig->poster_name }}
                    <span>· {{ $gig->created_at->diffForHumans() }}</span>
                </div>
                <div class="au-gig-footer">
                    <span class="au-gig-price">₦{{ number_format($gig->amount) }}</span>
                    <span class="au-gig-status {{ $gig->status === 'open' ? 'open' : 'taken' }}">{{ $gig->status === 'open' ? 'Open' : 'Taken' }}</span>
                </div>
            </a>
            @empty
            <p class="au-empty-note">No hustles match — try a different search or category.</p>
            @endforelse
        </div>
    </div>
</div>
