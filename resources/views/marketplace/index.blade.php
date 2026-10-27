@extends('layouts.app-authenticated')

@section('world', 'market')
@section('title', 'Campus Market')

@section('content')
@php
  $mkList = $listings instanceof \Illuminate\Pagination\AbstractPaginator ? $listings->getCollection() : collect($listings);
  $cats = [
    'Fashion'     => ['shirt', '#F97316'],
    'Electronics' => ['cpu', '#2563EB'],
    'Books'       => ['book', '#16A34A'],
    'Food'        => ['utensils', '#E11D48'],
    'Services'    => ['wrench', '#7C5CF0'],
    'Rentals'     => ['key', '#0EA5A4'],
    'Other'       => ['grid', '#64748B'],
  ];
  $types = ['item' => 'Items', 'service' => 'Services', 'rent' => 'Rentals', 'store' => 'Stores'];
  $q = trim((string) request('q'));
  $cat = request('category');
  $type = request('type');
  $filtered = $q !== '' || $cat || $type;
  $imgOf = function ($l) {
      $url = $l->image_url ?? null;
      if (!$url) {
          $imgs = $l->images ?? null;
          $first = ($imgs && method_exists($imgs, 'first')) ? $imgs->first() : null;
          $url = $first ? ($first->url ?? $first->path ?? $first->image_path ?? $first->image_url ?? null) : null;
      }
      if (!$url) return null;
      return preg_match('~^(https?:)?//~', $url) ? $url : asset(ltrim($url, '/'));
  };
  $mkLoved = auth()->check() ? \Illuminate\Support\Facades\DB::table('listing_favorites')->where('user_id', auth()->id())->pluck('listing_id')->all() : [];
  $mkVerified = \App\Models\User::whereIn('id', $mkList->pluck('user_id')->filter()->unique()->values())->where('is_verified_seller', true)->pluck('id')->all();
  $mkRates = \App\Models\Review::where('reviewable_type', \App\Models\Listing::class)->whereIn('reviewable_id', $mkList->pluck('id'))->selectRaw('reviewable_id, AVG(rating) as a, COUNT(*) as c')->groupBy('reviewable_id')->get()->keyBy('reviewable_id');

  $recent = collect();
  $top = collect();
  if (!$filtered) {
    try {
      if (auth()->check()) {
        $rIds = \App\Models\ListingView::where('user_id', auth()->id())->orderByDesc('updated_at')->limit(40)->pluck('listing_id')->unique()->take(10)->values()->all();
        if ($rIds) {
          $recent = \App\Models\Listing::whereIn('id', $rIds)->where('status', 'available')->get()->sortBy(fn ($l) => array_search($l->id, $rIds))->values();
        }
      }
    } catch (\Throwable $e) { $recent = collect(); }
    try {
      $tIds = \App\Models\ListingOrder::selectRaw('listing_id, COUNT(*) as c')->groupBy('listing_id')->orderByDesc('c')->take(10)->pluck('listing_id')->all();
      $top = $mkList->whereIn('id', $tIds)->sortBy(fn ($l) => array_search($l->id, $tIds))->values();
    } catch (\Throwable $e) { $top = collect(); }
  }
@endphp
<link rel="stylesheet" href="{{ asset('css/mk2.css') }}?v={{ time() }}">
<div class="mk2" id="mk2" data-token="{{ csrf_token() }}">

  
  <section class="mk2-sec">
    <div class="mk2-sh"><b>Shop by category</b></div>
    <div class="mk2-cats">
      @foreach($cats as $name => $meta)
        <a href="{{ route('marketplace.index', ['category' => $name]) }}" class="mk2-cat {{ $cat === $name ? 'on' : '' }}"><span style="background:{{ $meta[1] }}"><i data-lucide="{{ $meta[0] }}"></i></span><small>{{ $name }}</small></a>
      @endforeach
      <a href="{{ route('marketplace.index', ['type' => 'store']) }}" class="mk2-cat {{ $type === 'store' ? 'on' : '' }}"><span style="background:#CA8A04"><i data-lucide="store"></i></span><small>Stores</small></a>
    </div>
  </section>

  @if($filtered)
    <section class="mk2-sec">
      <div class="mk2-sh">
        <b>{{ $q !== '' ? 'Results for “' . $q . '”' : ($cat ?: ($types[$type] ?? 'Results')) }}</b>
        <a href="{{ route('marketplace.index') }}" class="mk2-clear">Clear</a>
      </div>
      <p class="mk2-n">{{ $mkList->count() }} {{ $mkList->count() === 1 ? 'listing' : 'listings' }}</p>
      @if($mkList->count())
        <div class="mk2-grid">
          @foreach($mkList as $item)
            @include('marketplace.partials.mk2-card')
          @endforeach
        </div>
      @else
        <div class="mk2-empty"><b>Nothing found</b>Try another word or category.</div>
      @endif
    </section>
  @else
    @include('marketplace.partials.mk2-rail', ['rTitle' => 'Recently viewed', 'rItems' => $recent])
    @include('marketplace.partials.mk2-rail', ['rTitle' => 'Just arrived', 'rItems' => $mkList->sortByDesc('created_at')->take(10)->values()])
    @include('marketplace.partials.mk2-rail', ['rTitle' => 'Top sellers', 'rItems' => $top])
    @foreach($mkList->groupBy('category') as $cName => $group)
      @include('marketplace.partials.mk2-rail', ['rTitle' => ($cName ?: 'Other'), 'rLink' => route('marketplace.index', ['category' => ($cName ?: 'Other')]), 'rItems' => $group->take(10)->values()])
    @endforeach
    @if(!$mkList->count())
      <div class="mk2-empty"><b>Nothing listed here yet</b>Be the first to sell.</div>
    @endif
  @endif
</div>

<script>
(function () {
  var g = document.getElementById('mk2');
  if (!g) return;
  function cart(n) {
    document.querySelectorAll('[aria-label="Cart"]').forEach(function (c) {
      var em = c.querySelector('.nv-badge');
      if (n > 0) {
        if (!em && c.classList.contains('pb-ic')) { em = document.createElement('em'); em.className = 'nv-badge'; c.appendChild(em); }
        if (em) em.textContent = n;
      } else if (em) { em.remove(); }
    });
  }
  g.addEventListener('click', function (e) {
    var b = e.target.closest('.mk2-heart');
    if (!b) return;
    e.preventDefault();
    if (b.dataset.busy) return;
    b.dataset.busy = '1';
    fetch(b.dataset.url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': g.dataset.token, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (typeof d.loved === 'boolean') { b.classList.toggle('on', d.loved); cart(d.cart_count); } })
      .catch(function () {})
      .then(function () { delete b.dataset.busy; });
  });
})();
</script>
@endsection
