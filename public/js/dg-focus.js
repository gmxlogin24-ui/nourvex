(function () {
  var raf = 0;
  function cards() { return [].slice.call(document.querySelectorAll('#dgFeed .dgf-card')); }
  function topEdge() {
    var pb = document.querySelector('.pb-bar'), r = pb ? pb.getBoundingClientRect() : null;
    var t = r && r.bottom > 0 ? r.bottom : 64;
    var pin = document.querySelector('.dg-pintabs.show');
    if (pin) t = Math.max(t, pin.getBoundingClientRect().bottom);
    return t;
  }
  function goal(c, zt, vh) {
    if (c.hidden || c.offsetParent === null || c.classList.contains('dgf-lead')) return 0;
    var r = c.getBoundingClientRect();
    var t = (r.top - zt) / (vh - zt), peak = 0.38, g;
    if (t <= 0) g = 0;
    else if (t < peak) g = t / peak;
    else if (t < 0.9) g = 1 - (t - peak) / (0.9 - peak);
    else g = 0;
    g = Math.min(1, Math.max(0, g * 1.5));
    return g * g * (3 - 2 * g);
  }
  function frame() {
    raf = 0;
    var zt = topEdge(), vh = window.innerHeight, busy = false;
    cards().forEach(function (c) {
      var r = c.getBoundingClientRect();
      if (r.bottom < -400 || r.top > vh + 400) return;
      var cur = c._g || 0, tg = goal(c, zt, vh), d = tg - cur;
      if (Math.abs(d) < 0.004) { cur = tg; } else { cur += d * 0.1; busy = true; }
      if (cur !== c._g) { c._g = cur; c.style.setProperty('--g', cur.toFixed(3)); }
    });
    if (busy) raf = requestAnimationFrame(frame);
  }
  function req() { if (!raf) raf = requestAnimationFrame(frame); }
  function init() {
    document.addEventListener('scroll', req, { passive: true, capture: true });
    window.addEventListener('resize', req);
    document.addEventListener('click', function () { setTimeout(req, 120); });
    req();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
