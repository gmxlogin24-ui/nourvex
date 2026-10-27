(function () {
  var deck = document.getElementById('dgDeck');
  if (!deck) return;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var x0 = 0, y0 = 0, busy = false;
  deck.addEventListener('pointerdown', function (e) { x0 = e.clientX; y0 = e.clientY; }, true);
  deck.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('.dg-jc') : null;
    if (!a || a.getAttribute('data-pos') !== '0') return;
    if (Math.abs(e.clientX - x0) > 10 || Math.abs(e.clientY - y0) > 10) return;
    e.preventDefault(); e.stopPropagation();
    if (busy) return;
    var href = a.getAttribute('href');
    if (reduce) { window.location.href = href; return; }
    busy = true;
    var img = a.querySelector('img');
    var r = a.querySelector('.dg-jc-in').getBoundingClientRect();
    var acc = a.getAttribute('data-acc') || '255,255,255';
    var o = document.createElement('div');
    o.className = 'dg-zoom';
    o.style.cssText = 'position:fixed;z-index:99999;overflow:hidden;pointer-events:all;background:#000 center/cover no-repeat;'
      + 'left:' + r.left + 'px;top:' + r.top + 'px;width:' + r.width + 'px;height:' + r.height + 'px;border-radius:34px;'
      + 'transition:left .8s cubic-bezier(.7,0,.2,1),top .8s cubic-bezier(.7,0,.2,1),width .8s cubic-bezier(.7,0,.2,1),height .8s cubic-bezier(.7,0,.2,1),border-radius .8s cubic-bezier(.7,0,.2,1);';
    if (img && img.src) o.style.backgroundImage = 'url(' + img.src + ')';
    var v = document.createElement('i');
    v.style.cssText = 'position:absolute;inset:0;opacity:0;background:radial-gradient(circle at 50% 50%,rgba(' + acc + ',.55),rgba(' + acc + ',0) 70%);transition:opacity .8s ease;';
    o.appendChild(v);
    document.body.appendChild(o);
    void o.offsetWidth;
    o.style.left = '0'; o.style.top = '0'; o.style.width = '100vw'; o.style.height = '100vh'; o.style.borderRadius = '0';
    v.style.opacity = '1';
    setTimeout(function () { window.location.href = href; }, 820);
  }, true);
  window.addEventListener('pageshow', function () {
    busy = false;
    [].slice.call(document.querySelectorAll('.dg-zoom')).forEach(function (n) { n.remove(); });
  });
})();

(function () {
  var d = document.querySelector('.dg-more');
  if (!d || !window.matchMedia) return;
  var mq = window.matchMedia('(min-width:901px)');
  function keep() { if (mq.matches && !d.open) d.open = true; }
  keep();
  d.addEventListener('toggle', keep);
  window.addEventListener('resize', keep);
})();
