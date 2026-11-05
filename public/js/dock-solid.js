(function () {
  function parse(c) {
    var m = c && c.match(/rgba?\(([^)]+)\)/);
    if (!m) return null;
    var p = m[1].split(/[ ,\/]+/).filter(Boolean).map(parseFloat);
    return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
  }
  function run() {
    var sel = '[class*="dock"] a, [class*="dock"] button, [id*="dock"] a, [id*="dock"] button';
    document.querySelectorAll(sel).forEach(function (el) {
      if (el.dataset.dockSolid) return;
      var cs = getComputedStyle(el);
      var o = parseFloat(cs.opacity);
      if (!isNaN(o) && o < 1) {
        el.style.setProperty('opacity', String((1 + o) / 2), 'important');
      }
      var bg = parse(cs.backgroundColor);
      if (bg && bg.a > 0 && bg.a < 1) {
        var na = (1 + bg.a) / 2;
        el.style.setProperty('background-color', 'rgba(' + bg.r + ',' + bg.g + ',' + bg.b + ',' + na.toFixed(3) + ')', 'important');
      }
      el.dataset.dockSolid = '1';
    });
  }
  function start() { run(); setTimeout(run, 800); setTimeout(run, 2000); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
