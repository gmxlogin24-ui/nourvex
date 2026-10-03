@php
    $maAvatar = auth()->user()?->avatar_url;
    $maInitial = strtoupper(substr(auth()->user()->name ?? 'U', 0, 1));
    $maTypes = ['item' => 'Items', 'service' => 'Services', 'rent' => 'Rentals', 'store' => 'Stores'];
    $maCats = ['Fashion', 'Electronics', 'Books', 'Food', 'Services', 'Rentals', 'Other'];
@endphp

<div class="au-page">
    <div class="au-topbar">
        <a href="{{ route('profile.index') }}" class="au-avatar-btn">
            @if($maAvatar)<img src="{{ $maAvatar }}" alt="">@else<span>{{ $maInitial }}</span>@endif
        </a>
        <a href="{{ Route::has('chat.index') ? route('chat.index') : '#' }}" class="au-icon-btn" title="Chat"><i class="bi bi-chat-dots-fill"></i></a>
        <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="au-icon-btn" title="Notifications"><i class="bi bi-bell-fill"></i></a>
    </div>

    <div class="au-greeting">
        <span class="au-greeting-welcome">Shop</span>
        <span class="au-greeting-name">Campus Market</span>
    </div>

    <div class="au-toolbar-card">
        <form method="GET" action="{{ route('marketplace.index') }}" class="au-search-bar">
            @if($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
            @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Search listings...">
        </form>

        <div class="au-chip-scroll">
            <a href="{{ route('marketplace.index') }}" class="au-chip {{ !$activeType ? 'active' : '' }}">All Types</a>
            @foreach($maTypes as $val => $label)
            <a href="{{ route('marketplace.index', ['type' => $val]) }}" class="au-chip {{ $activeType == $val ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="au-chip-scroll">
            <a href="{{ route('marketplace.index') }}" class="au-chip {{ !$activeCategory ? 'active' : '' }}">All Categories</a>
            @foreach($maCats as $cat)
            <a href="{{ route('marketplace.index', ['category' => $cat]) }}" class="au-chip {{ $activeCategory == $cat ? 'active' : '' }}">{{ $cat }}</a>
            @endforeach
        </div>
    </div>

    @if(($recommended ?? collect())->isNotEmpty())
    <div class="au-page-section">
        <div class="au-section-header au-section-header-loose"><span>Recommended for You</span></div>
        <div class="au-listing-grid">
            @foreach($recommended as $item)
            <a href="{{ route('marketplace.show', $item->id) }}" class="au-listing-card">
                <div class="au-listing-img">
                    @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->title }}">@else<div class="au-listing-placeholder"><i class="bi bi-image"></i></div>@endif
                    <span class="au-type-badge">{{ ucfirst($item->listing_type) }}</span>
                </div>
                <p class="au-listing-price">₦{{ number_format($item->amount) }}</p>
                <p class="au-listing-title">{{ $item->title }}</p>
                <p class="au-listing-loc"><i class="bi bi-geo-alt-fill"></i> {{ $item->location }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="au-page-section">
        <div class="au-section-header au-section-header-loose">
            <span>All Listings</span>
            @auth@if(auth()->user()->planAllows('market_sell'))<a href="{{ route('marketplace.create') }}">Sell</a>@endif@else<a href="{{ route('register') }}">Sign Up</a>@endauth
        </div>
        <div class="au-listing-grid">
            @forelse($listings as $item)
            <a href="{{ route('marketplace.show', $item->id) }}" class="au-listing-card">
                <div class="au-listing-img">
                    @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->title }}">@else<div class="au-listing-placeholder"><i class="bi bi-image"></i></div>@endif
                    <span class="au-type-badge">{{ ucfirst($item->listing_type) }}</span>
                    @if($item->is_verified_seller)<span class="au-verified-badge"><i class="bi bi-patch-check-fill"></i> Verified</span>@endif
                </div>
                @if($item->store_name)<p class="au-listing-store">{{ $item->store_name }}</p>@endif
                <p class="au-listing-price">₦{{ number_format($item->amount) }}</p>
                <p class="au-listing-title">{{ $item->title }}</p>
                <p class="au-listing-loc"><i class="bi bi-geo-alt-fill"></i> {{ $item->location }}</p>
            </a>
            @empty
            <p class="au-empty-note">No listings in this category yet.</p>
            @endforelse
        </div>
    </div>
</div>
