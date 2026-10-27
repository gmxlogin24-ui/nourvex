@extends('layouts.app-authenticated')
@section('title', 'Games')
@section('content')
<link rel="stylesheet" href="{{ asset('css/games.css') }}?v={{ time() }}">
@php $feat = array_slice($games, 0, 12, true); @endphp
<div class="gm" id="gm">
  <div class="gm-stage" id="gmStage">
    <header class="gm-top"><h1>Games</h1><span class="gm-count">{{ count($games) }} games</span></header>

    <div class="gm-strip" id="gmStrip">
      @foreach($feat as $i => $g)
        <button type="button" class="gm-th {{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}" aria-label="{{ $g['title'] }}" style="--c1:{{ $g['c1'] }};--c2:{{ $g['c2'] }}">
          @if($g['image_url'])<img src="{{ $g['image_url'] }}" alt="">@else<i data-lucide="{{ $g['icon'] }}"></i>@endif
        </button>
      @endforeach
    </div>

    <section class="gm-hero" id="gmHero">
      @foreach($feat as $i => $g)
        <article class="gm-slide {{ $i === 0 ? 'on' : '' }}" style="--c1:{{ $g['c1'] }};--c2:{{ $g['c2'] }}">
          <div class="gm-art">
            @if($g['image_url'])
              @if(($g['img_w'] ?? 9999) < 700)<img class="gm-blur" src="{{ $g['image_url'] }}" alt=""><img class="gm-img gm-contain" src="{{ $g['image_url'] }}" alt="">@else<img class="gm-img" src="{{ $g['image_url'] }}" alt="">@endif
            @else<span class="gm-ico"><i data-lucide="{{ $g['icon'] }}"></i></span>@endif
            @if(!empty($g['preview']) && !empty($g['generated']))<iframe class="gm-live" data-src="{{ $g['preview'] }}" sandbox="allow-scripts" tabindex="-1" aria-hidden="true" scrolling="no"></iframe>@endif
          </div>
          <div class="gm-shade"></div>
          <div class="gm-copy">
            <small class="gm-kick">{{ $g['badge'] }} · {{ $g['genre'] }}</small>
            <h2>{{ $g['title'] }}</h2>
            <p>{{ $g['tagline'] }}</p>
            <div class="gm-btns">
              @if($g['url'])<a href="{{ $g['url'] }}" class="gm-play" data-go="{{ $g['url'] }}">Play</a>@else<button type="button" class="gm-play off" disabled>Coming soon</button>@endif
              <button type="button" class="gm-more" data-open="{{ $i }}">Details</button>
            </div>
          </div>
        </article>
      @endforeach
      <div class="gm-dots">
        @foreach($feat as $i => $g)<button type="button" class="gm-dot {{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}" aria-label="{{ $g['title'] }}"></button>@endforeach
      </div>
      <div class="gm-nav">
        <button type="button" class="gm-arr" data-dir="-1" aria-label="Previous"><i data-lucide="chevron-left"></i></button>
        <button type="button" class="gm-arr" data-dir="1" aria-label="Next"><i data-lucide="chevron-right"></i></button>
      </div>
    </section>
  </div>

  <div class="gm-body">
    <h2 class="gm-h"><span>Play now</span><i data-lucide="arrow-right"></i></h2>
    <div class="gm-row">
      @foreach($games as $i => $g)
        @if($g['live'] && $i < 14)
          <button type="button" class="gm-ic" data-open="{{ $i }}" style="--c1:{{ $g['c1'] }};--c2:{{ $g['c2'] }}">
            <span class="gm-tile">@if($g['image_url'])<img src="{{ $g['image_url'] }}" alt="" loading="lazy">@else<i data-lucide="{{ $g['icon'] }}"></i>@endif</span>
            <b>{{ $g['title'] }}</b>
          </button>
        @endif
      @endforeach
    </div>

    <h2 class="gm-h"><span>All games</span><small>{{ count($games) }}</small></h2>
    <div class="gm-grid">
      @foreach($games as $i => $g)
        <button type="button" class="gm-card" data-open="{{ $i }}" style="--c1:{{ $g['c1'] }};--c2:{{ $g['c2'] }}">
          <span class="gm-cart">@if($g['image_url'])<img src="{{ $g['image_url'] }}" alt="" loading="lazy">@else<i data-lucide="{{ $g['icon'] }}"></i>@endif</span>
          <span class="gm-cb"><b>{{ $g['title'] }}</b><small>{{ $g['genre'] }}</small></span>
        </button>
      @endforeach
    </div>
  </div>
