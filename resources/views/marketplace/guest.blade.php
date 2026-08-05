@extends('layouts.landing')
@section('content')

@php
    $categoryIcons = ['Fashion'=>'shirt','Electronics'=>'cpu','Books'=>'book','Food'=>'utensils','Services'=>'wrench','Rentals'=>'key','Other'=>'grid'];

    $heroSlides = [
        ['eyebrow' => 'Promoted', 'title' => 'Campus Tech Deals', 'highlight' => 'Save up to 20%', 'sub' => 'Laptops, earbuds, and gadgets from students near you.', 'cta' => 'Shop Now', 'seed' => 'campulsyhero1'],
        ['eyebrow' => 'Promoted', 'title' => 'Fresh Fashion Drops', 'highlight' => 'New arrivals daily', 'sub' => 'Thrifted and new pieces, styled by students for students.', 'cta' => 'Browse Now', 'seed' => 'campulsyhero2'],
        ['eyebrow' => 'Promoted', 'title' => 'Study Essentials', 'highlight' => 'Everything for less', 'sub' => 'Textbooks, stationery, and dorm gear at student prices.', 'cta' => 'Shop Now', 'seed' => 'campulsyhero3'],
    ];

    // TODO: replace all placeholder collections below with real controller data.
    $quickActions = [
        ['label' => 'Best Sellers', 'icon' => 'flame', 'color' => '#f97316'],
        ['label' => 'New Arrivals', 'icon' => 'mail', 'color' => '#2563eb'],
        ['label' => 'Top Rated', 'icon' => 'star', 'color' => '#2563eb'],
        ['label' => 'On Sale', 'icon' => 'tag', 'color' => '#f97316'],
    ];

    // This pool feeds the rotating Featured Products carousel. Once a real
    // "sponsored/featured" controller exists, replace this array with the
    // actual query result — the JS below expects: badge, title, price,
    // oldPrice (nullable), seed (used to build a placeholder image URL,
    // swap for image_url when real data exists).
    $featuredPool = [
        ['badge' => 'BESTSELLER', 'title' => '48" 4K Monitor', 'price' => 45000, 'oldPrice' => null, 'seed' => 'techA1'],
        ['badge' => 'NEW', 'title' => 'Wireless Earbuds', 'price' => 12500, 'oldPrice' => 15000, 'seed' => 'techB1'],
        ['badge' => 'RARE', 'title' => 'Noise-Cancelling Headphones', 'price' => 28000, 'oldPrice' => null, 'seed' => 'techC1'],
        ['badge' => 'HOT', 'title' => 'Smartphone 5G', 'price' => 189000, 'oldPrice' => 209000, 'seed' => 'techD1'],
    ];

    $catTiles = [
        ['label' => 'Game Controllers', 'from' => 8000, 'icon' => 'gamepad-2', 'seed' => 'catgame'],
        ['label' => 'Smartphones', 'from' => 65000, 'icon' => 'smartphone', 'seed' => 'catphone'],
        ['label' => 'Smart Home', 'from' => 9500, 'icon' => 'home', 'seed' => 'cathome'],
    ];

    $placeholderPool = collect([
        (object)['title' => 'Wireless earbuds - barely used', 'amount' => 7500, 'listing_type' => 'item', 'location' => 'Hostel B'],
        (object)['title' => 'Intro to Economics textbook', 'amount' => 2500, 'listing_type' => 'item', 'location' => 'Faculty of Arts'],
        (object)['title' => 'Room fan - 3 speed', 'amount' => 4000, 'listing_type' => 'item', 'location' => 'Hostel A'],
        (object)['title' => 'Graphic design - flyers & logos', 'amount' => 3000, 'listing_type' => 'service', 'location' => 'Online'],
        (object)['title' => 'Mini fridge for rent', 'amount' => 2000, 'listing_type' => 'rent', 'location' => 'Hostel C'],
        (object)['title' => 'Campus snack store - bulk noodles', 'amount' => 500, 'listing_type' => 'store', 'location' => 'Main Gate'],
        (object)['title' => 'Bluetooth speaker', 'amount' => 6000, 'listing_type' => 'item', 'location' => 'Hostel D'],
        (object)['title' => 'Laundry pickup service', 'amount' => 1500, 'listing_type' => 'service', 'location' => 'Campus-wide'],
    ]);

    $displayListings = $listings->isNotEmpty() ? $listings : $placeholderPool;
    $justArrived = $displayListings->take(4);
    $secondFeatured = $displayListings->skip(4)->take(4)->count() ? $displayListings->skip(4)->take(4) : $displayListings->take(4);
