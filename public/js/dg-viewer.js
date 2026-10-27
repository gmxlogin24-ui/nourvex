(function () {
  var root = document.getElementById('dgFeed'); if (!root) return;
  var cards = root.querySelectorAll('.dgf-card');
  if ('IntersectionObserver' in window) {
    var n = 0;
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        var c = e.target; io.unobserve(c);
        setTimeout(function () { c.classList.add('in'); }, (n++ % 4) * 90);
      });
    }, { threshold: 0.15 });
    cards.forEach(function (c) { c.classList.add('pre'); io.observe(c); });
  }
  var dg = document.getElementById('dg');
  function openViewer(card) {
    var list = [].filter.call(root.querySelectorAll('.dgf-card'), function (c) { return !c.hidden; });
    var ov = document.createElement('div'); ov.className = 'dgv';
    var x = document.createElement('button'); x.type = 'button'; x.className = 'dgv-x'; x.textContent = '\u00d7'; x.setAttribute('aria-label', 'Close');
    var wrap = document.createElement('div'); wrap.className = 'dgv-feed';
    ov.appendChild(x); ov.appendChild(wrap);
    var target = null, vids = [];
    list.forEach(function (c) {
      var s = document.createElement('section'); s.className = 'dgv-s';
      var bg = document.createElement('div'); bg.className = 'dgv-bg';
      if (c.dataset.video) { var v = document.createElement('video'); v.src = c.dataset.video; v.muted = true; v.loop = true; v.playsInline = true; v.preload = 'metadata'; bg.appendChild(v); vids.push(v); }
      else if (c.dataset.img) { bg.style.backgroundImage = "url('" + c.dataset.img.replace(/'/g, '%27') + "')"; }
      var ct = document.createElement('div'); ct.className = 'dgv-c';
      var ey = document.createElement('div'); ey.className = 'dgv-eye'; ey.textContent = c.querySelector('.dgf-eye').textContent.replace(/\s+/g, ' ').trim();
      var h = document.createElement('h2'); h.textContent = c.querySelector('h5').textContent;
      var p = document.createElement('p'); p.textContent = c.dataset.body || '';
      ct.appendChild(ey); ct.appendChild(h); ct.appendChild(p);
      var go = c.querySelector('.dgf-go');
      if (go) { var a = document.createElement('a'); a.className = 'dgv-go'; a.href = go.href; a.target = '_blank'; a.rel = go.rel; a.textContent = go.textContent; ct.appendChild(a); }
      var act = document.createElement('div'); act.className = 'dgv-act';
      var lk = c.querySelector('.dgf-like');
      if (lk) {
        var lb = document.createElement('button'); lb.type = 'button'; lb.innerHTML = lk.innerHTML; if (lk.classList.contains('on')) lb.classList.add('on');
        lb.addEventListener('click', function () { lk.click(); setTimeout(function () { lb.innerHTML = lk.innerHTML; lb.classList.toggle('on', lk.classList.contains('on')); }, 450); });
        act.appendChild(lb);
        var cm = c.querySelector('.dgf-cm'), hid = c.querySelector('.pf-cm');
        if (cm && hid) { var cb = document.createElement('button'); cb.type = 'button'; cb.innerHTML = cm.innerHTML; cb.addEventListener('click', function () { hid.click(); }); act.appendChild(cb); }
      }
      s.appendChild(bg); s.appendChild(ct); if (act.children.length) s.appendChild(act);
      wrap.appendChild(s);
      if (c === card) target = s;
    });
    document.body.appendChild(ov);
    if (dg) dg.style.overflow = 'hidden';
    if (target) wrap.scrollTop = target.offsetTop;
    var vio = null;
    if ('IntersectionObserver' in window) {
      vio = new IntersectionObserver(function (es) { es.forEach(function (e) { var v = e.target.querySelector('video'); if (!v) return; if (e.isIntersecting) v.play().catch(function () {}); else v.pause(); }); }, { root: wrap, threshold: 0.6 });
      wrap.querySelectorAll('.dgv-s').forEach(function (s) { vio.observe(s); });
    }
    function close() { if (vio) vio.disconnect(); ov.remove(); if (dg) dg.style.overflow = ''; document.removeEventListener('keydown', esc); }
    function esc(e) { if (e.key === 'Escape') close(); }
    x.addEventListener('click', close); document.addEventListener('keydown', esc);
  }
  root.addEventListener('click', function (e) {
    if (e.target.closest('button,a')) return;
    var c = e.target.closest('.dgf-card'); if (c) openViewer(c);
  });
})();