</div>

<div class="gm-sheet" id="gmSheet" hidden>
  <div class="gm-back" data-close></div>
  <div class="gm-pane" role="dialog" aria-modal="true" aria-labelledby="sTitle">
    <div class="gm-grab"></div>
    <div class="gm-sart" id="sArt"></div>
    <small class="gm-skick" id="sKick"></small>
    <h3 id="sTitle"></h3>
    <p id="sTag"></p>
    <ul class="gm-facts"><li id="sGenre"></li></ul>
    <small class="gm-credit" id="sCredit"></small>
    <a class="gm-splay" id="sPlay" href="#">Play now</a>
    <button type="button" class="gm-close" data-close>Close</button>
  </div>
</div>

<script>
(function () {
  var G = @json($games);
  if (!G.length) return;
  var $ = function (id) { return document.getElementById(id); };
  var gm = $('gm'), stage = $('gmStage');
  var slides = [].slice.call(document.querySelectorAll('.gm-slide'));
  var N = slides.length;
  var ths = [].slice.call(document.querySelectorAll('.gm-th'));
  var dots = [].slice.call(document.querySelectorAll('.gm-dot'));
  var strip = $('gmStrip'), hero = $('gmHero'), sheet = $('gmSheet');
  var cur = 0, timer = null, closeT = null, barRoot = null, themeBg = 'rgb(255,255,255)';
  document.body.appendChild(sheet);

  function icons() { if (window.lucide) lucide.createIcons(); }
  function setImp(el, p, v) { el.style.setProperty(p, v, 'important'); }

  function theme() {
    var c = null, e = document.body;
    while (e) {
      var v = getComputedStyle(e).backgroundColor, m = v.match(/[\d.]+/g);
      if (m && m.length >= 3 && (m.length < 4 || parseFloat(m[3]) >= 0.9)) { c = m.slice(0, 3).map(Number); break; }
      e = e.parentElement;
    }
    var dark;
    if (c) dark = (0.299 * c[0] + 0.587 * c[1] + 0.114 * c[2]) < 128;
    else {
      var h = document.documentElement;
      var s = (h.className + ' ' + (h.getAttribute('data-theme') || '') + ' ' + document.body.className + ' ' + (document.body.getAttribute('data-theme') || '')).toLowerCase();
      dark = /dark/.test(s) ? !/light/.test(s) : (!/light/.test(s) && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
    }
    themeBg = c ? 'rgb(' + c.join(',') + ')' : (dark ? 'rgb(15,15,20)' : 'rgb(255,255,255)');
    var t = dark ? 'dark' : 'light';
    gm.setAttribute('data-gmt', t);
    sheet.setAttribute('data-gmt', t);
    barTone(true);
  }

  function barTone() {}
  function isGlass(x) { var c = getComputedStyle(x), v = c.backdropFilter || c.webkitBackdropFilter || ''; return v !== '' && v !== 'none'; }
  function barFix(left) {
    var vw = document.documentElement.clientWidth;
    var pts = [[vw - 34, 26], [vw - 90, 26], [vw * 0.5, 26], [34, 26]];
    gm.style.pointerEvents = 'none'; sheet.style.pointerEvents = 'none';
    var hit = null;
    for (var p = 0; p < pts.length && !hit; p++) {
      var st = document.elementsFromPoint(pts[p][0], pts[p][1]);
      for (var i = 0; i < st.length; i++) {
        var e = st[i];
        if (e === document.documentElement || e === document.body || e.contains(gm) || gm.contains(e) || sheet.contains(e) || e.closest('#nvDock')) continue;
        hit = e; break;
      }
    }
    gm.style.pointerEvents = ''; sheet.style.pointerEvents = '';
    if (hit) {
      var chain = [hit], r = hit;
      while (r.parentElement && r.parentElement !== document.body && !r.parentElement.contains(gm) && !r.parentElement.closest('#nvDock')) { r = r.parentElement; chain.push(r); }
      var rr = r.getBoundingClientRect();
      if (rr.height < 220 && rr.width > 120) {
        barRoot = r;
        var gi = -1;
        chain.forEach(function (x, i) { if (gi < 0 && isGlass(x)) gi = i; });
        chain.forEach(function (x, i) {
          var wide = x.getBoundingClientRect().width >= vw * 0.9;
          if (gi >= 0 ? i > gi : wide) {
            setImp(x, 'background-color', 'transparent'); setImp(x, 'background-image', 'none');
            setImp(x, 'box-shadow', 'none'); setImp(x, 'border-color', 'transparent');
            setImp(x, 'backdrop-filter', 'none'); setImp(x, '-webkit-backdrop-filter', 'none');
          }
        });
        if (getComputedStyle(r).position === 'static') {
          setImp(r, 'position', 'fixed'); setImp(r, 'top', rr.top + 'px'); setImp(r, 'left', rr.left + 'px');
          setImp(r, 'width', rr.width + 'px'); setImp(r, 'margin', '0');
        }
        setImp(r, 'z-index', '90001');
      }
    }
    return barRoot ? Math.max(0, barRoot.getBoundingClientRect().bottom) : 0;
  }
  function gmFit() {
    document.body.style.setProperty('padding-top', '0', 'important');
    var shell = document.querySelector('.auth-page-shell');
    var vw = document.documentElement.clientWidth;
    var left = (vw > 900 && shell) ? Math.max(0, shell.getBoundingClientRect().left) : 0;
    var over = barFix(left);
    gm.style.width = (vw - left) + 'px';
    for (var k = 0; k < 2; k++) {
      var m0 = parseFloat(gm.style.marginTop) || 0, l0 = parseFloat(gm.style.marginLeft) || 0;
      var r = gm.getBoundingClientRect();
      gm.style.marginTop = (m0 - (r.top + window.pageYOffset)) + 'px';
      gm.style.marginLeft = (l0 + (left - (r.left + window.pageXOffset))) + 'px';
    }
    var g2 = gm.getBoundingClientRect(), d = document.querySelector('#nvDock .ovd'), L = 0, R = 0, B = 0;
    if (d) {
      var q = d.getBoundingClientRect();
      if (q.width > 0 && q.height > 0) {
        if (q.width > q.height) {
          if (q.top > window.innerHeight / 2) B = Math.max(0, window.innerHeight - q.top) + 8;
          else over = Math.max(over, q.bottom);
        } else if (q.left + q.width / 2 < vw / 2) L = Math.max(0, q.right - g2.left) + 16;
        else R = Math.max(0, g2.right - q.left) + 16;
      }
    }
    gm.style.setProperty('--dkl', L + 'px');
    gm.style.setProperty('--dkr', R + 'px');
    gm.style.setProperty('--dkb', B + 'px');
    gm.style.setProperty('--over', over + 'px');
  }

  function pop(el, cb) {
    el.classList.remove('pop'); void el.offsetWidth; el.classList.add('pop');
    if (cb) setTimeout(cb, 170);
  }
  function show(i) {
    cur = (i + N) % N;
    slides.forEach(function (s, k) { s.classList.toggle('on', k === cur); });
    ths.forEach(function (t, k) { t.classList.toggle('on', k === cur); });
    dots.forEach(function (d, k) { d.classList.toggle('on', k === cur); });
    live();
    var t = ths[cur];
    if (t && strip && strip.offsetParent && strip.scrollWidth > strip.clientWidth) strip.scrollTo({ left: t.offsetLeft - (strip.clientWidth - t.offsetWidth) / 2, behavior: 'smooth' });
  }
  function live() {
    slides.forEach(function (s, k) {
      var f = s.querySelector('.gm-live'); if (!f) return;
      if (k === cur) {
        if (!f.getAttribute('src')) { f.classList.remove('ready'); f.onload = function () { f.classList.add('ready'); }; f.setAttribute('src', f.getAttribute('data-src')); }
      } else if (f.getAttribute('src')) {
        setTimeout(function () { if (slides.indexOf(s) !== cur) { f.removeAttribute('src'); f.classList.remove('ready'); } }, 700);
      }
    });
  }
  function stop() { if (timer) { clearInterval(timer); timer = null; } }
  function play() {
    stop();
    if (N > 1) timer = setInterval(function () { if (!document.hidden && sheet.hidden) show(cur + 1); }, 7000);
  }
  function openSheet(i) {
    var g = G[i], art = $('sArt');
    clearTimeout(closeT);
    sheet.style.setProperty('--c1', g.c1);
    art.textContent = '';
    if (g.image_url) { var im = document.createElement('img'); im.src = g.image_url; im.alt = ''; art.appendChild(im); }
    else { var ic = document.createElement('i'); ic.setAttribute('data-lucide', g.icon); art.appendChild(ic); }
    $('sKick').textContent = g.badge;
    $('sTitle').textContent = g.title;
    $('sTag').textContent = g.tagline;
    $('sGenre').textContent = g.genre;
    $('sCredit').textContent = g.credit ? 'Source: ' + g.credit : '';
    var p = $('sPlay');
    if (g.url) { p.setAttribute('href', g.url); p.textContent = 'Play now'; p.classList.remove('off'); }
    else { p.removeAttribute('href'); p.textContent = 'Coming soon'; p.classList.add('off'); }
    sheet.hidden = false; void sheet.offsetWidth; sheet.classList.add('open');
    document.body.style.overflow = 'hidden';
    stop(); icons();
  }
  function closeSheet() {
    sheet.classList.remove('open');
    document.body.style.overflow = '';
    closeT = setTimeout(function () { sheet.hidden = true; }, 260);
    play();
  }

  ths.forEach(function (t) { t.addEventListener('click', function () { pop(t); show(Number(t.dataset.i)); play(); }); });
  dots.forEach(function (d) { d.addEventListener('click', function () { show(Number(d.dataset.i)); play(); }); });
  [].forEach.call(document.querySelectorAll('.gm-arr'), function (a) {
    a.addEventListener('click', function () { pop(a); show(cur + Number(a.dataset.dir)); play(); });
  });
  [].forEach.call(document.querySelectorAll('[data-open]'), function (el) {
    el.addEventListener('click', function () {
      var i = Number(el.getAttribute('data-open'));
      pop(el, function () { if (i < N) show(i); openSheet(i); });
    });
  });
  [].forEach.call(document.querySelectorAll('[data-go]'), function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var u = a.getAttribute('data-go');
      pop(a, function () { location.href = u; });
    });
  });
  $('sPlay').addEventListener('click', function (e) {
    e.preventDefault();
    var u = this.getAttribute('href');
    if (!u || u === '#') return;
    pop(this, function () { location.href = u; });
  });
  [].forEach.call(sheet.querySelectorAll('[data-close]'), function (el) { el.addEventListener('click', closeSheet); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !sheet.hidden) { closeSheet(); return; }
    if (!sheet.hidden) return;
    var tag = (e.target && e.target.tagName) || '';
    if (/INPUT|TEXTAREA|SELECT/.test(tag)) return;
    if (e.key === 'ArrowRight') { show(cur + 1); play(); }
    if (e.key === 'ArrowLeft') { show(cur - 1); play(); }
  });
  hero.addEventListener('pointerenter', stop);
  hero.addEventListener('pointerleave', play);
  var x0 = null;
  hero.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
  hero.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0; x0 = null;
    if (Math.abs(dx) > 40) show(cur + (dx < 0 ? 1 : -1));
    play();
  }, { passive: true });

  window.addEventListener('scroll', function () { barTone(false); }, { passive: true });
  window.addEventListener('resize', gmFit);
  window.addEventListener('orientationchange', gmFit);
  window.addEventListener('load', function () { theme(); gmFit(); });
  try {
    var mo = new MutationObserver(theme);
    mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme', 'style'] });
    mo.observe(document.body, { attributes: true, attributeFilter: ['class', 'data-theme'] });
    if (window.matchMedia) matchMedia('(prefers-color-scheme: dark)').addEventListener('change', theme);
  } catch (e) {}
  theme(); gmFit(); setTimeout(function () { theme(); gmFit(); }, 250); setTimeout(function () { theme(); gmFit(); }, 900);
  show(0); play(); icons();
})();
</script>
@endsection
