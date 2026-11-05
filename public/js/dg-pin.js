(function () {
  var orig, clone, btns = [], btn, sc = null, tick = false, shown = false;
  function pinTop() {
    var pb = document.querySelector('.pb-bar');
    var r = pb ? pb.getBoundingClientRect() : null;
    return Math.round(r && r.bottom > 0 ? r.bottom + 6 : 70);
  }
  function sync() {
    var links = [].slice.call(orig.querySelectorAll('a'));
    links.forEach(function (a, i) { if (btns[i]) btns[i].classList.toggle('on', a.classList.contains('on')); });
    var act = clone.querySelector('button.on');
    if (act && act.scrollIntoView) { try { clone.scrollLeft = act.offsetLeft - 20; } catch (e) {} }
  }
  function build() {
    clone = document.createElement('div');
    clone.className = 'dg-pintabs';
    [].slice.call(orig.querySelectorAll('a')).forEach(function (a, i) {
      var b = document.createElement('button');
      b.type = 'button'; b.textContent = a.textContent.trim();
      b.addEventListener('click', function () {
        a.click();
        setTimeout(sync, 30);
        window.scrollBy({ top: 0 });
      });
      clone.appendChild(b); btns.push(b);
    });
    document.body.appendChild(clone);
    sync();
  }
  function scrolled() { return Math.max(window.scrollY || 0, sc ? sc.scrollTop : 0); }
  function update() {
    tick = false;
    var pt = pinTop();
    document.documentElement.style.setProperty('--ovm-pin', pt + 'px');
    if (orig && clone) {
      var r = orig.getBoundingClientRect();
      var should = r.top <= pt && scrolled() > 40;
      if (should) {
        clone.style.left = r.left + 'px'; clone.style.width = r.width + 'px'; clone.style.top = pt + 'px';
        document.documentElement.style.setProperty('--ovm-h', clone.offsetHeight + 'px');
        if (!shown) { sync(); clone.classList.add('show'); shown = true; }
      } else if (shown) {
        clone.classList.remove('show'); shown = false;
        orig.classList.add('ovm-drop');
        setTimeout(function () { orig.classList.remove('ovm-drop'); }, 500);
        document.documentElement.style.setProperty('--ovm-h', '0px');
      }
    }
    if (btn) btn.classList.toggle('show', scrolled() > 700);
  }
  function req() { if (!tick) { tick = true; requestAnimationFrame(update); } }
  function init() {
    orig = document.getElementById('ovmTabs');
    btn = document.querySelector('.dg-top');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button'; btn.className = 'dg-top'; btn.setAttribute('aria-label', 'Back to top');
      btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
      document.body.appendChild(btn);
    }
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      if (sc) sc.scrollTo({ top: 0, behavior: 'smooth' });
    });
    if (orig) build();
    document.addEventListener('scroll', function (e) {
      var t = e.target; if (t && t !== document && t.nodeType === 1) sc = t;
      req();
    }, { passive: true, capture: true });
    window.addEventListener('resize', req);
    var m = new MutationObserver(function () { if (clone) sync(); });
    if (orig) m.observe(orig, { attributes: true, subtree: true, attributeFilter: ['class'] });
    update();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
