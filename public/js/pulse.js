document.addEventListener('DOMContentLoaded', function () {
  if (typeof gsap === 'undefined') return;
  gsap.registerPlugin(ScrollTrigger, ScrollToPlugin, SplitText);

  const feed = document.getElementById('pulseFeed');
  if (!feed) return;
  const cards = gsap.utils.toArray('.pulse-card');
  if (!cards.length) return;

  /* ---------- pulse rail (heartbeat ticks) ---------- */
  const rail = document.getElementById('pulseRail');
  cards.forEach(c => { const t = document.createElement('div'); t.className = 'tick'; t.dataset.type = c.dataset.type; rail.appendChild(t); });
  const ticks = gsap.utils.toArray('.pulse-rail .tick');
  let activeIndex = 0;

  // declared early and reassigned later (once Three.js sets it up) — calling
  // tintScene before that point just does nothing instead of crashing
  let targetColor = null;
  function tintScene(type) {
    if (!targetColor) return;
    targetColor.set(type === 'art' ? 0xff3d7f : 0x3ddcff);
  }

  function setActive(idx) {
    activeIndex = idx === null ? activeIndex : idx;
    ticks.forEach((t, ti) => {
      t.classList.remove('active-art', 'active-news');
      if (ti === idx) t.classList.add(t.dataset.type === 'art' ? 'active-art' : 'active-news');
    });
    if (idx !== null) tintScene(cards[idx].dataset.type);
  }

  /* ---------- text + media reveal per card ---------- */
  cards.forEach((card, i) => {
    const titleEl = card.querySelector('.pulse-card__title');
    const bodyEl  = card.querySelector('.pulse-card__body');
    const eyebrow = card.querySelector('.pulse-card__eyebrow');
    const meta    = card.querySelector('.pulse-card__meta');
    const tiles   = card.querySelectorAll('.tile');

    const titleSplit = new SplitText(titleEl, { type: 'chars', charsClass: 'char' });
    const bodySplit  = new SplitText(bodyEl,  { type: 'words', wordsClass: 'word' });

    gsap.set(titleSplit.chars, { yPercent: 130, rotate: 6, opacity: 0 });
    gsap.set(bodySplit.words, { yPercent: 60, opacity: 0, filter: 'blur(6px)' });
    gsap.set(eyebrow, { opacity: 0, x: -16 });
    if (meta) gsap.set(meta.children, { opacity: 0, y: 10 });
    gsap.set(tiles, { scale: 1.22 });

    function animateIn() {
      const tl = gsap.timeline()
        .to(tiles, { scale: 1.05, duration: 1.3, ease: 'power2.out' }, 0)
        .to(eyebrow, { opacity: 1, x: 0, duration: .5, ease: 'power3.out' }, .05)
        .to(titleSplit.chars, { yPercent: 0, rotate: 0, opacity: 1, duration: .9, ease: 'power4.out', stagger: .018 }, .12)
        .to(bodySplit.words, { yPercent: 0, opacity: 1, filter: 'blur(0px)', duration: .6, ease: 'power3.out', stagger: .012 }, .3);
      if (meta) tl.to(meta.children, { opacity: 1, y: 0, duration: .5, ease: 'power3.out', stagger: .08 }, .5);
    }

    ScrollTrigger.create({
      trigger: card, scroller: '#pulseFeed', start: 'top 60%', end: 'bottom top',
      onToggle: self => { if (self.isActive) { setActive(i); animateIn(); } }
    });
  });

  window.addEventListener('load', () => ScrollTrigger.refresh());

  /* ---------- self-playing auto-scroll ---------- */
  const AUTO_DELAY = 5200, RESUME_DELAY = 4500;
  let autoTimer, resumeTimer;
  const indicator = document.getElementById('autoplayIndicator');

  function goToCard(idx) {
    idx = (idx + cards.length) % cards.length;
    gsap.to(feed, { duration: 1.1, ease: 'power3.inOut', scrollTo: { y: cards[idx].offsetTop } });
  }
  function scheduleAuto() {
    clearTimeout(autoTimer);
    autoTimer = setTimeout(() => { indicator.classList.add('show'); goToCard(activeIndex + 1); scheduleAuto(); }, AUTO_DELAY);
  }
  function pauseAuto() {
    indicator.classList.remove('show');
    clearTimeout(autoTimer); clearTimeout(resumeTimer);
    resumeTimer = setTimeout(scheduleAuto, RESUME_DELAY);
  }
  ['wheel', 'touchstart', 'pointerdown'].forEach(evt => feed.addEventListener(evt, pauseAuto, { passive: true }));
  scheduleAuto();

  /* ---------- paywall gate ---------- */
  const gate = document.getElementById('pulseGate');
  const gateCard = document.getElementById('pulseGateCard');
  function openGate() { gate.classList.add('show'); gsap.to(gateCard, { opacity: 1, y: 0, scale: 1, duration: .4, ease: 'back.out(1.6)' }); }
  function closeGate() { gsap.to(gateCard, { opacity: 0, y: 24, scale: .96, duration: .25, onComplete: () => gate.classList.remove('show') }); }
  const postBtn = document.getElementById('pulsePostBtn');
  const composerTrigger = document.getElementById('pulseComposerTrigger');
  const gateClose = document.getElementById('pulseGateClose');
  if (postBtn) postBtn.addEventListener('click', openGate);
  if (composerTrigger) composerTrigger.addEventListener('click', openGate);
  if (gateClose) gateClose.addEventListener('click', closeGate);
  gate.addEventListener('click', e => { if (e.target === gate) closeGate(); });

  /* ---------- three.js ambient particle layer ---------- */
  if (typeof THREE === 'undefined') return;
  const canvas = document.getElementById('pulseBgCanvas');
  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
  renderer.setSize(innerWidth, innerHeight);

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(55, innerWidth / innerHeight, 0.1, 100);
  camera.position.z = 8;

  const PARTICLE_COUNT = 260;
  const positions = new Float32Array(PARTICLE_COUNT * 3);
  for (let i = 0; i < PARTICLE_COUNT; i++) {
    positions[i * 3]   = (Math.random() - 0.5) * 16;
    positions[i * 3 + 1] = (Math.random() - 0.5) * 10;
    positions[i * 3 + 2] = (Math.random() - 0.5) * 8;
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  const mat = new THREE.PointsMaterial({ color: 0xff3d7f, size: 0.06, transparent: true, opacity: .75, blending: THREE.AdditiveBlending });
  const points = new THREE.Points(geo, mat);
  scene.add(points);

  targetColor = new THREE.Color(0xff3d7f);

  const pointer = { x: 0, y: 0 };
  const smooth = { x: 0, y: 0 };

  window.addEventListener('mousemove', e => {
    pointer.x = (e.clientX / innerWidth - 0.5) * 2;
    pointer.y = (e.clientY / innerHeight - 0.5) * 2;
  });

  function onTilt(e) {
    const gamma = gsap.utils.clamp(-30, 30, e.gamma || 0) / 30;
    const beta  = gsap.utils.clamp(-30, 30, (e.beta || 0) - 40) / 30;
    pointer.x = gamma; pointer.y = beta;
  }
  const motionBtn = document.getElementById('motionPermit');
  if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
    motionBtn.classList.add('show');
    motionBtn.addEventListener('click', () => {
      DeviceOrientationEvent.requestPermission().then(res => {
        if (res === 'granted') { window.addEventListener('deviceorientation', onTilt); motionBtn.classList.remove('show'); }
      });
    });
  } else if ('DeviceOrientationEvent' in window) {
    window.addEventListener('deviceorientation', onTilt);
  }

  function animate() {
    requestAnimationFrame(animate);
    smooth.x += (pointer.x - smooth.x) * 0.04;
    smooth.y += (pointer.y - smooth.y) * 0.04;
    points.rotation.y = smooth.x * 0.35;
    points.rotation.x = smooth.y * 0.2;
    camera.position.x = smooth.x * 0.6;
    camera.position.y = -smooth.y * 0.4;
    camera.lookAt(0, 0, 0);
    mat.color.lerp(targetColor, 0.04);
    renderer.render(scene, camera);
  }
  animate();

  window.addEventListener('resize', () => {
    renderer.setSize(innerWidth, innerHeight);
    camera.aspect = innerWidth / innerHeight;
    camera.updateProjectionMatrix();
  });
});

document.querySelectorAll('.rail-btn--like, .rail-btn--save').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const wasActive = btn.classList.contains('is-active');
        btn.classList.toggle('is-active');
        if (!wasActive) {
            btn.classList.add('bursting');
            setTimeout(function () { btn.classList.remove('bursting'); }, 500);
        }
    });
});
