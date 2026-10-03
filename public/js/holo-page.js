(function () {
  var stage = document.getElementById('hbStage');
  var tilt = document.getElementById('hbTilt');
  var flip = document.getElementById('hbFlip');
  if (!stage || !tilt || !flip || !window.gsap) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var d0 = document.getElementById('hbD0'), d1 = document.getElementById('hbD1');

  var qx = gsap.quickTo(tilt, 'rotationX', { duration: .5, ease: 'power3.out' });
  var qy = gsap.quickTo(tilt, 'rotationY', { duration: .5, ease: 'power3.out' });
  function light(px, py) {
    tilt.style.setProperty('--mx', (px * 100) + '%');
    tilt.style.setProperty('--my', (py * 100) + '%');
    tilt.style.setProperty('--sx', (px * 100) + '%');
    tilt.style.setProperty('--hue', Math.round(140 + px * 200));
  }
  var pointerOn = false, moved = false, x0 = 0, y0 = 0;
  stage.addEventListener('pointerdown', function (e) { moved = false; x0 = e.clientX; y0 = e.clientY; });
  stage.addEventListener('pointermove', function (e) {
    var r = stage.getBoundingClientRect();
    var px = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
    var py = Math.min(1, Math.max(0, (e.clientY - r.top) / r.height));
    pointerOn = true;
    qy((px - .5) * 20); qx(-(py - .5) * 20); light(px, py);
    if (Math.abs(e.clientX - x0) + Math.abs(e.clientY - y0) > 10) moved = true;
  });
  stage.addEventListener('pointerleave', function () { pointerOn = false; qx(0); qy(0); light(.3, .2); });
  window.addEventListener('deviceorientation', function (e) {
    if (pointerOn || reduce || e.gamma == null) return;
    var g = Math.max(-20, Math.min(20, e.gamma));
    var b = Math.max(-20, Math.min(20, (e.beta || 0) - 45));
    qy(g * .6); qx(-b * .5); light(.5 + g / 60, .5 + b / 60);
  });

  var flipped = false;
  stage.addEventListener('click', function (e) {
    if (moved || e.target.closest('a,button')) return;
    flipped = !flipped;
    gsap.to(flip, { rotationY: flipped ? 180 : 0, duration: .85, ease: 'back.out(1.4)' });
    gsap.fromTo(stage, { scale: .96 }, { scale: 1, duration: .5, ease: 'elastic.out(1,.5)' });
    d0.classList.toggle('on', !flipped); d1.classList.toggle('on', flipped);
  });

  if (reduce) return;
  var cnt = document.querySelector('.hb-count');
  gsap.set(stage, { opacity: 0, y: 36, scale: .94 });
  gsap.set(flip, { rotationX: 24 });
  gsap.timeline()
    .to(stage, { opacity: 1, y: 0, scale: 1, duration: .9, ease: 'power3.out' })
    .to(flip, { rotationX: 0, duration: 1.1, ease: 'elastic.out(1,.55)' }, 0);
  if (cnt) {
    var end = parseFloat(cnt.getAttribute('data-value')) || 0, final = cnt.textContent, o = { v: 0 };
    gsap.to(o, { v: end, duration: 1.4, delay: .25, ease: 'power3.out',
      onUpdate: function () { cnt.textContent = o.v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
      onComplete: function () { cnt.textContent = final; } });
  }
  gsap.to(tilt, { y: -5, duration: 2.6, yoyo: true, repeat: -1, ease: 'sine.inOut' });
  gsap.to('.hb-wrap .a1', { x: '25%', y: '18%', duration: 7, yoyo: true, repeat: -1, ease: 'sine.inOut' });
  gsap.to('.hb-wrap .a2', { x: '-20%', y: '-22%', duration: 9, yoyo: true, repeat: -1, ease: 'sine.inOut' });
})();
