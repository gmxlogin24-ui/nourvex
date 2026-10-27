(function () {
  var els = [].slice.call(document.querySelectorAll('.hw-top,.hw-market,.hw-q'));
  if (!els.length) return;
  var STAY = 4000, timers = [], last = 0;
  function tuck(e) { e.classList.add('hw-away'); }
  function reveal(e) {
    e.classList.remove('hw-away');
    var i = els.indexOf(e); clearTimeout(timers[i]);
    timers[i] = setTimeout(function () { tuck(e); }, STAY);
  }
  els.forEach(tuck);
  els.forEach(function (e) {
    e.addEventListener('click', function (ev) {
      if (e.classList.contains('hw-away')) { ev.preventDefault(); ev.stopPropagation(); reveal(e); }
    }, true);
  });
  document.addEventListener('pointerdown', function (ev) {
    els.forEach(function (e) { if (!e.contains(ev.target)) tuck(e); });
  }, { passive: true });
  window.addEventListener('scroll', function () {
    var n = Date.now(); if (n - last < 150) return; last = n;
    els.forEach(tuck);
  }, { capture: true, passive: true });
})();