@endphp

<style>
/* ============================================================
   EVERYTHING for the guest marketplace page lives in this ONE
   file — HTML, CSS, and JS all together, nothing scattered.
   ============================================================ */

body[data-page="marketplace"] {
  --mg-bg: #f5f8fc;
  --mg-card: #ffffff;
  --mg-text: #16181d;
  --mg-muted: #6b7280;
  --mg-border: rgba(0,0,0,0.08);
  --mg-gold: #b8860b;
  --mg-blue: #2563eb;
  background-color: var(--mg-bg) !important;
}
[data-theme="dark"] body[data-page="marketplace"] {
  --mg-bg: #0a0a0d;
  --mg-card: #1a1a22;
  --mg-text: #f5f5f7;
  --mg-muted: #9797a3;
  --mg-border: rgba(255,255,255,0.1);
  background-color: var(--mg-bg) !important;
}

.mg-page * { box-sizing: border-box; }
.mg-page { color: var(--mg-text); }

.mg-theme-toggle {
  position: fixed;
  top: 5.5rem; left: 1rem; z-index: 50;
  display: inline-flex; align-items: center; gap: 0.4rem;
  padding: 0.55rem 1.1rem;
  border-radius: 999px;
  border: 2px solid var(--mg-gold);
  background: #fff !important;
  color: #16181d !important;
  font-size: 0.85rem; font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}

