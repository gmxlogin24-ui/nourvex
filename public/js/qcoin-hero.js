(function () {
  if (window.__qc3) return;
  window.__qc3 = true;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var DEF = { rx: 8, ry: -12 };
  var roots = [];

  function flip(root) {
    var spinner = root.querySelector('.qc-spin');
    if (!spinner) return;
    spinner.classList.remove('qc-go');
    void spinner.offsetWidth;
    spinner.classList.add('qc-go');
    if (navigator.vibrate) { try { navigator.vibrate(12); } catch (e) {} }
  }

  function bind(root) {
    if (root.dataset.bound) return;
    root.dataset.bound = '1';
    roots.push(root);
    var spinner = root.querySelector('.qc-spin');
    function set(rx, ry) {
      root.style.setProperty('--rx', rx.toFixed(1) + 'deg');
      root.style.setProperty('--ry', ry.toFixed(1) + 'deg');
    }
    root.addEventListener('pointermove', function (e) {
      var r = root.getBoundingClientRect();
      var x = (e.clientX - r.left) / r.width - 0.5;
      var y = (e.clientY - r.top) / r.height - 0.5;
      set(DEF.rx - y * 22, DEF.ry + x * 34);
    });
    root.addEventListener('pointerleave', function () { set(DEF.rx, DEF.ry); });
    root.addEventListener('click', function () { flip(root); });
    if (spinner) spinner.addEventListener('animationend', function () { spinner.classList.remove('qc-go'); });
  }

  function init() {
    document.querySelectorAll('[data-qc3]').forEach(bind);
    if (!reduce) {
      setInterval(function () {
        roots.forEach(function (r) {
          if (r.getBoundingClientRect().bottom > 0 && r.getBoundingClientRect().top < window.innerHeight) flip(r);
        });
      }, 6000);
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
