(function () {
  var nav = document.getElementById('hubNav'); if (!nav) return;
  var wp = document.querySelector('.wp'); if (!wp) return;
  var homeKey = nav.dataset.home;
  var home = document.getElementById('hubP-' + homeKey);
  if (!home) {
    home = document.createElement('div'); home.className = 'hub-panel'; home.id = 'hubP-' + homeKey;
    [].slice.call(wp.children).forEach(function (c) {
      if (!c.classList.contains('hub-bar') && !c.classList.contains('hub-nav') && !c.classList.contains('hub-panel') && !c.hasAttribute('data-hub-keep')) home.appendChild(c);
    });
    wp.appendChild(home);
  }
  var tabs = [].slice.call(nav.querySelectorAll('a[data-hub]'));
  function inject(p, html, run) {
    var d = new DOMParser().parseFromString(html, 'text/html');
    var src = d.querySelector('#appPushContent') || d.querySelector('.ws2-main') || d.querySelector('main') || d.body;
    [].slice.call(d.querySelectorAll('link[rel="stylesheet"]')).forEach(function (l) {
      var h = l.getAttribute('href');
      if (h && !document.querySelector('link[href="' + h + '"]')) { var n = document.createElement('link'); n.rel = 'stylesheet'; n.href = h; document.head.appendChild(n); }
    });
    var scripts = [];
    if (run) {
      var re = new RegExp(run);
      [].slice.call(d.querySelectorAll('script:not([src])')).forEach(function (s) { if (re.test(s.textContent)) scripts.push(s.textContent); });
    }
    [].slice.call(src.querySelectorAll('script,.nav-icons,.ovm-bar')).forEach(function (n) { n.remove(); });
    p.innerHTML = src.innerHTML; p.dataset.loaded = '1';
    if (window.lucide) lucide.createIcons();
    scripts.forEach(function (t) {
      var s = document.createElement('script');
      s.textContent = t.replace(/document\.addEventListener\(\s*['"]DOMContentLoaded['"]\s*,/g, '(function(f){f()})(');
      document.body.appendChild(s);
    });
  }
  function load(a, p) {
    p.innerHTML = '<div class="hub-loading">Loading…</div>';
    fetch(a.dataset.url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok) throw 0; return r.text(); })
      .then(function (html) { inject(p, html, a.dataset.run); })
      .catch(function () { window.location.href = a.dataset.url; });
  }
  function show(key) {
    var a = tabs.filter(function (x) { return x.dataset.hub === key; })[0]; if (!a) return;
    tabs.forEach(function (x) { x.classList.toggle('on', x === a); });
    [].slice.call(wp.querySelectorAll('.hub-panel')).forEach(function (p) { p.hidden = (p.id !== 'hubP-' + key); });
    var p = document.getElementById('hubP-' + key);
    if (a.dataset.url && p && !p.dataset.loaded) load(a, p);
    try { history.replaceState(null, '', key === homeKey ? location.pathname + location.search : '#' + key); } catch (e) {}
  }
  tabs.forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.hub); }); });
  window.addEventListener('hashchange', function () { show((location.hash || '').replace('#', '') || homeKey); });
  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    var f = e.target, p = f.closest ? f.closest('.hub-panel') : null;
    if (!p || p.id === 'hubP-' + homeKey) return;
    if (/\/(gigs|marketplace)$/.test(f.action.split('?')[0])) return;
    e.preventDefault();
    fetch(f.action, { method: 'POST', body: new FormData(f), credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        var a = tabs.filter(function (x) { return p.id === 'hubP-' + x.dataset.hub; })[0];
        inject(p, html, a ? a.dataset.run : null);
      })
      .catch(function () { f.submit(); });
  });
  document.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('.hub-panel a') : null; if (!a || !a.href) return;
    var t = tabs.filter(function (x) { return x.dataset.url && a.href.split('?')[0].split('#')[0] === x.dataset.url.split('?')[0]; })[0];
    if (t) { e.preventDefault(); show(t.dataset.hub); }
  });
  var h = (location.hash || '').replace('#', '');
  show(h && tabs.some(function (x) { return x.dataset.hub === h; }) ? h : homeKey);
})();
