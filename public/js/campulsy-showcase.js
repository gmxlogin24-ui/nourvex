document.addEventListener('DOMContentLoaded', () => {
  const pillarCards = document.querySelectorAll('.cp-pillar-card');
  if (pillarCards.length) {
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (!entry.isIntersecting) return;
        const card = entry.target;
        revealObserver.unobserve(card);
        setTimeout(() => {
          if (window.gsap) gsap.to(card, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' });
          else { card.style.opacity = 1; card.style.transform = 'none'; }
        }, i * 90);

        const valueEl = card.querySelector('.cp-pillar-value');
        if (valueEl && valueEl.dataset.count) {
          const target = parseInt(valueEl.dataset.count, 10);
          const suffix = valueEl.dataset.suffix || '';
          const start = performance.now();
          function tick(now) {
            const p = Math.min(1, (now - start) / 1200);
            const eased = 1 - Math.pow(1 - p, 3);
            valueEl.textContent = Math.round(target * eased).toLocaleString() + suffix;
            if (p < 1) requestAnimationFrame(tick);
          }
          requestAnimationFrame(tick);
        }
      });
    }, { threshold: 0.3 });
    pillarCards.forEach(card => revealObserver.observe(card));
  }

  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.registerPlugin(ScrollTrigger);

  const mm = gsap.matchMedia();
  mm.add('(min-width: 0px)', () => {
    const pin = document.getElementById('cpStoryPin');
    const track = document.getElementById('cpStoryTrack');
    const dots = document.querySelectorAll('.cp-story-dots span');
    const cards = document.querySelectorAll('.cp-story-card');
    if (!pin || !track) return;

    const getDistance = () => track.scrollWidth - window.innerWidth + 80;

    const tween = gsap.to(track, {
      x: () => -getDistance(),
      ease: 'none',
      scrollTrigger: {
        trigger: pin,
        start: 'top top',
        end: () => '+=' + (getDistance() + window.innerHeight * 0.25),
        scrub: 0.6,
        pin: true,
        anticipatePin: 1,
        invalidateOnRefresh: true,
        onUpdate: (self) => {
          const idx = Math.min(dots.length - 1, Math.floor(self.progress * dots.length));
          dots.forEach((d, i) => d.classList.toggle('active', i === idx));
        }
      }
    });

    return () => { tween.scrollTrigger && tween.scrollTrigger.kill(); tween.kill(); };
  });
});
