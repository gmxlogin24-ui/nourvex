document.addEventListener('DOMContentLoaded', function () {

  if (window.gsap && window.ScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
  }

  /* ---------------- Lenis smooth momentum scroll ---------------- */
  if (window.Lenis) {
    const lenis = new Lenis({ duration: 1.1, smoothWheel: true });
    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);
    if (window.ScrollTrigger) {
      lenis.on('scroll', ScrollTrigger.update);
      gsap.ticker.add((time) => { lenis.raf(time * 1000); });
      gsap.ticker.lagSmoothing(0);
    }
  }

  /* ---------------- Hero entrance ---------------- */
  if (window.gsap) {
    gsap.from('#heroHeadline, .hero-eyebrow, .hero-sub, .hero-cta, .hero-social', {
      y: 26, opacity: 0, duration: 0.9, stagger: 0.12, ease: 'power3.out'
    });
  }

  /* ---------------- Generic text-reveal (used sitewide, incl. logged-in pages) ---------------- */
  if (window.gsap) {
    document.querySelectorAll('.text-reveal').forEach(function (el) {
      if (window.ScrollTrigger) {
        gsap.to(el, {
          y: 0, opacity: 1, duration: 0.8, ease: 'power3.out',
          scrollTrigger: { trigger: el, start: 'top 88%', once: true }
        });
      } else {
        gsap.to(el, { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out' });
      }
    });
  }

  /* ---------------- Magnetic buttons ---------------- */
  document.querySelectorAll('.magnetic').forEach(function (btn) {
    let bounds;
    btn.addEventListener('mouseenter', function () { bounds = btn.getBoundingClientRect(); });
    btn.addEventListener('mousemove', function (e) {
      if (!bounds) bounds = btn.getBoundingClientRect();
      const relX = e.clientX - bounds.left - bounds.width / 2;
      const relY = e.clientY - bounds.top - bounds.height / 2;
      btn.style.transform = 'translate(' + (relX * 0.25) + 'px,' + (relY * 0.4) + 'px)';
    });
    btn.addEventListener('mouseleave', function () {
      btn.style.transform = 'translate(0,0)';
    });
  });

  /* ---------------- Marquee: duplicate content for seamless loop ---------------- */
  document.querySelectorAll('.motion-marquee-track').forEach(function (track) {
    track.innerHTML += track.innerHTML;
  });

  /* ---------------- Motion zone background: mesh drift + particles ---------------- */
  const mesh = document.getElementById('motionMesh');
  const zone = document.getElementById('motionZone');
  if (window.gsap && window.ScrollTrigger && mesh && zone) {
    gsap.to(mesh, {
      rotation: 12, xPercent: 4, ease: 'none',
      scrollTrigger: { trigger: zone, start: 'top top', end: 'bottom top', scrub: 1 }
    });
    ['motionBlob1', 'motionBlob2', 'motionBlob3'].forEach(function (id, i) {
      const el = document.getElementById(id);
      if (!el) return;
      gsap.to(el, {
        y: 120 + i * 40, x: (i % 2 === 0 ? -1 : 1) * (60 + i * 20), ease: 'none',
        scrollTrigger: { trigger: zone, start: 'top top', end: 'bottom top', scrub: 1 }
      });
    });
  }
  const particleWrap = document.getElementById('motionParticles');
  if (particleWrap) {
    for (let i = 0; i < 20; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      p.style.left = Math.random() * 100 + '%';
      p.style.top = Math.random() * 100 + '%';
      particleWrap.appendChild(p);
      if (window.gsap) {
        gsap.to(p, {
          y: -window.innerHeight, duration: 14 + Math.random() * 12,
          repeat: -1, delay: Math.random() * 10, ease: 'none', opacity: 0.4
        });
      }
    }
  }

  /* ---------------- Horizontal pinned "how it works" steps ---------------- */
  const stepsTrack = document.getElementById('motionStepsTrack');
  const stepsPin = document.getElementById('motionStepsPin');
  if (window.gsap && window.ScrollTrigger && stepsTrack && stepsPin) {
    const distance = stepsTrack.scrollWidth - window.innerWidth + 120;
    gsap.to(stepsTrack, {
      x: -Math.max(distance, 0), ease: 'none',
      scrollTrigger: { trigger: stepsPin, start: 'top top', end: 'bottom top', scrub: 0.6 }
    });
  }

  /* ---------------- Stat count-up on scroll ---------------- */
  document.querySelectorAll('.stat-num-live').forEach(function (el) {
    const target = parseFloat(el.dataset.target);
    const decimals = parseInt(el.dataset.decimal || 0);
    const suffix = el.dataset.suffix || '';
    if (window.gsap && window.ScrollTrigger) {
      ScrollTrigger.create({
        trigger: el, start: 'top 88%', once: true,
        onEnter: function () {
          const proxy = { v: 0 };
          gsap.to(proxy, {
            v: target, duration: 1.6, ease: 'power2.out',
            onUpdate: function () { el.textContent = proxy.v.toFixed(decimals) + suffix; }
          });
        }
      });
    } else {
      el.textContent = target.toFixed(decimals) + suffix;
    }
  });

  /* ---------------- Cloth-touch background (mobile only) ---------------- */
  const isMobile = window.matchMedia('(max-width: 768px)').matches;
  const canvas = document.getElementById('clothCanvas');
  if (isMobile && canvas && canvas.getContext) {
    const ctx = canvas.getContext('2d');
    let w, h, points = [];
    const spacing = 26;
    const touch = { x: -9999, y: -9999, active: false };

    function resize() {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
      points = [];
      for (let y = 0; y <= h + spacing; y += spacing) {
        for (let x = 0; x <= w + spacing; x += spacing) {
          points.push({ ox: x, oy: y, x: x, y: y, vx: 0, vy: 0 });
        }
      }
    }
    resize();
    window.addEventListener('resize', resize);

    function setTouch(clientX, clientY, active) {
      touch.x = clientX; touch.y = clientY; touch.active = active;
    }
    canvas.addEventListener('touchstart', function (e) {
      const t = e.touches[0]; setTouch(t.clientX, t.clientY, true);
    }, { passive: true });
    canvas.addEventListener('touchmove', function (e) {
      const t = e.touches[0]; setTouch(t.clientX, t.clientY, true);
    }, { passive: true });
    canvas.addEventListener('touchend', function () {
      touch.active = false; touch.x = -9999; touch.y = -9999;
    }, { passive: true });

    const radius = 130, pull = 22, spring = 0.12, damping = 0.80;

    function tick() {
      ctx.clearRect(0, 0, w, h);
      ctx.fillStyle = 'rgba(124,92,252,0.45)';
      for (let i = 0; i < points.length; i++) {
        const p = points[i];
        let targetX = p.ox, targetY = p.oy;
        if (touch.active) {
          const dx = touch.x - p.ox, dy = touch.y - p.oy;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < radius) {
            const force = (1 - dist / radius) * pull;
            const angle = Math.atan2(dy, dx);
            targetX = p.ox + Math.cos(angle) * force;
            targetY = p.oy + Math.sin(angle) * force;
          }
        }
        p.vx = (p.vx + (targetX - p.x) * spring) * damping;
        p.vy = (p.vy + (targetY - p.y) * spring) * damping;
        p.x += p.vx; p.y += p.vy;
        const disp = Math.hypot(p.x - p.ox, p.y - p.oy);
        const r = 1.2 + Math.min(disp * 0.08, 2.5);
        ctx.beginPath();
        ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
        ctx.fill();
      }
      requestAnimationFrame(tick);
    }
    tick();
  }
});
