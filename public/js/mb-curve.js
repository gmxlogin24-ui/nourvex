(function () {
  var mq = window.matchMedia('(max-width:899px)');
  var PROPS = ['background', 'background-color', 'background-image', 'backdrop-filter', '-webkit-backdrop-filter', 'filter', 'box-shadow'];
  function merged() { return (document.body.getAttribute('data-mobile-nav-style') || '').indexOf('merged_') === 0 && mq.matches; }
  function clean() {
    ['mbCurve', 'mbSides'].forEach(function (id) { var n = document.getElementById(id); if (n) { n.remove(); } });
    var r = document.documentElement;
    r.style.removeProperty('--mb-bg');
    r.removeAttribute('data-mb-tone');
  }
  function strip(n) {
    if (!n || !n.style) { return; }
    PROPS.forEach(function (p) { if (n.style.getPropertyValue(p)) { n.style.removeProperty(p); } });
  }
  function guard() {
    if (!merged()) { return; }
    var d = document.getElementById('nvDock');
    if (!d) { return; }
    strip(d); strip(d.querySelector('.ovd'));
    [].forEach.call(d.querySelectorAll('.ovd-btn'), strip);
  }
  function init() {
    clean(); guard();
    var d = document.getElementById('nvDock');
    if (d) { new MutationObserver(guard).observe(d, { attributes: true, attributeFilter: ['style'], subtree: true }); }
    new MutationObserver(function () { setTimeout(guard, 30); }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme', 'class'] });
    new MutationObserver(function () { setTimeout(guard, 30); }).observe(document.body, { attributes: true, attributeFilter: ['data-mobile-nav-style'] });
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
  window.addEventListener('load', function () { clean(); guard(); });
  setTimeout(guard, 400);
  setTimeout(guard, 1500);
})();
