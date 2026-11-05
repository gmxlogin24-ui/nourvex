(function () {
  var MAX = 5, mq = window.matchMedia('(max-width:899px)');
  var closing = false;
  function active() { return (document.body.getAttribute('data-mobile-nav-style') || '').indexOf('merged_') === 0 && mq.matches; }
  function el(tag, cls) { var e = document.createElement(tag); if (cls) e.className = cls; return e; }
  function icon(src) {
    if (!src) return null;
    var n = src.querySelector('svg') || src.querySelector('i[data-lucide]');
    return n ? n.cloneNode(true) : null;
  }
  function sheetEl() { return document.getElementById('mbSheet'); }
  function moreEl() { return document.querySelector('.mb-more'); }
  function gsapOk() {
    return !!(window.gsap && gsap.fromTo && gsap.globalTimeline && gsap.globalTimeline.timeScale() > 0 && gsap.globalTimeline.timeScale() <= 3);
  }
  function rowsOf(s) { return [].slice.call(s.querySelectorAll('.mb-row')); }
  function offsets(rows) {
    var m = moreEl(), mr = m ? m.getBoundingClientRect() : null;
    var cx = mr ? mr.left + mr.width / 2 : window.innerWidth / 2;
    var cy = mr ? mr.top + mr.height / 2 : window.innerHeight;
    return rows.map(function (r) {
      var b = r.getBoundingClientRect();
      return { x: cx - (b.left + b.width / 2), y: cy - (b.top + b.height / 2) };
    });
  }
  function cancelWA(rows) {
    rows.forEach(function (r) { if (r.getAnimations) { r.getAnimations().forEach(function (a) { a.cancel(); }); } });
  }
  function waPlay(rows, off, opening, done) {
    var n = rows.length, fin = 0;
    if (!n) { if (done) { done(); } return; }
    rows.forEach(function (r, i) {
      var from = 'translate(' + off[i].x + 'px,' + off[i].y + 'px) scale(.25)';
      var kf = opening ? [{ transform: from, opacity: 0 }, { transform: 'none', opacity: 1 }] : [{ transform: 'none', opacity: 1 }, { transform: from, opacity: 0 }];
      var a = r.animate(kf, { duration: opening ? 450 : 250, delay: (opening ? (n - 1 - i) : i) * 40, easing: opening ? 'cubic-bezier(.34,1.56,.64,1)' : 'cubic-bezier(.4,0,1,1)', fill: 'both' });
      a.onfinish = function () { fin++; if (fin === n && done) { done(); } };
    });
  }
  function playOpen(s) {
    var rows = rowsOf(s), back = s.querySelector('.mb-back'), off = offsets(rows);
    s._off = off;
    cancelWA(rows);
    if (gsapOk()) {
      gsap.killTweensOf(rows);
      gsap.timeline().fromTo(back, { opacity: 0 }, { opacity: 1, duration: 0.25, ease: 'power1.out' });
      gsap.timeline().fromTo(rows,
        { x: function (i) { return off[i].x; }, y: function (i) { return off[i].y; }, scale: 0.25, opacity: 0 },
        { x: 0, y: 0, scale: 1, opacity: 1, duration: 0.55, ease: 'back.out(1.7)', stagger: { each: 0.045, from: 'end' } });
    } else {
      waPlay(rows, off, true);
    }
  }
  function playClose(s, done) {
    var rows = rowsOf(s), back = s.querySelector('.mb-back'), off = s._off || offsets(rows);
    cancelWA(rows);
    if (gsapOk()) {
      gsap.killTweensOf(rows);
      gsap.timeline().to(back, { opacity: 0, duration: 0.22, ease: 'power1.in' });
      gsap.timeline({ onComplete: done }).to(rows, {
        x: function (i) { return off[i].x; }, y: function (i) { return off[i].y; }, scale: 0.25, opacity: 0,
        duration: 0.3, ease: 'back.in(1.4)', stagger: { each: 0.03, from: 'start' }
      });
    } else {
      waPlay(rows, off, false, done);
    }
  }
  function closeSheet(instant) {
    var s = sheetEl(), m = moreEl();
    if (m) { m.setAttribute('aria-expanded', 'false'); }
    if (!s || s.hidden) { return; }
    if (instant) { s.hidden = true; closing = false; return; }
    if (closing) { return; }
    closing = true;
    playClose(s, function () { s.hidden = true; closing = false; });
  }
  function openSheet() {
    var s = sheetEl(), m = moreEl();
    if (!s || !m) { return; }
    closing = false;
    fillSheet(s);
    s.hidden = false;
    m.setAttribute('aria-expanded', 'true');
    playOpen(s);
  }
  function teardown() {
    var s = sheetEl(); if (s) { s.remove(); }
    closing = false;
    [].forEach.call(document.querySelectorAll('.mb-more, .mb-prof'), function (n) { n.remove(); });
    [].forEach.call(document.querySelectorAll('.mb-hid'), function (n) { n.classList.remove('mb-hid'); });
  }
  function row(href, label, ic, badge, sheetAttr) {
    var r = el(href ? 'a' : 'button', 'mb-row');
    if (href) { r.setAttribute('href', href); } else { r.type = 'button'; }
    if (sheetAttr) { r.setAttribute('data-dock-sheet', sheetAttr); }
    var box = el('span', 'mb-ic');
    if (ic) { box.appendChild(ic); }
    if (badge) { box.appendChild(badge.cloneNode(true)); }
    var lb = el('span', 'mb-lb'); lb.textContent = label;
    r.appendChild(box); r.appendChild(lb);
    return r;
  }
  function fillSheet(sheet) {
    var grid = sheet.querySelector('.mb-grid'); grid.innerHTML = '';
    var seen = {};
    var dock = document.querySelectorAll('#nvDock .ovd .ovd-btn:not(.mb-more):not(.mb-prof)');
    [].forEach.call(dock, function (a) { if (a.href) { seen[a.href] = 1; } });
    [].forEach.call(dock, function (a, i) {
      if (i < MAX) { return; }
      grid.appendChild(row(a.getAttribute('href'), a.getAttribute('aria-label') || '', icon(a.querySelector('.ovd-in')), a.querySelector('.nv-badge'), a.getAttribute('data-dock-sheet')));
    });
    var bar = document.getElementById('pbBar');
    if (bar) {
      [].forEach.call(bar.children, function (c) {
        var cls = c.className || '';
        if (c.tagName === 'A' && cls.indexOf('pb-av') > -1) { return; }
        if (c.tagName === 'FORM') { grid.appendChild(row(c.getAttribute('action'), 'Search', icon(c), null)); return; }
        if (c.tagName === 'BUTTON') {
          var b = row(null, 'Light / dark', icon(c), null);
          b.addEventListener('click', function () { closeSheet(); c.click(); });
          grid.appendChild(b); return;
        }
        if (c.tagName === 'A') {
          if (c.href && seen[c.href]) { return; }
          grid.appendChild(row(c.getAttribute('href'), c.getAttribute('aria-label') || '', icon(c), c.querySelector('.nv-badge')));
        }
      });
    }
    if (window.lucide) { lucide.createIcons(); }
  }
  function build() {
    var nav = document.querySelector('#nvDock .ovd');
    if (!nav) { return; }
    if (!active()) { teardown(); return; }
    if (nav.querySelector('.mb-more')) { return; }
    [].forEach.call(nav.querySelectorAll('.ovd-btn'), function (a, i) { if (i >= MAX) { a.classList.add('mb-hid'); } });

    var av = document.querySelector('#pbBar .pb-av');
    if (av) {
      var p = el('a', 'ovd-btn mb-prof');
      p.setAttribute('href', av.getAttribute('href') || '#'); p.setAttribute('aria-label', 'Profile');
      p.innerHTML = '<span class="ovd-tile"><span class="ovd-in"></span></span><span class="ovd-lbl">Profile</span>';
      if (av.firstElementChild) { p.querySelector('.ovd-in').appendChild(av.firstElementChild.cloneNode(true)); }
      try { if (av.pathname && av.pathname !== '/' && location.pathname.indexOf(av.pathname) === 0) { p.classList.add('on'); } } catch (e) {}
      nav.appendChild(p);
    }

    var more = el('button', 'ovd-btn mb-more'); more.type = 'button';
    more.setAttribute('aria-label', 'More'); more.setAttribute('aria-expanded', 'false');
    more.innerHTML = '<span class="ovd-tile"><span class="ovd-in"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span></span><span class="ovd-lbl">More</span>';
    var ub = document.querySelector('#pbBar .pb-tg .nv-badge');
    if (ub) { var d = el('em', 'nv-badge'); d.textContent = ub.textContent; more.querySelector('.ovd-tile').appendChild(d); }
    nav.appendChild(more);

    var sheet = el('div', 'mb-sheet'); sheet.id = 'mbSheet'; sheet.hidden = true;
    sheet.innerHTML = '<div class="mb-back"></div><div class="mb-panel"><div class="mb-grid"></div></div>';
    document.body.appendChild(sheet);
    sheet.querySelector('.mb-back').addEventListener('click', function () { closeSheet(); });
    sheet.addEventListener('click', function (e) { if (e.target.closest && e.target.closest('.mb-row')) { closeSheet(); } });
    more.addEventListener('click', function () {
      var s = sheetEl();
      if (!s) { return; }
      if (s.hidden) { openSheet(); } else { closeSheet(); }
    });
  }
  function init() { build(); }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
  new MutationObserver(build).observe(document.body, { attributes: true, attributeFilter: ['data-mobile-nav-style'] });
  if (mq.addEventListener) { mq.addEventListener('change', build); } else if (mq.addListener) { mq.addListener(build); }
})();
