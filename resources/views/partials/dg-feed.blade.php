@php
  $fq = fn () => \App\Models\News::query()->withCount(['comments', 'likes'])->withExists(['likes as liked' => fn ($l) => $l->where('user_id', auth()->id() ?? 0)]);
  $fLive = $fq()->live()->orderByDesc('is_featured')->orderByDesc('published_at')->take(24)->get();
  $fSoon = $fq()->where('published_at', '>', now())->orderBy('published_at')->take(8)->get();
  $fPosts = $fLive->concat($fSoon);
@endphp
<link rel="stylesheet" href="{{ asset('css/dg-feed.css') }}?v={{ filemtime(public_path('css/dg-feed.css')) }}">
<section class="dgf" id="dgFeed" aria-label="Pulse">
  <div class="dgf-head"><div><h4>Pulse</h4><p>News, offers and what is coming.</p></div><button type="button" id="pulsePostBtn" class="dgf-post">+ Post</button></div>
  <div class="dgf-tabs" role="tablist">
    @foreach(['all' => 'All', 'news' => 'News', 'ads' => 'Ads', 'upcoming' => 'Upcoming'] as $k => $l)
    <button type="button" class="dgf-tab {{ $k === 'all' ? 'on' : '' }}" data-tab="{{ $k }}" role="tab">{{ $l }}</button>
    @endforeach
  </div>
  <div class="dgf-list">
    @foreach($fPosts as $p)
    @php
      $soon = $p->published_at && $p->published_at->isFuture();
      $ad = $p->type === 'ad';
      $kind = $soon ? 'soon' : ($ad ? 'ad' : 'news');
      $cat = $ad ? ($p->category ?? 'Original') : ($p->category ?: 'Campus News');
    @endphp
    <article class="dgf-card" data-kind="{{ $kind }}" data-id="{{ $p->id }}" data-img="{{ $p->image_url }}" data-video="{{ $p->video_url }}" data-body="{{ $p->body }}">
      <div class="dgf-img" @if($p->image_url) style="background-image:url('{{ $p->image_url }}')" @endif>@if(!$p->image_url && $p->video_url)<span class="dgf-play"></span>@endif</div>
      <div class="dgf-body">
        <div class="dgf-eye"><i></i>{{ $cat }}@if($ad)<em>Sponsored</em>@endif @if($soon)<em>Upcoming</em>@endif</div>
        <h5>{{ $p->title }}</h5>
        <p>{{ \Illuminate\Support\Str::limit($p->body, 110) }}</p>
        <div class="dgf-meta">
          <span>{{ $soon ? 'Drops ' : '' }}{{ $p->published_at?->diffForHumans() ?? 'Just now' }}</span>
          @unless($soon)
          <button type="button" class="dgf-like {{ ($p->liked ?? false) ? 'on' : '' }}" data-id="{{ $p->id }}" aria-label="Like"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 21s-7.5-4.6-10-9.3C.6 8.1 2.4 4.5 6 4.1c2-.2 3.6.9 6 3.4 2.4-2.5 4-3.6 6-3.4 3.6.4 5.4 4 4 7.6C19.5 16.4 12 21 12 21z"/></svg><b>{{ $p->likes_count ?? 0 }}</b></button>
          <button type="button" class="dgf-cm" aria-label="Comments"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4A9 9 0 0 1 3 15.4 8.4 8.4 0 0 1 11.4 3 8.4 8.4 0 0 1 21 11.5z"/></svg><b data-n="{{ $p->id }}">{{ $p->comments_count ?? 0 }}</b></button><button type="button" class="pf-cm" data-id="{{ $p->id }}" style="display:none" tabindex="-1" aria-hidden="true"></button>
          @if($p->source_url)<a class="dgf-go" href="{{ route('pulse.go', $p) }}" target="_blank" rel="noopener{{ $ad ? ' sponsored' : '' }}">{{ $ad ? 'Learn more' : 'Read' }}</a>@endif
          @endunless
        </div>
      </div>
    </article>
    @endforeach
  </div>
  <p class="dgf-empty" id="dgfEmpty" hidden>Nothing here yet.</p>
</section>
<script>
(function () {
  var root = document.getElementById('dgFeed'); if (!root) return;
  var tabs = root.querySelectorAll('.dgf-tab'), cards = root.querySelectorAll('.dgf-card'), empty = document.getElementById('dgfEmpty');
  var CSRF = '{{ csrf_token() }}';
  function apply(tab) {
    var shown = 0, lead = false;
    cards.forEach(function (c) {
      var k = c.dataset.kind;
      var ok = tab === 'all' ? k !== 'soon' : tab === 'news' ? k === 'news' : tab === 'ads' ? k === 'ad' : k === 'soon';
      c.hidden = !ok; c.classList.remove('dgf-lead');
      if (ok) { shown++; if (!lead) { c.classList.add('dgf-lead'); lead = true; } }
    });
    if (empty) empty.hidden = shown > 0;
  }
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.remove('on'); });
      t.classList.add('on'); apply(t.dataset.tab);
    });
  });
  apply('all');
  root.addEventListener('click', function (e) { var c = e.target.closest('.dgf-cm'); if (!c) return; var h = c.closest('.dgf-card').querySelector('.pf-cm'); if (h) h.click(); });
  root.addEventListener('click', function (e) {
    var b = e.target.closest('.dgf-like'); if (!b) return;
    fetch('/pulse/' + b.dataset.id + '/like', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw 0; return r.json(); })
      .then(function (j) { b.classList.toggle('on', !!j.liked); b.querySelector('b').textContent = j.count; })
      .catch(function () {});
  });
  if ('IntersectionObserver' in window) {
    var seen = {};
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        var id = e.target.dataset.id;
        if (e.isIntersecting && !seen[id] && e.target.dataset.kind !== 'soon') {
          seen[id] = 1;
          fetch('/pulse/' + id + '/view', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, credentials: 'same-origin' }).catch(function () {});
        }
      });
    }, { threshold: 0.6 });
    cards.forEach(function (c) { io.observe(c); });
  }
})();
</script>

<link rel="stylesheet" href="{{ asset('css/dg-viewer.css') }}?v={{ filemtime(public_path('css/dg-viewer.css')) }}">
<script src="{{ asset('js/dg-viewer.js') }}?v={{ filemtime(public_path('js/dg-viewer.js')) }}" defer></script>
