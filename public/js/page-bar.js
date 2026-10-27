(function () {
  var b = document.getElementById('pbBar');
  if (!b) return;
  document.body.classList.add('pb-on');
  var t = null;
  document.addEventListener('scroll', function () {
    b.classList.add('pb-scrolling');
    clearTimeout(t);
    t = setTimeout(function () { b.classList.remove('pb-scrolling'); }, 250);
  }, { passive: true, capture: true });
  if (window.lucide) lucide.createIcons();
})();
