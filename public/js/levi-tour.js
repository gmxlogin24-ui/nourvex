(function () {
  const STEPS = [
    { selector: '[data-nav="gigs"]', text: "This is Gigs — jobs people around you are hiring for. Tap in to browse or post one." },
    { selector: '[data-nav="marketplace"]', text: "This is the Marketplace — buy and sell items directly with other users." },
    { selector: '[data-nav="wallet"]', text: "Your Wallet lives here — fund it, send money, or withdraw your earnings." },
    { selector: '#leviBubble, .levi-inline', text: "And that's me — tap here anytime, I can search things or take you anywhere in the app." },
  ];

  function startTour() {
    let i = 0;
    const overlay = document.createElement('div');
    overlay.className = 'levi-tour-overlay';
    document.body.appendChild(overlay);

    const tooltip = document.createElement('div');
    tooltip.className = 'levi-tour-tooltip';
    document.body.appendChild(tooltip);

    function finish() {
      overlay.remove();
      tooltip.remove();
      fetch(document.querySelector('meta[name="tour-endpoint"]')?.content, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Accept': 'application/json',
        },
      }).catch(() => {});
    }

    function showStep() {
      const step = STEPS[i];
      if (!step) { finish(); return; }
      const el = document.querySelector(step.selector);
      if (!el) { i++; showStep(); return; }

      const rect = el.getBoundingClientRect();
      const pad = 8;
      overlay.style.clipPath = `polygon(
        0 0, 100% 0, 100% 100%, 0 100%, 0 0,
        ${rect.left - pad}px ${rect.top - pad}px,
        ${rect.left - pad}px ${rect.bottom + pad}px,
        ${rect.right + pad}px ${rect.bottom + pad}px,
        ${rect.right + pad}px ${rect.top - pad}px,
        ${rect.left - pad}px ${rect.top - pad}px
      )`;

      let top = rect.bottom + 12;
      if (top + 140 > window.innerHeight) top = Math.max(12, rect.top - 130);
      tooltip.style.top = top + 'px';
      tooltip.style.left = Math.min(Math.max(12, rect.left), window.innerWidth - 292) + 'px';

      tooltip.innerHTML = `<span class="levi-tour-badge">✦ LEVI</span>${step.text}
        <div class="levi-tour-actions">
          <button class="levi-tour-skip" type="button">Skip</button>
          <button class="levi-tour-next" type="button">${i === STEPS.length - 1 ? 'Got it' : 'Next'}</button>
        </div>`;

      tooltip.querySelector('.levi-tour-skip').onclick = finish;
      tooltip.querySelector('.levi-tour-next').onclick = () => { i++; showStep(); };
    }

    showStep();
  }

  document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.getElementById('leviTourTrigger');
    if (trigger && trigger.dataset.seen !== '1') setTimeout(startTour, 600);
  });
})();
