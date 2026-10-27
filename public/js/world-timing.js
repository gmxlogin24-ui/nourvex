(function () {
  if (window.WorldTiming) return;
  var t0 = performance.now();
  var loadMs = null;
  /* how long the page took to become ready (fast = small, slow = large) */
  function measure() { if (loadMs === null) loadMs = performance.now() - t0; }
  if (document.readyState === 'complete') measure(); else window.addEventListener('load', measure);

  /* maps the load time to a scale: 0.6 for fast loads, up to 1.8 for slow ones */
  function scale() {
    var ms = loadMs === null ? 800 : loadMs;
    return Math.min(1.8, Math.max(0.6, ms / 900));
  }
  window.WorldTiming = {
    /* duration in seconds, scaled by the measured load speed */
    dur: function (base) { return base * scale(); },
    /* stagger in seconds, scaled the same way */
    stagger: function (base) { return base * scale(); },
    scale: scale,
    loadMs: function () { return loadMs; }
  };
})();
