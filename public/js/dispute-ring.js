// Dispute ring carousel: highlights whichever card sits nearest the
// horizontal center of its scroll container. Works for any element
// with class "dispute-ring-container" containing ".dispute-ring-card"
// children, so it's shared across admin + customer-care dispute pages.
function initDisputeRing(container) {
  const cards = Array.from(container.querySelectorAll('.dispute-ring-card'));
  if (!cards.length) return;

  const dotsWrap = container.parentElement.querySelector('.dispute-ring-dots');
  const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('.dispute-ring-dot')) : [];

  let ticking = false;

  function updateActive() {
    const containerRect = container.getBoundingClientRect();
    const containerCenter = containerRect.left + containerRect.width / 2;

    let closestIndex = 0;
    let closestDist = Infinity;

    cards.forEach((card, i) => {
      const rect = card.getBoundingClientRect();
      const cardCenter = rect.left + rect.width / 2;
      const dist = Math.abs(cardCenter - containerCenter);
      if (dist < closestDist) {
        closestDist = dist;
        closestIndex = i;
      }
    });

    cards.forEach((card, i) => card.classList.toggle('is-active', i === closestIndex));
    dots.forEach((dot, i) => dot.classList.toggle('is-active', i === closestIndex));

    ticking = false;
  }

  container.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(updateActive);
      ticking = true;
    }
  }, { passive: true });

  // Tapping a card scrolls it to center
  cards.forEach((card) => {
    card.addEventListener('click', (e) => {
      if (e.target.closest('button, a, input, form')) return; // don't hijack real controls
      card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    });
  });

  updateActive();
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.dispute-ring-container').forEach(initDisputeRing);
});
