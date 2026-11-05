(function () {
  var b = null, t = null;
  function init() {
    b = document.querySelector('.pd-bk');
    if (!b) return;
    window.addEventListener('scroll', function () {
      if (window.scrollY < 10) { b.classList.remove('pd-bk-hide'); return; }
      b.classList.add('pd-bk-hide');
      clearTimeout(t);
      t = setTimeout(function () { b.classList.remove('pd-bk-hide'); }, 700);
    }, { passive: true });
    b.addEventListener('click', function (e) {
      if (b.classList.contains('pd-bk-hide')) {
        e.preventDefault();
        b.classList.remove('pd-bk-hide');
      }
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
