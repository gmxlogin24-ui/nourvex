document.addEventListener('DOMContentLoaded', function () {
  if (typeof gsap === 'undefined') return;
  const isTouch = matchMedia('(pointer: coarse)').matches;
  if (isTouch) return;

  document.querySelectorAll('.quick-grid-item, .qa-card').forEach(card => {
    let rect = card.getBoundingClientRect();
    card.addEventListener('mouseenter', () => { rect = card.getBoundingClientRect(); });
    card.addEventListener('mousemove', (e) => {
      const px = (e.clientX - rect.left) / rect.width - 0.5;
      const py = (e.clientY - rect.top) / rect.height - 0.5;
      gsap.to(card, { rotateY: px * 14, rotateX: -py * 14, y: -4, duration: 0.35, ease: 'power2.out', transformPerspective: 600 });
    });
    card.addEventListener('mouseleave', () => {
      gsap.to(card, { rotateY: 0, rotateX: 0, y: 0, duration: 0.5, ease: 'power3.out' });
    });
  });
});

// Auto-cycle analytics tabs every 6s, pause on manual interaction
document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('.analytics-tab');
  if (!tabs.length) return;
  let idx = 0;
  let autoTimer;
  let userTouched = false;

  function cycle() {
    if (userTouched) return;
    idx = (idx + 1) % tabs.length;
    tabs[idx].click();
  }
  function schedule() {
    clearInterval(autoTimer);
    autoTimer = setInterval(cycle, 3000);
  }
  tabs.forEach((tab, i) => {
    tab.addEventListener('click', () => {
      idx = i;
      userTouched = true;
      clearInterval(autoTimer);
      // stay on the clicked tab for 10s extra, then resume the normal 5s cycle
      setTimeout(() => { userTouched = false; schedule(); }, 10000);
    });
  });
  schedule();
});
