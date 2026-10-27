(function () {
  if (window.__qcHold) return;
  window.__qcHold = true;

  var HOLD_MS = 1500;
  var PREVIEW = location.search.indexOf('qc_preview=1') > -1;
  var reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  var meta = document.querySelector('meta[name="csrf-token"]');
  var csrf = meta ? meta.getAttribute('content') : '';

  function buzz(p) { try { if (navigator.vibrate) navigator.vibrate(p); } catch (e) {} }

  function init(btn) {
    var wrap = btn.closest('.qh-wrap');
    if (!wrap) return;
    var txt = btn.querySelector('.qh-txt');
    var hint = wrap.querySelector('.qh-hint');
    var fx = wrap.querySelector('.qh-fx');
    var dots = [].slice.call(btn.querySelectorAll('.qh-dots i'));
    var idle = btn.getAttribute('data-label-idle') || '';
    var idleHint = 'Press and hold for 1.5 seconds';
    var start = 0, raf = 0, busy = false, ticks = 0, reloaded = false, dotAnims = [], timer = 0;

    if (PREVIEW) btn.disabled = false;

    function setP(v) { btn.style.setProperty('--p', v); }
    function say(t) { if (txt) txt.textContent = t; }
    function note(t) { if (hint) hint.textContent = t; }
    function reload() { if (!reloaded) { reloaded = true; location.reload(); } }

    function reset() {
      cancelAnimationFrame(raf);
      start = 0;
      btn.classList.remove('qh-holding');
      setP(0);
      say(idle);
      note(btn.disabled ? '' : idleHint);
    }

    function tick(now) {
      var p = Math.min(1, (now - start) / HOLD_MS);
      setP(Math.round(p * 100));
      var step = Math.floor(p * 6);
      if (step > ticks) { ticks = step; buzz(8); }
      if (p >= 1) { fire(); return; }
      raf = requestAnimationFrame(tick);
    }

    function press(e) {
      if (busy || btn.disabled) return;
      if (e.type === 'keydown') {
        if (e.repeat || (e.key !== ' ' && e.key !== 'Enter')) return;
      } else if (e.button !== undefined && e.button !== 0) {
        return;
      }
      e.preventDefault();
      start = performance.now();
      ticks = 0;
      btn.classList.add('qh-holding');
      say('Keep holding…');
      note('');
      raf = requestAnimationFrame(tick);
    }

    function release(e) {
      if (busy || !start) return;
      if (e && e.type === 'keyup' && e.key !== ' ' && e.key !== 'Enter') return;
      reset();
    }

    function startDots() {
      if (reduce) return;
      dotAnims = dots.map(function (d, i) {
        return d.animate([{ transform: 'translateY(0)' }, { transform: 'translateY(-5px)' }, { transform: 'translateY(0)' }],
          { duration: 600, iterations: Infinity, delay: i * 120 });
      });
    }
    function stopDots() { dotAnims.forEach(function (a) { a.cancel(); }); dotAnims = []; }

    function fire() {
      busy = true;
      start = 0;
      cancelAnimationFrame(raf);
      btn.classList.remove('qh-holding');
      btn.classList.add('qh-loading');
      btn.disabled = true;
      setP(100);
      say('Minting your Q');
      note('');
      buzz([12, 30, 12]);
      startDots();
      if (PREVIEW) { setTimeout(function () { celebrate(50); }, 1100); return; }
      fetch(btn.getAttribute('data-action'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
      })
        .then(function (r) { return r.text(); })
        .then(function (html) {
          var m = /You claimed ([\d,]+) Q Coins/.exec(html || '');
          if (m) { celebrate(parseInt(m[1].replace(/,/g, ''), 10) || 0); return; }
          if (/already claimed/i.test(html || '')) { alreadyDone(); return; }
          failed();
        })
        .catch(failed);
    }

    function celebrate(amt) {
      stopDots();
      btn.classList.remove('qh-loading');
      btn.classList.add('qh-done');
      buzz([30, 40, 70]);
      note('Bag secured. Balance updating');
      var t0 = performance.now();
      function count(now) {
        var k = Math.min(1, (now - t0) / 900);
        say('Claimed +' + Math.round(amt * (1 - Math.pow(1 - k, 3))) + ' Q');
        if (k < 1) requestAnimationFrame(count);
      }
      requestAnimationFrame(count);
      if (!reduce) burst(18);
      timer = setTimeout(reload, 2300);
    }

    function alreadyDone() {
      stopDots();
      btn.classList.remove('qh-loading');
      say('Already claimed today');
      note('');
    }

    function failed() {
      busy = false;
      stopDots();
      btn.classList.remove('qh-loading');
      btn.disabled = false;
      reset();
      note("Couldn't claim just now. Hold to try again.");
    }

    function burst(n) {
      if (!fx) return;
      for (var i = 0; i < n; i++) {
        var star = i % 3 === 2;
        var el = document.createElement('span');
        el.className = 'qh-p' + (star ? ' qh-star' : '');
        el.textContent = star ? '✦' : 'Q';
        fx.appendChild(el);
        var ang = -Math.PI / 2 + (Math.random() - 0.5) * Math.PI * 1.4;
        var d = 70 + Math.random() * 70;
        var dx = Math.cos(ang) * d, dy = Math.sin(ang) * d - 20;
        var anim = el.animate([
          { transform: 'translate(0,0) scale(.3) rotate(0deg)', opacity: 1 },
          { transform: 'translate(' + dx + 'px,' + dy + 'px) scale(1.05) rotate(200deg)', opacity: 1, offset: 0.6 },
          { transform: 'translate(' + dx + 'px,' + (dy + 46) + 'px) scale(.9) rotate(360deg)', opacity: 0 }
        ], { duration: 1000 + Math.random() * 400, easing: 'cubic-bezier(.2,.7,.3,1)', fill: 'forwards' });
        (function (node, a) { a.onfinish = function () { node.remove(); }; })(el, anim);
      }
    }

    btn.addEventListener('pointerdown', press);
    btn.addEventListener('pointerup', release);
    btn.addEventListener('pointerleave', release);
    btn.addEventListener('pointercancel', release);
    btn.addEventListener('keydown', press);
    btn.addEventListener('keyup', release);
    btn.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    reset();
  }

  [].slice.call(document.querySelectorAll('[data-qc-hold]')).forEach(init);
})();
