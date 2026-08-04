document.addEventListener('DOMContentLoaded', function () {
  if (typeof gsap === 'undefined') return;

  const tiles = gsap.utils.toArray('.plan-tile');
  if (tiles.length) {
    gsap.set(tiles, { autoAlpha: 0, y: 30 });
    gsap.to(tiles, {
      autoAlpha: 1, y: 0,
      duration: 0.6,
      ease: 'power3.out',
      stagger: 0.12,
      delay: 0.1
    });
  }

  const card = document.querySelector('.auth-card-clean');
  if (card) {
    gsap.set(card, { autoAlpha: 0, y: 24, scale: 0.98 });
    gsap.to(card, { autoAlpha: 1, y: 0, scale: 1, duration: 0.7, ease: 'power3.out' });
  }
});
