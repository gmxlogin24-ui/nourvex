@php
  $wgItems = collect($gigs ?? [])->sortByDesc('created_at')->take(6)->values()->map(fn($g) => [
    'id' => $g->id, 'title' => $g->title, 'amount' => $g->amount,
    'sub' => '₦' . number_format($g->amount) . ' · ' . ($g->poster_name ?? 'A student'),
    'image_url' => $g->image_url ?? null, 'seed' => 'gig' . $g->id, 'category' => $g->category ?? 'Hustle',
  ]);
  if ($wgItems->isEmpty()) {
    $wgItems = collect([
      ['id' => null, 'title' => 'Post your first hustle', 'sub' => 'Example card · tap to post', 'image_url' => null, 'seed' => 'gigexample1', 'category' => 'Get started'],
      ['id' => null, 'title' => 'Quick cash for campus tasks', 'sub' => 'Example card · tap to post', 'image_url' => null, 'seed' => 'gigexample2', 'category' => 'Hustles'],
      ['id' => null, 'title' => 'Tutoring, printing, errands', 'sub' => 'Example card · tap to post', 'image_url' => null, 'seed' => 'gigexample3', 'category' => 'Ideas'],
    ]);
  }
@endphp
<section class="ws-wrap">
  <div class="ws-deck" id="wsDeck">
    @foreach($wgItems as $s)
    @php $href = $s['id'] ? route('gigs.show', $s['id']) : route('gigs.create'); @endphp
    <a href="{{ $href }}" class="ws-card" draggable="false">
      <img src="{{ $s['image_url'] ?? 'https://picsum.photos/seed/' . $s['seed'] . '/600/800' }}" alt="" draggable="false" loading="lazy">
      <div class="ws-shade"></div>
      <div class="ws-info">
        <span class="ws-eye">{{ $s['category'] }}</span>
        <span class="ws-title">{{ $s['title'] }}</span>
        <span class="ws-sub">{{ $s['sub'] }}</span>
      </div>
    </a>
    @endforeach
  </div>
  <div class="ws-hint">Swipe left or right</div>
  <div class="ws-dots" id="wsDots">@foreach($wgItems as $s)<span></span>@endforeach</div>
</section>
<script>
(function () {
  var deck = document.getElementById('wsDeck'); if (!deck) return;
  var cards = [].slice.call(deck.querySelectorAll('.ws-card'));
  var dots = [].slice.call(document.querySelectorAll('#wsDots span'));
  var order = cards.slice(), sx = 0, dx = 0, drag = false, moved = false, busy = false, idleAt = 0;
  function paint() {
    order.forEach(function (c, p) {
      var k = Math.min(p, 2);
      c.style.transition = 'transform .35s cubic-bezier(.22,1,.36,1), opacity .35s';
      c.style.zIndex = String(20 - p);
      c.style.transform = 'translateY(' + (k * 10) + 'px) scale(' + (1 - k * 0.05) + ')';
      c.style.opacity = p > 2 ? '0' : '1';
      c.style.pointerEvents = p === 0 ? 'auto' : 'none';
    });
    var idx = cards.indexOf(order[0]);
    dots.forEach(function (d, i) { d.classList.toggle('on', i === idx); });
  }
  function fling(dir) {
    if (busy || order.length < 2) return; busy = true;
    var top = order[0];
    top.style.transition = 'transform .4s ease, opacity .4s ease';
    top.style.transform = 'translateX(' + (dir * 140) + '%) rotate(' + (dir * 16) + 'deg)';
    top.style.opacity = '0';
    setTimeout(function () { order.push(order.shift()); busy = false; paint(); }, 400);
  }
  deck.addEventListener('dragstart', function (e) { e.preventDefault(); });
  deck.addEventListener('pointerdown', function (e) {
    var top = order[0];
    idleAt = Date.now();
    if (busy || !top.contains(e.target)) return;
    drag = true; moved = false; sx = e.clientX; dx = 0;
    top.style.transition = 'none';
  });
  window.addEventListener('pointermove', function (e) {
    if (!drag) return;
    dx = e.clientX - sx;
    if (Math.abs(dx) > 6) moved = true;
    order[0].style.transform = 'translateX(' + dx + 'px) rotate(' + (dx / 18) + 'deg)';
  });
  window.addEventListener('pointerup', function () {
    if (!drag) return; drag = false; idleAt = Date.now();
    if (moved && Math.abs(dx) > 70) fling(dx > 0 ? 1 : -1); else paint();
  });
  deck.addEventListener('click', function (e) {
    if (moved) { e.preventDefault(); e.stopPropagation(); }
  }, true);
  // Auto-slide: a random side every ~3.8s, paused while the user is touching or just touched the deck
  setInterval(function () {
    if (drag || busy || Date.now() - idleAt < 2500) return;
    fling(Math.random() < 0.5 ? -1 : 1);
  }, 3800);
  paint();
})();
</script>
