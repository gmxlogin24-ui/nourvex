{{-- Swipe from a screen edge to change world.
     Swipe left from the right edge: next world. Swipe right from the left edge: previous world. --}}
@php
  $hwWorlds = [route('dashboard'), route('gigs.index'), route('marketplace.index')];
@endphp
<script>
(function () {
  var W = @json($hwWorlds);
  var EDGE = 24, MIN = 70;
  var here = location.pathname.replace(/\/$/, '');
  var idx = -1;
  W.forEach(function (u, i) {
    try { if (new URL(u, location.href).pathname.replace(/\/$/, '') === here) idx = i; } catch (e) {}
  });
  var sx = 0, sy = 0, side = null, active = false;

  function go(n) {
    var len = W.length;
    location.href = W[((n % len) + len) % len];
  }

  window.addEventListener('pointerdown', function (e) {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    var w = window.innerWidth;
    if (e.clientX <= EDGE) side = 'left';
    else if (e.clientX >= w - EDGE) side = 'right';
    else { side = null; return; }
    sx = e.clientX; sy = e.clientY; active = true;
  }, { passive: true });

  window.addEventListener('pointerup', function (e) {
    if (!active) return;
    active = false;
    var dx = e.clientX - sx, dy = e.clientY - sy;
    if (Math.abs(dx) < MIN || Math.abs(dx) < Math.abs(dy) * 1.5) return;
    if (side === 'right' && dx < 0) go(idx + 1);
    if (side === 'left' && dx > 0) go(idx < 0 ? W.length - 1 : idx - 1);
  }, { passive: true });

  window.addEventListener('pointercancel', function () { active = false; });
})();
</script>
