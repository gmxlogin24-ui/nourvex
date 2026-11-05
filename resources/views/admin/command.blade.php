@extends('layouts.app-admin')
@section('title', 'Command')
@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-home.css') }}?v={{ time() }}">
<main class="cm" id="cm">
  <div class="cm-strip" id="cmStrip">
    @foreach($strip as $s)<div class="cm-chip"><small>{{ $s[0] }}</small><b>{{ $s[1] === null ? '—' : number_format($s[1]) }}</b></div>@endforeach
    <div class="cm-chip"><small>System time</small><b id="cmClock">--:--:--</b></div>
  </div>
  <div class="cm-modes" id="cmModes">
    <button type="button" class="on" data-mode="orbit">Orbit</button>
    <button type="button" data-mode="tree">Tree</button>
    <button type="button" data-fit>Recenter</button>
  </div>
  <div class="cm-view" id="cmView" data-mode="orbit">
    <div class="cm-world" id="cmWorld">
      <svg class="cm-wires" id="cmW" aria-hidden="true"></svg>
      <div class="cm-core" id="cmCore"><div class="cm-orb"><i></i><i></i><i></i></div><b>NAURVEX</b><small>COMMAND CORE</small></div>
    </div>
  </div>
  <aside class="cm-panel" id="cmP" hidden>
    <button type="button" class="cm-x" id="cmX" aria-label="Close">&times;</button>
    <div class="cm-ph"><span class="cm-i big" id="cmPIc"></span><div><small>MODULE</small><h2 id="cmPT">—</h2></div></div>
    <div class="cm-tabs"><button type="button" class="on" data-t="n">Numbers</button><button type="button" data-t="g">Go to</button></div>
    <div id="cmTn"><div class="cm-big"><span id="cmPM">—</span><small>total</small></div><div class="cm-grid" id="cmPG"></div></div>
    <div id="cmTg" hidden></div>
    <a class="cm-open" id="cmPO" href="#">Open module &rarr;</a>
  </aside>
  <form class="cm-ask" id="cmAsk" autocomplete="off">
    <i data-lucide="terminal"></i>
    <input id="cmQ" placeholder="Jump to… users, coin, audit" aria-label="Jump to module">
    <button type="submit" aria-label="Go"><i data-lucide="arrow-right"></i></button>
  </form>
  <div class="cm-sug" id="cmSug" hidden></div>
