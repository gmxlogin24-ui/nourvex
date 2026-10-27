<script src="{{ asset('js/no-motion.js') }}"></script>
<script>
(function () {
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (window.lucide) lucide.createIcons();
  var more = document.querySelector('.dg-more');
  if (more) document.addEventListener('click', function (e) { if (more.open && !more.contains(e.target)) more.open = false; });
  if (!window.gsap) return;

  /* alive: entrance, count-up, scroll reveal, press spring */
  if (!reduce) {
    gsap.from('.dg-kick, .dg-hero h2, .dg-chip', { y: 22, opacity: 0, duration: .7, stagger: .1, delay: .15, ease: 'power3.out', clearProps: 'transform,opacity' });
    gsap.from('.dg-earn', { y: 34, opacity: 0, scale: .96, duration: .75, delay: .3, ease: 'back.out(1.4)', clearProps: 'transform,opacity' });
    gsap.from('.dg-bars i', { scaleY: 0, transformOrigin: '50% 100%', duration: .7, stagger: .06, delay: .6, ease: 'elastic.out(1,.6)' });
    gsap.from('.dg-quick', { x: 30, opacity: 0, duration: .7, delay: .4, ease: 'power3.out', clearProps: 'transform,opacity' });
    gsap.from('.dg-quick .dg-it', { scale: .5, opacity: 0, duration: .55, stagger: .09, delay: .6, ease: 'back.out(2)', clearProps: 'transform,opacity' });
    gsap.from('.dg-stage', { x: 40, opacity: 0, duration: .8, delay: .5, ease: 'power3.out', clearProps: 'transform,opacity' });
    var big = document.querySelector('.dg-earn-t b');
    if (big) {
      var raw = big.textContent, n = parseInt(raw.replace(/[^0-9]/g, ''), 10) || 0, o = { v: 0 };
      gsap.to(o, { v: n, duration: 1.4, delay: .5, ease: 'power2.out',
        onUpdate: function () { big.textContent = Math.round(o.v * {{ (float) config('qcoin.q_per_naira_display', 10) }}).toLocaleString() + ' Q'; },
        onComplete: function () { big.textContent = raw; } });
    }
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
          if (!e.isIntersecting) return;
          gsap.to(e.target, { y: 0, opacity: 1, scale: 1, duration: .7, ease: 'power3.out', clearProps: 'transform,opacity' });
          io.unobserve(e.target);
        });
      }, { threshold: .15 });
      [].slice.call(document.querySelectorAll('.dg-hlh, .dg-card')).forEach(function (el) {
        gsap.set(el, { y: 40, opacity: 0, scale: .94 }); io.observe(el);
      });
    }
    [].slice.call(document.querySelectorAll('.dg-card, .dg-it, .dg-earn')).forEach(function (el) {
      el.addEventListener('pointerdown', function () { gsap.to(el, { scale: .95, duration: .12 }); });
      ['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) {
        el.addEventListener(ev, function () { gsap.to(el, { scale: 1, duration: .5, ease: 'elastic.out(1,.5)' }); });
      });
    });
  }

  /* hustles: falls once, stays down (remembered), and the strip sways from its top */
  var drop = document.getElementById('dgDrop');
  if (drop) {
    var swing = drop.querySelector('.dg-swing');
    var KEY = 'ov_hustles_down_v1', already = false;
    try { already = localStorage.getItem(KEY) === '1'; } catch (e) {}
    if (already || reduce) {
      gsap.set(drop, { y: 0 });
    } else {
      gsap.set(drop, { y: -300 });
      gsap.to(drop, { y: 0, duration: 1.1, delay: 1.2, ease: 'bounce.out',
        onComplete: function () { try { localStorage.setItem(KEY, '1'); } catch (e) {} } });
    }
    if (swing && !reduce) gsap.to(swing, { rotation: 3, duration: 1.8, yoyo: true, repeat: -1, ease: 'sine.inOut' });
  }

  if (!document.getElementById('dgCoin')) return;
  /* coin: floats, slides, taps through to Market */
  var coin = document.getElementById('dgCoin'), shut = document.getElementById('dgShut'), strips = [];
  ['#3F5BFF', '#5B76FF', '#3F5BFF', '#5B76FF', '#3F5BFF', '#5B76FF'].forEach(function (col) {
    var s = document.createElement('i'); s.style.background = col; shut.appendChild(s); strips.push(s);
  });
  function shutters(url) {
    gsap.timeline({ onComplete: function () { location.href = url; } })
      .to(strips, { scaleY: 1, duration: reduce ? 0 : .45, stagger: reduce ? 0 : .07, ease: 'power3.in' });
  }
  var tl = null;
  if (!reduce) {
    gsap.to('#dgCoinIn', { rotationY: 360, duration: 2.4, repeat: -1, ease: 'none' });
    tl = gsap.timeline({ delay: .8 });
    tl.to(coin, { x: 96, duration: .55, ease: 'back.out(1.6)' });
  } else {
    gsap.set(coin, { x: 96 });
  }
  var sx = 0, down = false;
  coin.addEventListener('pointerdown', function (e) { sx = e.clientX; down = true; if (tl) tl.pause(); coin.setPointerCapture(e.pointerId); });
  coin.addEventListener('pointerup', function (e) {
    if (!down) return; down = false;
    var dx = e.clientX - sx;
    if (dx > 24 || Math.abs(dx) < 6) shutters(coin.dataset.go);
    else if (tl) tl.resume();
  });
  coin.addEventListener('pointercancel', function () { down = false; if (tl) tl.resume(); });
  window.addEventListener('pageshow', function () { gsap.set(strips, { scaleY: 0 }); });
})();
</script>
