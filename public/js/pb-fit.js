(function () {
  function fit() {
    var b = document.getElementById('pbBar');
    if (!b || getComputedStyle(b).display === 'none') return;
    var h = Math.ceil(b.getBoundingClientRect().bottom + 6);
    document.documentElement.style.setProperty('--pb-h', h + 'px');
  }
  fit();
  window.addEventListener('load', fit);
  window.addEventListener('resize', fit);
  setTimeout(fit, 300);
})();