</main>
<script>
(function () {
  var M = @json($mods);
  var $ = function (i) { return document.getElementById(i); };
  var view = $('cmView'), world = $('cmWorld'), core = $('cmCore'), W = $('cmW'), P = $('cmP');
  document.body.classList.add('ax-lock');
  /* cmMoved */ document.body.appendChild(document.getElementById('cm'));
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function fmt(v) { if (v === null || v === undefined) return '—'; v = Number(v); return Math.abs(v) >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v.toLocaleString(); }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

  var ringCount = [0, 0];
  M.forEach(function (m, i) { ringCount[i % 2]++; });
  var seen = [0, 0], nodes = [];
  M.forEach(function (m, i) {
    var ring = i % 2, idx = seen[ring]++;
    var b = document.createElement('button'); b.type = 'button';
    b.className = 'cm-n' + (m.act > 0 ? ' p' : ''); b.dataset.k = m.k;
    b.style.setProperty('--pd', Math.max(1.3, 3.4 - Math.min(m.act, 40) / 14) + 's');
    b.innerHTML = '<span class="cm-i"><i data-lucide="' + m.i + '"></i></span><b>' + esc(m.n) + '</b><em>' + fmt(m.main) + '</em>';
    world.appendChild(b);
    var p = document.createElementNS('http://www.w3.org/2000/svg', 'path'); W.appendChild(p);
    nodes.push({ el: b, p: p, m: m, i: i, ring: ring, a0: idx / ringCount[ring] * Math.PI * 2 + ring * 0.7, x: 0, y: 0 });
  });
  if (window.lucide) lucide.createIcons();

  var mode = 'orbit', t = 0, last = 0, pressed = false, frozen = false, first = true;
  var px = 0, py = 0, z = 1, pt = null, cur = null, cx = 0, cy = 0, EX = 300, EY = 300, OY = 0;

  var PH = { x: [115, 165], y: [140, 230] };
  function RAD() { return innerWidth < 901 ? PH : { x: [220, 380], y: [200, 330] }; }
  function target(n) {
    if (mode === 'tree') return [130, (n.i - (M.length - 1) / 2) * 88];
    var a = n.a0 + t * (n.ring ? -0.07 : 0.1), R = RAD();
    return [Math.cos(a) * R.x[n.ring], Math.sin(a) * R.y[n.ring]];
  }
  function fit() {
    var r = view.getBoundingClientRect();
    var R = RAD(), ex = mode === 'tree' ? 255 : R.x[1] + 70, ey = mode === 'tree' ? M.length * 44 + 50 : R.y[1] + 58; EX = ex; EY = ey;
    OY = 0;
    if (innerWidth < 901 && mode !== 'tree') { z = clamp(r.width / (2 * ex), 0.5, 1.15); var yo = clamp((r.height - 272) / 2 / z - 58, 120, 330); PH.y = [yo * 0.8, yo]; OY = -36; }
    else z = clamp(Math.min(r.width / (2 * ex), r.height * 0.9 / (2 * ey)), 0.35, 1.3);
    px = 0; py = 0; pt = null;
  }
  function loop(ts) {
    var dt = Math.min(0.05, (ts - last) / 1000 || 0); last = ts;
    if (!frozen) t += dt;
    var k = first ? 1 : Math.min(1, dt * 7);
    var ct = mode === 'tree' ? [-170, 0] : [0, 0];
    cx += (ct[0] - cx) * k; cy += (ct[1] - cy) * k;
    core.style.transform = 'translate(' + cx + 'px,' + cy + 'px) translate(-50%,-50%)';
    nodes.forEach(function (n) {
      var g = target(n);
      n.x += (g[0] - n.x) * k; n.y += (g[1] - n.y) * k;
      n.el.style.transform = 'translate(' + n.x + 'px,' + n.y + 'px) translate(-50%,-50%)';
      var ex = mode === 'tree' ? n.x - 110 : n.x, hot = n.m.k === cur ? 'hot' : '';
      var d = mode === 'tree'
        ? 'M' + (cx + 64) + ',' + cy + ' C' + ((cx + 64 + ex) / 2) + ',' + cy + ' ' + ((cx + 64 + ex) / 2) + ',' + n.y + ' ' + ex + ',' + n.y
        : 'M' + cx + ',' + cy + ' L' + ex + ',' + n.y;
      n.p.setAttribute('d', d); n.p.setAttribute('class', hot);
    });
    if (pt) { px += (pt.x - px) * k; py += (pt.y - py) * k; }
    px = clamp(px, -EX * z, EX * z); py = clamp(py, -EY * z, EY * z);
    world.style.transform = 'translate(' + px + 'px,' + (py + OY) + 'px) scale(' + z + ')';
    first = false;
    requestAnimationFrame(loop);
  }
  fit(); requestAnimationFrame(loop);
  window.addEventListener('resize', function () { if (!cur) fit(); });

  var ptrs = {}, down = null, mv = 0, startD = 1, startZ = 1;
  function pc() { return Object.keys(ptrs).length; }
  function pd() { var a = Object.keys(ptrs).map(function (k) { return ptrs[k]; }); return Math.hypot(a[0].x - a[1].x, a[0].y - a[1].y) || 1; }
  view.addEventListener('pointerdown', function (e) {
    ptrs[e.pointerId] = { x: e.clientX, y: e.clientY }; down = e.target; pressed = true; pt = null;
    if (pc() === 1) mv = 0;
    try { view.setPointerCapture(e.pointerId); } catch (x) {}
    if (pc() === 2) { startD = pd(); startZ = z; }
  });
  view.addEventListener('pointermove', function (e) {
    var p = ptrs[e.pointerId]; if (!p) return;
    var dx = e.clientX - p.x, dy = e.clientY - p.y; p.x = e.clientX; p.y = e.clientY; mv += Math.abs(dx) + Math.abs(dy);
    if (pc() === 2) { z = clamp(startZ * pd() / startD, 0.35, 2.4); }
  });
  function up(e) {
    if (!ptrs[e.pointerId]) return; delete ptrs[e.pointerId];
    if (!pc()) {
      pressed = false;
      if (mv < 8) { var n = down && down.closest ? down.closest('.cm-n') : null; if (n) open(n.dataset.k); }
    }
  }
  view.addEventListener('pointerup', up); view.addEventListener('pointercancel', up);
  view.addEventListener('wheel', function (e) { e.preventDefault(); z = clamp(z * (e.deltaY < 0 ? 1.1 : 0.9), 0.35, 2.4); }, { passive: false });

  $('cmModes').addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b) return;
    if (b.hasAttribute('data-fit')) { fit(); return; }
    mode = b.dataset.mode; view.dataset.mode = mode;
    [].forEach.call($('cmModes').querySelectorAll('[data-mode]'), function (x) { x.classList.toggle('on', x === b); });
    fit();
  });

  function open(k) {
    var n = nodes.filter(function (x) { return x.m.k === k; })[0]; if (!n) return;
    var m = n.m; cur = k; frozen = true;
    $('cmPT').textContent = m.n; $('cmPM').textContent = fmt(m.main);
    $('cmPIc').innerHTML = '<i data-lucide="' + m.i + '"></i>';
    $('cmPG').innerHTML = m.sub.map(function (s) { return '<div><small>' + esc(s[0]) + '</small><b>' + fmt(s[1]) + '</b></div>'; }).join('');
    $('cmTg').innerHTML = (m.links || []).map(function (l) { return '<a href="' + esc(l.url) + '">' + esc(l.n) + '<span>&rarr;</span></a>'; }).join('') || '<p class="cm-dim">No extra pages.</p>';
    $('cmPO').href = m.url; P.hidden = false; tab('n');
    [].forEach.call(world.querySelectorAll('.cm-n'), function (e) { e.classList.toggle('on', e.dataset.k === k); });
    if (window.lucide) lucide.createIcons();
    var r = view.getBoundingClientRect();
    pt = innerWidth < 901 ? { x: 0 - n.x * z, y: -0.3 * r.height - n.y * z } : { x: -0.12 * r.width - n.x * z, y: -n.y * z };
  }
  function close() { cur = null; frozen = false; P.hidden = true; pt = null; [].forEach.call(world.querySelectorAll('.cm-n'), function (e) { e.classList.remove('on'); }); }
  function tab(k) {
    [].forEach.call(P.querySelectorAll('.cm-tabs button'), function (b) { b.classList.toggle('on', b.dataset.t === k); });
    $('cmTn').hidden = k !== 'n'; $('cmTg').hidden = k !== 'g';
  }
  $('cmX').addEventListener('click', close);
  P.querySelector('.cm-tabs').addEventListener('click', function (e) { var b = e.target.closest('button'); if (b) tab(b.dataset.t); });

  var all = [], seenU = {};
  M.forEach(function (m) {
    all.push({ n: m.n, go: function () { open(m.k); } });
    (m.links || []).forEach(function (l) { if (!seenU[l.url]) { seenU[l.url] = 1; all.push({ n: l.n, go: function () { location.href = l.url; } }); } });
  });
  var inp = $('cmQ'), sug = $('cmSug');
  function find(q) { q = q.trim().toLowerCase(); return q ? all.filter(function (a) { return a.n.toLowerCase().indexOf(q) > -1; }).slice(0, 6) : []; }
  inp.addEventListener('input', function () {
    var f = find(inp.value); sug._f = f; sug.hidden = !f.length;
    sug.innerHTML = f.map(function (a, i) { return '<button type="button" data-i="' + i + '">' + esc(a.n) + '</button>'; }).join('');
  });
  sug.addEventListener('click', function (e) { var b = e.target.closest('button'); if (b) { sug._f[+b.dataset.i].go(); sug.hidden = true; inp.value = ''; } });
  $('cmAsk').addEventListener('submit', function (e) {
    e.preventDefault(); var f = find(inp.value);
    if (f.length) { f[0].go(); sug.hidden = true; } else { inp.placeholder = 'No match. Try: users, coin, audit'; }
    inp.value = '';
  });
  setInterval(function () { var d = new Date(); $('cmClock').textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2) + ':' + ('0' + d.getSeconds()).slice(-2); }, 1000);
})();
</script>
@endsection
