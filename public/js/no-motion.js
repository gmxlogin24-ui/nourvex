(function () {
  var g = window.gsap;
  if (!g || window.__nvNoMotion) return;
  window.__nvNoMotion = true;
  function flat(v) {
    if (v && typeof v === 'object') { v.duration = 0; v.delay = 0; v.stagger = 0; v.repeat = 0; v.repeatDelay = 0; v.yoyo = false; }
    return v;
  }
  var to = g.to, from = g.from, fromTo = g.fromTo;
  g.to = function (t, v) { return to.call(g, t, flat(v)); };
  g.from = function (t, v) { return from.call(g, t, flat(v)); };
  g.fromTo = function (t, a, b) { return fromTo.call(g, t, a, flat(b)); };
})();
