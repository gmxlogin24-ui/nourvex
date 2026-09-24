@php
    $mmAvatar = auth()->user()?->avatar_url;
    $mmTypes = ['item' => 'Items', 'service' => 'Services', 'rent' => 'Rentals', 'store' => 'Stores'];
    $mmTypeIcons = ['item' => 'ph-package', 'service' => 'ph-wrench', 'rent' => 'ph-key', 'store' => 'ph-storefront'];
    $mmCats = ['Fashion', 'Electronics', 'Books', 'Food', 'Services', 'Rentals', 'Other'];
    $mmCatIcons = ['Fashion' => 'ph-t-shirt', 'Electronics' => 'ph-device-mobile', 'Books' => 'ph-book-open', 'Food' => 'ph-hamburger', 'Services' => 'ph-wrench', 'Rentals' => 'ph-key', 'Other' => 'ph-dots-three'];
    $mmStackSource = ($recommended ?? collect())->count() >= 2 ? $recommended : $listings;
    $mmStackItems = $mmStackSource->take(6)->map(fn($i) => ['title' => $i->title, 'image_url' => $i->image_url ?? null, 'description' => $i->description ?? null]);
    $mmFeatured = $mmStackItems->first();
@endphp

<link rel="stylesheet" href="{{ asset('css/sponsor-stack.css') }}?v={{ time() }}">
<script src="{{ asset('js/sponsor-stack.js') }}?v={{ time() }}" defer></script>

<div class="dqm-page">
    <div class="dqm-stack-row" style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div class="dqm-card" style="flex:1;min-width:0;padding:14px;align-self:center;">
            <p style="font-size:10px;font-weight:700;color:var(--user-accent, #0a84ff);text-transform:uppercase;letter-spacing:.04em;margin:0 0 5px;">Sponsored</p>
            <p style="font-size:13px;font-weight:700;color:var(--dqm-text);margin:0 0 5px;line-height:1.25;">{{ $mmFeatured['title'] ?? 'Campus Market' }}</p>
            <p style="font-size:11px;color:var(--dqm-text-2);margin:0;line-height:1.4;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;">{{ $mmFeatured['description'] ?? 'A rotating spotlight on listings like these.' }}</p>
        </div>
        @include('partials.sponsor-stack', ['items' => $mmStackItems, 'tagLabel' => 'Sponsored', 'compact' => false])
    </div>

    <div class="dqm-card" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('marketplace.index') }}" style="display:flex;align-items:center;gap:8px;">
            @if($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
            @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
            <i data-lucide="search" class="w-4 h-4" style="color:var(--dqm-text-3);flex-shrink:0;"></i>
            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Search listings..." style="border:none;background:none;outline:none;flex:1;font-size:13px;color:var(--dqm-text);">
        </form>
    </div>

    <div class="dqm-service-row mm-filter-row" style="margin-bottom:8px;">
        <a href="{{ route('marketplace.index') }}" class="dqm-service {{ !$activeType ? 'dqm-selected' : '' }}"><i class="ph ph-squares-four" style="font-size:15px;"></i><span>All Types</span></a>
        @foreach($mmTypes as $val => $label)
        <a href="{{ route('marketplace.index', ['type' => $val]) }}" class="dqm-service {{ $activeType == $val ? 'dqm-selected' : '' }}"><i class="ph {{ $mmTypeIcons[$val] }}" style="font-size:15px;"></i><span>{{ $label }}</span></a>
        @endforeach
    </div>
    <div class="dqm-service-row mm-filter-row" style="margin-bottom:16px;">
        <a href="{{ route('marketplace.index') }}" class="dqm-service {{ !$activeCategory ? 'dqm-selected' : '' }}"><i class="ph ph-squares-four" style="font-size:15px;"></i><span>All Categories</span></a>
        @foreach($mmCats as $cat)
        <a href="{{ route('marketplace.index', ['category' => $cat]) }}" class="dqm-service {{ $activeCategory == $cat ? 'dqm-selected' : '' }}"><i class="ph {{ $mmCatIcons[$cat] }}" style="font-size:15px;"></i><span>{{ $cat }}</span></a>
        @endforeach
    </div>

    @if(($recommended ?? collect())->isNotEmpty())
    <div class="dqm-card-head"><span>Recommended for You</span></div>
    <div class="dqm-grid dqm-listing-grid" style="grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;">
        @foreach($recommended as $item)
        <a href="{{ route('marketplace.show', $item->id) }}" class="dqm-card" style="padding:0;overflow:hidden;">
            <div style="aspect-ratio:1;background:var(--dqm-bg);">
                @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width:100%;height:100%;object-fit:cover;">@endif
            </div>
            <div style="padding:7px;">
                <p style="font-size:13px;font-weight:700;color:var(--dqm-text);margin:0;">₦{{ number_format($item->amount) }}</p>
                <p style="font-size:11px;color:var(--dqm-text-2);margin:2px 0 0;">{{ $item->title }}</p>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <div class="dqm-card-head"><span>All Listings</span>@auth<a href="{{ route('marketplace.create') }}">Sell</a>@else<a href="{{ route('register') }}">Sign Up</a>@endauth</div>
    <div class="dqm-grid dqm-listing-grid" style="grid-template-columns:repeat(3,1fr);gap:8px;">
        @forelse($listings as $item)
        <a href="{{ route('marketplace.show', $item->id) }}" class="dqm-card" style="padding:0;overflow:hidden;">
            <div style="aspect-ratio:1;background:var(--dqm-bg);">
                @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width:100%;height:100%;object-fit:cover;">@endif
            </div>
            <div style="padding:7px;">
                @if($item->store_name)<p style="font-size:10px;color:var(--user-accent, #0a84ff);margin:0 0 2px;">{{ $item->store_name }}</p>@endif
                <p style="font-size:13px;font-weight:700;color:var(--dqm-text);margin:0;">₦{{ number_format($item->amount) }}</p>
                <p style="font-size:11px;color:var(--dqm-text-2);margin:2px 0 0;">{{ $item->title }}</p>
            </div>
        </a>
        @empty
        <p class="dqm-empty">No listings in this category yet.</p>
        @endforelse
    </div>
</div>
