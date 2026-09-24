(function () {
  var el = document.getElementById('qelvoPreloader');
  if (!el) return;

  var shown = false;
  var minVisibleUntil = 0;
  var revealTimer = null;

  function reveal() {
    if (shown) return;
    shown = true;
    el.classList.add('is-visible');
    minVisibleUntil = Date.now() + 300;
  }

  function hide() {
    if (!shown) return;
    var wait = Math.max(0, minVisibleUntil - Date.now());
    setTimeout(function () {
      el.classList.remove('is-visible');
      shown = false;
    }, wait);
  }

  revealTimer = setTimeout(reveal, 250);

  window.addEventListener('load', function () {
    clearTimeout(revealTimer);
    hide();
  });

  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      clearTimeout(revealTimer);
      el.classList.remove('is-visible');
      shown = false;
    }
  });

  function shouldSkip(a) {
    if (!a || !a.getAttribute('href')) return true;
    var href = a.getAttribute('href');
    if (a.target === '_blank') return true;
    if (a.hasAttribute('download')) return true;
    if (href.startsWith('#')) return true;
    if (href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return true;
    if (a.hasAttribute('data-no-preloader')) return true;
    if (a.origin && a.origin !== window.location.origin) return true;
    return false;
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest('a');
    if (shouldSkip(a)) return;
    setTimeout(reveal, 250);
  }, true);

  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.hasAttribute('data-no-preloader') || f.target === '_blank') return;
    setTimeout(reveal, 250);
  }, true);
})();
