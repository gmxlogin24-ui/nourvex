@php
  $wmSource = ($recommended ?? collect())->count() >= 2 ? $recommended : ($listings ?? collect());
  $wmItems = collect($wmSource)->take(6)->map(function ($l) {
      $url = $l->image_url ?? null;
      if (!$url && isset($l->images) && method_exists($l->images, 'first') && $l->images->first()) {
          $f = $l->images->first();
          $url = $f->url ?? $f->path ?? $f->image_path ?? $f->image_url ?? null;
      }
      if ($url && !preg_match('~^(https?:)?//~', $url)) { $url = asset(ltrim($url, '/')); }
      return [
          'id' => $l->id,
          'title' => $l->title,
          'image_url' => $url,
          'description' => (string) ($l->description ?? ''),
          'price' => (float) ($l->amount ?? 0),
          'category' => $l->category ?? null,
          'store' => $l->store_name ?? null,
          'location' => $l->location ?? null,
          'verified' => (bool) ($l->is_verified_seller ?? false),
      ];
  })->values();
  if ($wmItems->isEmpty()) {
      $wmItems = collect([
          ['id' => null, 'title' => 'List your first item', 'image_url' => null, 'description' => 'Sell items, services or rentals to students on campus. Your listing could appear here.', 'price' => 0, 'category' => null, 'store' => null, 'location' => null, 'verified' => false],
          ['id' => null, 'title' => 'Find campus deals', 'image_url' => null, 'description' => 'Fashion, electronics, books and food from students near you.', 'price' => 0, 'category' => null, 'store' => null, 'location' => null, 'verified' => false],
          ['id' => null, 'title' => 'Rent what you need', 'image_url' => null, 'description' => 'Borrow or rent gear for a day, a week or a semester.', 'price' => 0, 'category' => null, 'store' => null, 'location' => null, 'verified' => false],
      ]);
  }
  $wmFeat = $wmItems->first();
  $wmStackItems = $wmItems->map(fn ($i) => ['title' => $i['title'], 'image_url' => $i['image_url']])->values();
@endphp
<link rel="stylesheet" href="{{ asset('css/sponsor-stack.css') }}?v={{ time() }}">
<script src="{{ asset('js/sponsor-stack.js') }}?v={{ time() }}" defer></script>
<section class="wm-stack-row" id="wmRow">
  <div class="wm-write" id="wmWrite">
    <p class="wm-eyebrow">Sponsored pick</p>
    <h3 id="wmTitle">{{ $wmFeat['title'] }}</h3>
    <p class="wm-price" id="wmPrice">{{ $wmFeat['price'] ? '₦' . number_format($wmFeat['price']) : '' }}</p>
    <p class="wm-meta" id="wmMeta"></p>
    <p class="wm-desc" id="wmDesc">{{ \Illuminate\Support\Str::limit($wmFeat['description'], 320) }}</p>
    <a href="{{ $wmFeat['id'] ? url('marketplace/' . $wmFeat['id']) : route('marketplace.create') }}" class="wm-go" id="wmGo">{{ $wmFeat['id'] ? 'View listing' : 'Start selling' }}</a>
  </div>
  @include('partials.sponsor-stack', ['items' => $wmStackItems, 'tagLabel' => 'Sponsored', 'compact' => false])
</section>
<script>
(function () {
  var items = @json($wmItems, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
  var createUrl = @json(route('marketplace.create'));
  var base = @json(url('marketplace'));
  var row = document.getElementById('wmRow');
  var write = document.getElementById('wmWrite');
  if (!row) return;
  var last = -1;

  function frontIndex() {
    var cards = [].slice.call(row.querySelectorAll('.sponsor-card'));
    for (var i = 0; i < cards.length; i++) {
      if (cards[i].classList.contains('front')) return i;
    }
    return 0;
  }

  function fill(i) {
    var it = items[i]; if (!it) return;
    document.getElementById('wmTitle').textContent = it.title;
    document.getElementById('wmPrice').textContent = it.price ? '₦' + Number(it.price).toLocaleString() : '';
    var parts = [];
    if (it.category) parts.push(it.category);
    if (it.store) parts.push(it.store);
    if (it.location) parts.push(it.location);
    var meta = document.getElementById('wmMeta');
    meta.textContent = parts.join(' · ');
    if (it.verified) {
      var v = document.createElement('span');
      v.className = 'wm-verified';
      v.textContent = (parts.length ? ' · ' : '') + 'Verified seller';
      meta.appendChild(v);
    }
    document.getElementById('wmDesc').textContent = it.description || 'No description added yet.';
    var go = document.getElementById('wmGo');
    if (it.id) { go.href = base + '/' + it.id; go.textContent = 'View listing'; }
    else { go.href = createUrl; go.textContent = 'Start selling'; }
  }

  function sync() {
    var idx = frontIndex();
    if (idx === last) return;
    last = idx;
    write.classList.add('swap');
    setTimeout(function () { fill(idx); write.classList.remove('swap'); }, 220);
  }

  document.addEventListener('DOMContentLoaded', function () {
    fill(0);
    sync();
    new MutationObserver(sync).observe(row, { attributes: true, subtree: true, attributeFilter: ['class'] });
  });
})();
</script>
