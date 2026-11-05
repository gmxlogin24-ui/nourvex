(function () {
  function init() {
    var seen = {}, items = [];
    document.querySelectorAll('a[href]').forEach(function (a) {
      var h = a.getAttribute('href');
      if (!h || h.indexOf('/admin') < 0 || seen[h]) return;
      seen[h] = 1;
      var t = (a.getAttribute('title') || a.textContent || '').replace(/\s+/g, ' ').trim();
      if (!t || t.length > 40) t = h.split('/').filter(Boolean).slice(2).join(' / ') || 'Admin home';
      items.push({ t: t, h: h });
    });
    if (!items.length) return;
    var b = document.createElement('button'); b.id = 'axK'; b.type = 'button'; b.textContent = '⌘K'; b.setAttribute('aria-label', 'Search admin');
    var o = document.createElement('div'); o.id = 'axKo';
    o.innerHTML = '<div class="axk-box"><input type="text" placeholder="Jump to a page..." autocomplete="off"><div class="axk-list"></div></div>';
    document.body.appendChild(b); document.body.appendChild(o);
    var inp = o.querySelector('input'), list = o.querySelector('.axk-list'), shown = [];
    function draw() {
      var q = inp.value.toLowerCase();
      shown = items.filter(function (i) { return i.t.toLowerCase().indexOf(q) > -1; }).slice(0, 30);
      list.innerHTML = shown.map(function (i, n) { return '<a href="' + i.h + '"' + (n === 0 ? ' class="sel"' : '') + '>' + i.t.replace(/</g, '&lt;') + '</a>'; }).join('');
    }
    function open() { o.classList.add('on'); inp.value = ''; draw(); setTimeout(function () { inp.focus(); }, 30); }
    function close() { o.classList.remove('on'); }
    b.addEventListener('click', open);
    o.addEventListener('click', function (e) { if (e.target === o) close(); });
    inp.addEventListener('input', draw);
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter' && shown[0]) location.href = shown[0].h; });
    document.addEventListener('keydown', function (e) {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); o.classList.contains('on') ? close() : open(); }
      if (e.key === 'Escape') close();
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
