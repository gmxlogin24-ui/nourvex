(function () {
  var b = document.body;
  if (!b || !(b.classList.contains('ws2-gigs') || b.classList.contains('ws2-market'))) return;
  var main = document.querySelector('.ws2-main');
  if (!main) return;
  var skip = { INPUT:1, TEXTAREA:1, SELECT:1, BUTTON:1, IMG:1, SVG:1, SCRIPT:1, STYLE:1, I:1 };
  function scan(root) {
    var els = root.querySelectorAll('*');
    for (var i = 0; i < els.length; i++) {
      var el = els[i];
      if (skip[el.tagName] || el.classList.contains('wg-glass') || el.closest('.nv-dock-fixed')) continue;
      var r = el.getBoundingClientRect();
      if (r.width < 140 || r.height < 56) continue;
      var cs = getComputedStyle(el);
      if (cs.position === 'fixed') continue;
      var m = cs.backgroundColor.match(/rgba?\(([^)]+)\)/);
      if (!m) continue;
      var p = m[1].split(',').map(parseFloat);
      if (p[0] >= 250 && p[1] >= 250 && p[2] >= 250 && (p.length < 4 || p[3] >= 0.9) && parseFloat(cs.borderTopLeftRadius) >= 10) {
        el.classList.add('wg-glass');
      }
    }
  }
  var t;
  function sched() { clearTimeout(t); t = setTimeout(function () { scan(main); }, 120); }
  scan(main);
  window.addEventListener('load', sched);
  new MutationObserver(sched).observe(main, { childList: true, subtree: true });
})();
