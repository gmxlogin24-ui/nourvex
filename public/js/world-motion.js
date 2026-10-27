(function () {
  if (window.__worldMotion) return;
  window.__worldMotion = true;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasGsap = !!window.gsap;
  var T = window.WorldTiming || { dur: function (b) { return b; }, stagger: function (b) { return b; } };

  /* entrance only: the arriving page's cards fall into place, timed to how fast the page loaded */
  var ENTER = '.wp-top, .wp-search, .wp-chips, .wp-sort, .wp-stats, .wp-stack-row, .ws-wrap, ' +
    '.gc, .mc, .qr-card, .qr-stats, .qr-row, .qh-wrap, .qr-hero, ' +
    '.wl-balance, .wl-card, .wl-link, .wl-notice, ' +
    '.rk-me, .rk-podium, .rk-row, .rk-side, ' +
    '.cr-item, .cr-sum, .cr-empty, ' +
    '.inv-hero, .inv-card, .inv-note, ' +
    '.pf-head, .pf-card';
  function enter() {
    if (!hasGsap || reduce) return;
    var parts = document.querySelectorAll(ENTER);
    if (!parts.length) return;
    gsap.fromTo(parts, { y: 22, opacity: 0 },
      { y: 0, opacity: 1, duration: T.dur(.55), ease: 'power3.out', stagger: T.stagger(.04), clearProps: 'transform,opacity' });
  }
  if (document.readyState === 'complete') enter(); else window.addEventListener('load', enter);
})();
