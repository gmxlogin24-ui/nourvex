document.addEventListener('DOMContentLoaded', function () {
  const inner = document.getElementById('balanceFlipInner');
  const card = document.getElementById('balanceFlipCard');
  if (!inner || !card) return;

  const hasGsap = typeof gsap !== 'undefined';
  let rotation = 0;
  let dragging = false;
  let startX = 0;
  let startRotation = 0;
  let idleTween = null;

  function setRotation(r, animate) {
    rotation = r;
    if (hasGsap && animate) {
      gsap.to(inner, { rotationY: rotation, duration: 0.7, ease: 'power3.out', overwrite: true });
    } else {
      inner.style.transform = 'rotateY(' + rotation + 'deg)';
    }
  }

  function nearestFace() {
    const mod = ((rotation % 360) + 360) % 360;
    return mod > 90 && mod < 270 ? 180 : 0;
  }

  function snap() {
    const base = Math.round(rotation / 360) * 360;
    const target = base + nearestFace();
    setRotation(target, true);
    startIdle();
  }

  function startIdle() {
    if (!hasGsap) return;
    if (idleTween) idleTween.kill();
    const base = rotation;
    idleTween = gsap.to(inner, {
      rotationY: base + 6, duration: 2.6, ease: 'sine.inOut', yoyo: true, repeat: -1,
      onUpdate: function () { rotation = gsap.getProperty(inner, 'rotationY'); }
    });
  }
  function stopIdle() { if (idleTween) idleTween.kill(); }

  card.style.transformStyle = 'preserve-3d';
  inner.style.transformStyle = 'preserve-3d';
  inner.style.willChange = 'transform';

  card.addEventListener('pointerdown', function (e) {
    dragging = true;
    stopIdle();
    startX = e.clientX;
    startRotation = rotation;
    card.setPointerCapture(e.pointerId);
  });
  card.addEventListener('pointermove', function (e) {
    if (!dragging) return;
    const dx = e.clientX - startX;
    setRotation(startRotation + dx * 0.5, false);
  });
  function endDrag() {
    if (!dragging) return;
    dragging = false;
    snap();
  }
  card.addEventListener('pointerup', endDrag);
  card.addEventListener('pointercancel', endDrag);
  card.addEventListener('pointerleave', function () { if (dragging) endDrag(); });

  // auto light-sweep tilt following pointer (for the CSS radial highlight vars)
  const isTouch = matchMedia('(pointer:coarse)').matches;
  if (!isTouch) {
    card.addEventListener('mousemove', function (e) {
      const r = card.getBoundingClientRect();
      const px = ((e.clientX - r.left) / r.width) * 100;
      const py = ((e.clientY - r.top) / r.height) * 100;
      card.style.setProperty('--mx', px + '%');
      card.style.setProperty('--my', py + '%');
    });
  }

  setRotation(0, false);
  startIdle();
});