/* Full-bleed hero slider */
.mg-hero { position: relative; width: 100vw; left: 50%; margin-left: -50vw; min-height: 384px; overflow: hidden; margin-bottom: 2rem; }
.mg-slide { position: absolute; inset: 0; opacity: 0; visibility: hidden; transition: opacity .6s ease; text-decoration: none; color: #fff; }
.mg-slide.active { opacity: 1; visibility: visible; z-index: 1; }
.mg-slide img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.mg-slide-overlay { position: relative; z-index: 1; height: 100%; min-height: 320px; display: flex; flex-direction: column; justify-content: flex-end; padding: 2.5rem; background: linear-gradient(180deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.05) 35%, rgba(0,0,0,0.2) 70%, rgba(0,0,0,0.6) 100%); }
.mg-eyebrow { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #d4af37; margin-bottom: 0.5rem; }
.mg-slide-title { font-size: clamp(1.6rem, 3.5vw, 2.5rem); font-weight: 800; line-height: 1.15; max-width: 18ch; }
.mg-slide-title .hl { color: #d4af37; }
.mg-slide-sub { font-size: 0.95rem; opacity: 0.92; margin-top: 0.5rem; max-width: 34ch; }
.mg-slide-cta { align-self: flex-start; background: #fff; color: #16181d; font-weight: 700; font-size: 0.85rem; padding: 0.6rem 1.4rem; border-radius: 999px; margin-top: 1.1rem; width: fit-content; }
.mg-dots { position: absolute; bottom: 1.25rem; right: 1.5rem; z-index: 2; display: flex; gap: 0.5rem; }
.mg-dot { width: 8px; height: 8px; border-radius: 999px; background: rgba(255,255,255,0.45); border: none; padding: 0; cursor: pointer; }
.mg-dot.active { background: #d4af37; width: 22px; }

.mg-content { max-width: 72rem; margin: 0 auto; padding: 0 1.5rem 3rem; }

/* Category pills */
.mg-pills { display: flex; gap: 0.5rem; overflow-x: auto; margin-bottom: 0.75rem; padding-bottom: 4px; }
.mg-pill { flex-shrink: 0; padding: 0.5rem 1rem; border-radius: 999px; border: 1px solid var(--mg-border); background: var(--mg-card); color: var(--mg-text); text-decoration: none; font-size: 0.85rem; white-space: nowrap; }
.mg-pill.active { background: var(--mg-gold); border-color: var(--mg-gold); color: #fff; }

.mg-section-head { display: flex; justify-content: space-between; align-items: flex-end; margin: 2rem 0 1rem; gap: 1rem; }
.mg-section-title { font-size: 1.4rem; font-weight: 800; }
.mg-section-sub { color: var(--mg-muted); font-size: 0.85rem; }
.mg-section-link { font-size: 0.85rem; font-weight: 600; color: var(--mg-gold); text-decoration: none; }

/* Quick action icon row */
.mg-quick-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.85rem; margin-bottom: 1.5rem; }
@media (min-width: 700px) { .mg-quick-grid { grid-template-columns: repeat(4, 1fr); } }
.mg-quick-tile { display: flex; align-items: center; gap: 0.75rem; background: var(--mg-card); border: 1px solid var(--mg-border); border-radius: 1rem; padding: 0.9rem 1rem; text-decoration: none; color: var(--mg-text); }
.mg-quick-icon { width: 2.5rem; height: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
.mg-quick-label { font-weight: 700; font-size: 0.9rem; }
.mg-quick-sub { font-size: 0.72rem; color: var(--mg-gold); font-weight: 600; }

/* Plain listing card (Just Arrived / All Listings) */
.mg-card { display: block; background: var(--mg-card); border: 1px solid var(--mg-border); text-decoration: none; color: var(--mg-text);
  box-shadow: 0 0 0 1px rgba(0,0,0,0.08), 0 5px 0 0 rgba(0,0,0,0.08), 0 12px 22px rgba(0,0,0,0.15); }
[data-theme="dark"] .mg-card { box-shadow: 0 0 0 1px rgba(255,255,255,0.06), 0 5px 0 0 rgba(0,0,0,0.35), 0 12px 22px rgba(0,0,0,0.5); }
.mg-card-img { position: relative; aspect-ratio: 4/3; background: #e5e7eb; overflow: hidden; }
.mg-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.mg-badge { position: absolute; top: 8px; left: 8px; background: rgba(22,24,29,0.85); color: #fff; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; padding: 3px 8px; }
.mg-card-body { padding: 0.75rem; }
.mg-price { color: var(--mg-gold); font-weight: 700; }
.mg-title { font-size: 0.85rem; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mg-loc { color: var(--mg-muted); font-size: 0.75rem; margin-top: 4px; }

.mg-grid-4 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
@media (min-width: 700px) { .mg-grid-4 { grid-template-columns: repeat(4, 1fr); } }

/* Featured Products row: rotating product cards + promo tile beside them */
.mg-feature-row { display: grid; grid-template-columns: 1fr; gap: 1rem; }
@media (min-width: 900px) { .mg-feature-row { grid-template-columns: 3fr 1fr; } }
.mg-feature-products { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
@media (min-width: 560px) { .mg-feature-products { grid-template-columns: repeat(4, 1fr); } }

.mg-flip-scene { perspective: 1200px; height: 230px; cursor: pointer; position: relative; overflow: hidden; border-radius: 1rem; }
.mg-flip-inner { position: relative; width: 100%; height: 100%; transform-style: preserve-3d; -webkit-transform-style: preserve-3d; transition: transform 1s cubic-bezier(.45,.05,.15,1); will-change: transform; }
.mg-flip-inner.flipped { transform: rotateY(180deg) !important; }
.mg-flip-face { position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden; transform-style: preserve-3d; -webkit-transform-style: preserve-3d; overflow: hidden;
  background: var(--mg-card); border-radius: 1rem;
  box-shadow: 0 0 0 1px rgba(0,0,0,0.08), 0 5px 0 0 rgba(0,0,0,0.06), 0 14px 26px rgba(0,0,0,0.14); }
.mg-flip-back { transform: rotateY(180deg); }
.mg-pcard-img { position: relative; height: 65%; background: #f0f2f5; }
.mg-pcard-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.mg-pcard-badge { position: absolute; top: 8px; left: 8px; background: var(--mg-blue); color: #fff; font-size: 0.6rem; font-weight: 800; letter-spacing: .03em; padding: 3px 8px; border-radius: 4px; }
.mg-pcard-heart { position: absolute; top: 8px; right: 8px; width: 1.6rem; height: 1.6rem; border-radius: 50%; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; color: #16181d; }
.mg-pcard-body { padding: 0.6rem 0.75rem; }
.mg-pcard-title { font-size: 0.8rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mg-pcard-price { font-weight: 800; color: var(--mg-blue); font-size: 0.9rem; }
.mg-pcard-old { text-decoration: line-through; color: var(--mg-muted); font-size: 0.72rem; margin-left: 4px; font-weight: 500; }

.mg-flip-scene::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 5;
  pointer-events: none;
  background: linear-gradient(75deg, transparent 40%, rgba(255,255,255,0.55) 50%, transparent 60%);
  transform: translateX(-160%) skewX(-15deg);
  opacity: 0;
}
.mg-flip-scene.flipping::after { animation: mgShine 1s ease forwards; }
@keyframes mgShine {
  0%   { transform: translateX(-160%) skewX(-15deg); opacity: 0; }
  15%  { opacity: 0.9; }
  55%  { opacity: 0.9; }
  100% { transform: translateX(160%) skewX(-15deg); opacity: 0; }
}

/* Promo tile (MacBook Air / Save 35% style) */
.mg-promo-tile { background: linear-gradient(160deg, #2563eb 0%, #1d4ed8 100%); border-radius: 1rem; padding: 1.5rem; color: #fff; display: flex; flex-direction: column; justify-content: space-between; min-height: 230px; text-decoration: none; position: relative; overflow: hidden; }
.mg-promo-eyebrow { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; opacity: 0.85; }
.mg-promo-title { font-size: 1.15rem; font-weight: 800; margin-top: 0.3rem; max-width: 12ch; }
.mg-promo-cta { align-self: flex-start; background: #fff; color: #1d4ed8; font-weight: 700; font-size: 0.78rem; padding: 0.45rem 1rem; border-radius: 999px; margin-top: 0.75rem; }

/* Category tiles row */
.mg-cat-tiles { display: grid; grid-template-columns: 1fr; gap: 1rem; margin: 1.5rem 0; }
@media (min-width: 700px) { .mg-cat-tiles { grid-template-columns: repeat(3, 1fr); } }
.mg-cat-tile { background: var(--mg-card); border: 1px solid var(--mg-border); border-radius: 1rem; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; text-decoration: none; color: var(--mg-text); gap: 0.75rem; }
.mg-cat-tile-label { font-weight: 800; font-size: 1rem; }
.mg-cat-tile-from { font-size: 0.78rem; color: var(--mg-muted); margin: 2px 0 0.5rem; }
.mg-cat-tile-cta { font-size: 0.75rem; font-weight: 700; color: var(--mg-blue); }
.mg-cat-tile-icon-wrap { width: 3.5rem; height: 3.5rem; border-radius: 0.75rem; background: color-mix(in srgb, var(--mg-blue) 10%, transparent); display: flex; align-items: center; justify-content: center; color: var(--mg-blue); flex-shrink: 0; }
</style>

<div class="mg-page">
    <button type="button" class="mg-theme-toggle" onclick="
        var html = document.documentElement;
        var isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    ">Toggle Theme</button>

    {{-- Hero slider --}}
    <div class="mg-hero" id="mgHero">
        @foreach($heroSlides as $i => $slide)
        <a href="#mgListings" class="mg-slide {{ $i === 0 ? 'active' : '' }}">
            <img src="https://picsum.photos/seed/{{ $slide['seed'] }}/900/500" alt="{{ $slide['title'] }}">
            <div class="mg-slide-overlay">
                <div>
                    <p class="mg-eyebrow">{{ $slide['eyebrow'] }}</p>
                    <p class="mg-slide-title">{{ $slide['title'] }}<br><span class="hl">{{ $slide['highlight'] }}</span></p>
                    <p class="mg-slide-sub">{{ $slide['sub'] }}</p>
                </div>
                <span class="mg-slide-cta">{{ $slide['cta'] }}</span>
            </div>
        </a>
        @endforeach
        <div class="mg-dots">
            @foreach($heroSlides as $i => $slide)
            <button type="button" class="mg-dot {{ $i === 0 ? 'active' : '' }}" onclick="mgShowSlide({{ $i }})"></button>
            @endforeach
        </div>
    </div>

    <div class="mg-content">
        <div class="mg-pills">
            <a href="{{ route('marketplace.index') }}" class="mg-pill {{ !$activeType ? 'active' : '' }}">All Types</a>
            @foreach(['item' => 'Items', 'service' => 'Services', 'rent' => 'Rentals', 'store' => 'Stores'] as $val => $label)
            <a href="{{ route('marketplace.index', ['type' => $val]) }}" class="mg-pill {{ $activeType == $val ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="mg-pills">
            <a href="{{ route('marketplace.index') }}" class="mg-pill {{ !$activeCategory ? 'active' : '' }}">All Categories</a>
            @foreach($categoryIcons as $cat => $icon)
            <a href="{{ route('marketplace.index', ['category' => $cat]) }}" class="mg-pill {{ $activeCategory == $cat ? 'active' : '' }}">{{ $cat }}</a>
            @endforeach
        </div>

        {{-- Quick action row --}}
        <div class="mg-quick-grid">
            @foreach($quickActions as $qa)
            <a href="{{ route('marketplace.index') }}" class="mg-quick-tile">
                <div class="mg-quick-icon" style="background:{{ $qa['color'] }};"><i data-lucide="{{ $qa['icon'] }}" class="w-5 h-5"></i></div>
                <div>
                    <p class="mg-quick-label">{{ $qa['label'] }}</p>
                    <p class="mg-quick-sub">Shop Now →</p>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Featured Products — rotating queue. Every 15s with no new product,
             cards rotate among themselves. When a real new product is added
             (call window.mgAddFeaturedProduct(item) from your controller/JS),
             it enters at card 1 and every other card shifts down one slot. --}}
        <div class="mg-section-head">
            <div>
                <p class="mg-section-title">Featured Products</p>
                <p class="mg-section-sub">New picks rotate in automatically</p>
            </div>
            <a href="{{ route('register') }}" class="mg-section-link">All Listings →</a>
        </div>
        <div class="mg-feature-row">
            <div class="mg-feature-products" id="mgFeaturedGrid">
                @foreach($featuredPool as $p)
                <div>
                    <div class="mg-flip-scene" onclick="mgManualAdvance(this)">
                        <div class="mg-flip-inner">
                            <div class="mg-flip-face mg-pcard-face-a">
                                <div class="mg-pcard-img">
                                    <span class="mg-pcard-badge">{{ $p['badge'] }}</span>
                                    <span class="mg-pcard-heart"><i data-lucide="heart" class="w-3.5 h-3.5"></i></span>
                                    <img src="https://picsum.photos/seed/{{ $p['seed'] }}/300/300" alt="{{ $p['title'] }}">
                                </div>
                                <div class="mg-pcard-body">
                                    <p class="mg-pcard-title">{{ $p['title'] }}</p>
                                    <p><span class="mg-pcard-price">₦{{ number_format($p['price']) }}</span>@if($p['oldPrice'])<span class="mg-pcard-old">₦{{ number_format($p['oldPrice']) }}</span>@endif</p>
                                </div>
                            </div>
                            <div class="mg-flip-face mg-flip-back mg-pcard-face-b"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('register') }}" class="mg-promo-tile">
                <div>
                    <p class="mg-promo-eyebrow">Featured</p>
                    <p class="mg-promo-title">Sign up to sell on Campulsy</p>
                </div>
                <span class="mg-promo-cta">Get Started</span>
            </a>
        </div>

        {{-- Just Arrived --}}
        <div class="mg-section-head">
            <div>
                <p class="mg-section-title">Just Arrived</p>
                <p class="mg-section-sub">New on the marketplace this week</p>
            </div>
        </div>
        <div class="mg-grid-4">
            @foreach($justArrived as $item)
            <div class="mg-card">
                <div class="mg-card-img">
                    <span class="mg-badge">{{ ucfirst($item->listing_type ?? 'item') }}</span>
                </div>
                <div class="mg-card-body">
                    <p class="mg-price">₦{{ number_format($item->amount) }}</p>
                    <p class="mg-title">{{ $item->title }}</p>
                    <p class="mg-loc">{{ $item->location ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Category tiles --}}
        <div class="mg-cat-tiles">
            @foreach($catTiles as $ct)
            <a href="{{ route('marketplace.index') }}" class="mg-cat-tile">
                <div>
                    <p class="mg-cat-tile-label">{{ $ct['label'] }}</p>
                    <p class="mg-cat-tile-from">From ₦{{ number_format($ct['from']) }}</p>
                    <span class="mg-cat-tile-cta">Shop Now →</span>
                </div>
                <div class="mg-cat-tile-icon-wrap"><i data-lucide="{{ $ct['icon'] }}" class="w-6 h-6"></i></div>
            </a>
            @endforeach
        </div>

        {{-- Second Featured Products row (plain, no flip) --}}
        <div class="mg-section-head">
            <div>
                <p class="mg-section-title">Featured Products</p>
                <p class="mg-section-sub">More picks from around campus</p>
            </div>
        </div>
        <div class="mg-grid-4">
            @foreach($secondFeatured as $item)
            <div class="mg-card">
                <div class="mg-card-img">
                    <span class="mg-badge">{{ ucfirst($item->listing_type ?? 'item') }}</span>
                </div>
                <div class="mg-card-body">
                    <p class="mg-price">₦{{ number_format($item->amount) }}</p>
                    <p class="mg-title">{{ $item->title }}</p>
                    <p class="mg-loc">{{ $item->location ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- All Listings --}}
        <div class="mg-section-head" id="mgListings">
            <div>
                <p class="mg-section-title">All Listings</p>
                <p class="mg-section-sub">Browse everything on the marketplace</p>
            </div>
        </div>
        <div class="mg-grid-4">
            @foreach($displayListings as $item)
            <a href="{{ $listings->isNotEmpty() ? route('marketplace.show', $item->id) : route('register') }}" class="mg-card">
                <div class="mg-card-img">
                    @if(!empty($item->image_url))
                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                    @endif
                    <span class="mg-badge">{{ ucfirst($item->listing_type ?? 'item') }}</span>
                </div>
                <div class="mg-card-body">
                    <p class="mg-price">₦{{ number_format($item->amount) }}</p>
                    <p class="mg-title">{{ $item->title }}</p>
                    <p class="mg-loc">{{ $item->location ?? '' }}</p>
                </div>
            </a>
            @endforeach
            @if($listings->isEmpty())
            <p class="mg-loc" style="grid-column: 1/-1;">No listings yet — showing sample items above.</p>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    var slides = document.querySelectorAll('#mgHero .mg-slide');
    var dots = document.querySelectorAll('#mgHero .mg-dot');
    var current = 0;
    window.mgShowSlide = function (i) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = i;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    };
    if (slides.length > 1) {
        setInterval(function () { mgShowSlide((current + 1) % slides.length); }, 4500);
    }

    // ── Featured Products rotating carousel ─────────────────────────
    // pool[0..n] is the ordered list of products. Card i always shows
    // pool[i]. Self-rotate moves every item left by one (wraps around) —
    // no new content, just reshuffles what's already there, every 15s.
    // Adding a real new product unshifts it to pool[0] and every card
    // cascades down one slot, exactly like a news ticker.
    var pool = @json(array_values($featuredPool));
    var cardEls = Array.from(document.querySelectorAll('#mgFeaturedGrid .mg-flip-scene'));

    function mgRenderFaceHTML(item) {
        var oldPriceHtml = item.oldPrice ? '<span class="mg-pcard-old">₦' + Number(item.oldPrice).toLocaleString() + '</span>' : '';
        return '<div class="mg-pcard-img">' +
                    '<span class="mg-pcard-badge">' + item.badge + '</span>' +
                    '<span class="mg-pcard-heart"><i data-lucide="heart" class="w-3.5 h-3.5"></i></span>' +
                    '<img src="https://picsum.photos/seed/' + item.seed + '/300/300" alt="' + item.title + '">' +
                '</div>' +
                '<div class="mg-pcard-body">' +
                    '<p class="mg-pcard-title">' + item.title + '</p>' +
                    '<p><span class="mg-pcard-price">₦' + Number(item.price).toLocaleString() + '</span>' + oldPriceHtml + '</p>' +
                '</div>';
    }

    function mgUpdateCard(scene, item) {
        var inner = scene.querySelector('.mg-flip-inner');
        var isFlipped = inner.classList.contains('flipped');
        var hiddenFace = isFlipped ? scene.querySelector('.mg-pcard-face-a') : scene.querySelector('.mg-pcard-face-b');
        hiddenFace.innerHTML = mgRenderFaceHTML(item);
        scene.classList.add('flipping');
        inner.classList.toggle('flipped');
        setTimeout(function () { scene.classList.remove('flipping'); if (window.lucide) lucide.createIcons(); }, 1000);
    }

    function mgCascadeRender(staggerMs) {
        cardEls.forEach(function (scene, i) {
            var item = pool[i];
            if (!item) return;
            setTimeout(function () { mgUpdateCard(scene, item); }, i * staggerMs);
        });
    }

    // Manual tap: advance just that one card to the next item in the pool.
    window.mgManualAdvance = function (scene) {
        var i = cardEls.indexOf(scene);
        if (i === -1) return;
        var nextItem = pool[(i + 1) % pool.length];
        mgUpdateCard(scene, nextItem);
    };

    // Ready for real backend integration: call this whenever a genuinely
    // new featured product is created. Example:
    //   mgAddFeaturedProduct({badge:'NEW', title:'...', price:1000, oldPrice:null, seed:'xyz'});
    window.mgAddFeaturedProduct = function (item) {
        pool.unshift(item);
        if (pool.length > cardEls.length) pool.pop();
        mgCascadeRender(250);
    };

    function mgSelfRotate() {
        pool.push(pool.shift());
        mgCascadeRender(250);
    }

    if (cardEls.length > 1) {
        setInterval(mgSelfRotate, 15000);
    }
})();
</script>

@endsection
