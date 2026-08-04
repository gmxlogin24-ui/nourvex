document.addEventListener('DOMContentLoaded', function () {
  var folder = document.getElementById('ledgerCard');
  var track = document.getElementById('ledgerTrack');
  var dots = document.querySelectorAll('#ledgerDots span');
  if (!folder || !track) return;

  var isTouch = matchMedia('(pointer:coarse)').matches;
  var pages = Array.prototype.slice.call(track.querySelectorAll('.ledger-page'));
  var index = 0;

  function fanned() {
    return folder.classList.contains('is-peek') || folder.classList.contains('is-open');
  }

  function layout(animate) {
    pages.forEach(function (page, i) {
      var depth = (i - index + pages.length) % pages.length;
      var ty, tx, scale, rot, op;
      var bright;
      if (fanned()) {
        ty = -(depth * 36);
        tx = depth === 0 ? 0 : (depth === 1 ? -14 : 14);
        scale = 1 - depth * 0.07;
        rot = depth === 0 ? 0 : (depth === 1 ? -5 : 5);
        bright = depth === 0 ? 1 : 0.5;
      } else {
        ty = depth * 4;
        tx = 0;
        scale = 1 - depth * 0.02;
        rot = 0;
        bright = depth === 0 ? 1 : 1;
      }
      page.style.transition = animate
        ? 'transform .8s cubic-bezier(.22,1,.36,1), filter .7s ease'
        : 'none';
      page.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + scale + ') rotate(' + rot + 'deg)';
      page.style.filter = 'brightness(' + bright + ')';
      page.style.zIndex = 30 - depth * 10;
    });
    dots.forEach(function (d, di) { d.classList.toggle('active', di === index); });
  }

  function select(i) {
    index = i;
    folder.classList.remove('is-peek');
    folder.classList.add('is-open');
    layout(true);

    var page = pages[i];
    page.classList.remove('is-reflecting');
    void page.offsetWidth;
    page.classList.add('is-reflecting');
  }

  pages.forEach(function (page, i) {
    page.addEventListener('click', function (e) {
      if (!fanned()) return;
      e.stopPropagation();
      select(i);
    });
    if (!isTouch) {
      page.addEventListener('mouseenter', function () {
        if (!fanned()) return;
        select(i);
      });
    }
  });

  var mo = new MutationObserver(function () { layout(true); });
  mo.observe(folder, { attributes: true, attributeFilter: ['class'] });

  window.addEventListener('resize', function () { layout(false); });

  var obs = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      obs.unobserve(folder);
      document.querySelectorAll('.ledger-value').forEach(function (el) {
        var target = parseFloat(el.dataset.target || '0');
        var isInt = el.dataset.format === 'int';
        var start = performance.now(), dur = 1400;
        function tick(now) {
          var p = Math.min(1, (now - start) / dur);
          var eased = 1 - Math.pow(1 - p, 3);
          var val = target * eased;
          el.textContent = isInt
            ? Math.floor(val).toLocaleString()
            : '₦' + val.toLocaleString(undefined, { maximumFractionDigits: 2 });
          if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
      });
    });
  }, { threshold: 0.3 });
  obs.observe(folder);

  layout(false);
});
