/* Holo Balance v3 — built on the real .dqp-hero-card markup */
(function () {
  function h(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }
  function bold(root, i) {
    var s = root.querySelectorAll('.dqp-hero-holder b');
    return s[i] ? s[i].textContent.trim() : '';
  }
  function face(cls) {
    var f = h('div', 'hb-face ' + cls);
    var skin = h('div', 'hb-skin');
    skin.appendChild(h('div', 'hb-aurora a1'));
    skin.appendChild(h('div', 'hb-aurora a2'));
    skin.appendChild(h('div', 'hb-grid'));
    skin.appendChild(h('div', 'hb-foil'));
    var body = h('div', 'hb-body');
    f.appendChild(skin); f.appendChild(body);
    return { f: f, body: body };
  }

  function init() {
    var src = document.querySelector('.dqp-hero-card');
    if (!src || !window.gsap || document.querySelector('.hb-wrap')) return;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var labelEl = src.querySelector('.dqp-hero-label');
    var amountEl = src.querySelector('.dqp-hero-amount');
    var maskedEl = src.querySelector('.dqp-hero-masked');
    var holderRow = src.querySelector('.dqp-hero-holder-row');
    var actionsEl = src.querySelector('.dqp-hero-actions');
    var holderName = bold(src, 0), bankName = bold(src, 1);
    var masked = maskedEl ? maskedEl.textContent.trim() : '';

    var F = face('hb-front');
    var top = h('div', 'hb-top');
    top.appendChild(h('div', 'hb-chip'));
    top.appendChild(h('i', 'bi bi-wifi hb-nfc'));
    var mid = h('div', 'hb-mid');
    mid.appendChild(h('div', 'hb-label', labelEl ? labelEl.textContent.trim() : 'Total Balance'));
    var bottom = h('div', 'hb-bottom');
    F.body.appendChild(top); F.body.appendChild(mid); F.body.appendChild(bottom);

    var B = face('hb-back');
    var bt = h('div', 'hb-top');
    bt.appendChild(h('div', 'hb-label', 'Account details'));
    bt.appendChild(h('i', 'bi bi-bank hb-ico'));
    var bm = h('div', 'hb-mid');
    if (holderName || bankName || masked) {
      if (holderName) { bm.appendChild(h('div', 'hb-label', 'Card holder')); bm.appendChild(h('div', 'hb-line', holderName)); }
      if (bankName) { bm.appendChild(h('div', 'hb-label', 'Bank')); bm.appendChild(h('div', 'hb-line', bankName)); }
      if (masked) { bm.appendChild(h('div', 'hb-label', 'Account')); bm.appendChild(h('div', 'hb-line hb-mono', masked)); }
    } else {
      bm.appendChild(h('div', 'hb-line', 'No bank account linked yet'));
    }
    B.body.appendChild(bt); B.body.appendChild(bm); B.body.appendChild(h('div', 'hb-hint', 'Tap to flip back'));

    var wrap = h('section', 'hb-wrap');
    var stage = h('div', 'hb-stage');
    var tilt = h('div', 'hb-tilt');
    var flip = h('div', 'hb-flip');
    flip.appendChild(F.f); flip.appendChild(B.f);
    tilt.appendChild(flip); stage.appendChild(tilt); wrap.appendChild(stage);
    var dots = h('div', 'hb-dots');
    var d0 = h('span', 'hb-dot on'), d1 = h('span', 'hb-dot');
    dots.appendChild(d0); dots.appendChild(d1); wrap.appendChild(dots);

    try {
      if (amountEl) mid.appendChild(amountEl);
      if (maskedEl) mid.appendChild(maskedEl);
      if (holderRow) bottom.appendChild(holderRow);
      if (actionsEl) bottom.appendChild(actionsEl);
      src.parentNode.insertBefore(wrap, src);
      src.classList.add('hb-source');
    } catch (err) {
      console.error('[holo-balance]', err);
      return;
    }

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
    gsap.set(stage, { opacity: 0, y: 36, scale: .94 });
    gsap.set(flip, { rotationX: 24 });
    gsap.timeline()
      .to(stage, { opacity: 1, y: 0, scale: 1, duration: .9, ease: 'power3.out' })
      .to(flip, { rotationX: 0, duration: 1.1, ease: 'elastic.out(1,.55)' }, 0);
    gsap.to(tilt, { y: -5, duration: 2.6, yoyo: true, repeat: -1, ease: 'sine.inOut' });
    gsap.to(wrap.querySelectorAll('.a1'), { x: '25%', y: '18%', duration: 7, yoyo: true, repeat: -1, ease: 'sine.inOut' });
    gsap.to(wrap.querySelectorAll('.a2'), { x: '-20%', y: '-22%', duration: 9, yoyo: true, repeat: -1, ease: 'sine.inOut' });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
