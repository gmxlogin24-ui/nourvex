document.addEventListener('DOMContentLoaded', function () {
  const card = document.getElementById('glassBalanceCard');
  const amountEl = document.getElementById('glassBalanceAmount');
  if (!card || !amountEl || typeof gsap === 'undefined') return;

  gsap.registerPlugin(ScrollTrigger);

  const target = parseFloat(amountEl.dataset.target || '0');
  const counter = { val: 0 };

  gsap.set(card, { autoAlpha: 0, y: 24, scale: 0.98 });

  ScrollTrigger.create({
    trigger: card,
    start: 'top 85%',
    once: true,
    onEnter: () => {
      gsap.to(card, { autoAlpha: 1, y: 0, scale: 1, duration: 0.8, ease: 'power3.out' });
      gsap.to(counter, {
        val: target,
        duration: 1.4,
        ease: 'power2.out',
        delay: 0.15,
        onUpdate: () => {
          amountEl.textContent = '₦' + counter.val.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
      });
    }
  });

  // Subtle tilt on pointer move (desktop), skipped on touch
  const isTouch = matchMedia('(pointer: coarse)').matches;
  if (!isTouch) {
    let rect = card.getBoundingClientRect();
    card.addEventListener('mouseenter', () => { rect = card.getBoundingClientRect(); });
    card.addEventListener('mousemove', (e) => {
      const px = (e.clientX - rect.left) / rect.width - 0.5;
      const py = (e.clientY - rect.top) / rect.height - 0.5;
      gsap.to(card, {
        rotateY: px * 8,
        rotateX: -py * 8,
        duration: 0.5,
        ease: 'power2.out',
        transformPerspective: 800
      });
    });
    card.addEventListener('mouseleave', () => {
      gsap.to(card, { rotateY: 0, rotateX: 0, duration: 0.6, ease: 'power3.out' });
    });
  }
});
