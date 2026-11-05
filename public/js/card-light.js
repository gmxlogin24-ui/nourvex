(function () {
  var DARK_LIMIT = 0.5;
  var CARD = /card|glass|tile|panel|box|widget|deck|sheet|surface|item|row|hi-|dg-|dqm-|au-|stx|dash/i;

  function parse(c) {
    var m = c && c.match(/rgba?\(([^)]+)\)/);
    if (!m) return null;
    var p = m[1].split(/[ ,\/]+/).filter(Boolean).map(parseFloat);
    return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
  }
  function lum(c) {
    function ch(v) { v = v / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    return 0.2126 * ch(c.r) + 0.7152 * ch(c.g) + 0.0722 * ch(c.b);
  }
  function isCardish(el) {
    var s = (el.className && el.className.baseVal !== undefined ? el.className.baseVal : el.className) || '';
    return typeof s === 'string' && CARD.test(s);
  }
  function run() {
    var els = document.querySelectorAll('body div, body section, body article, body li, body a, body aside');
    els.forEach(function (el) {
      if (el.dataset.cardLight) return;
      if (el.closest('[class*="dock"], [id*="dock"], nav, header')) return;
      if (!isCardish(el)) return;
      var cs = getComputedStyle(el);
      var bg = parse(cs.backgroundColor);
      if (!bg || bg.a < 0.5) return;
      if (lum(bg) >= DARK_LIMIT) return;
      el.style.setProperty('background-color', '#FFFFFF', 'important');
      el.style.setProperty('color', '#0F1115', 'important');
      el.querySelectorAll('*').forEach(function (k) {
        if (k.tagName === 'IMG' || k.tagName === 'SVG' || k.closest('svg')) return;
        var kc = parse(getComputedStyle(k).color);
        if (kc && lum(kc) > 0.6) k.style.setProperty('color', '#0F1115', 'important');
      });
      el.dataset.cardLight = '1';
    });
  }
  function start() { run(); setTimeout(run, 800); setTimeout(run, 2000); }
  document.addEventListener('click', function () { setTimeout(run, 300); setTimeout(run, 1000); }, true);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
