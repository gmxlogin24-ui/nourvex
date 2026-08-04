document.addEventListener('DOMContentLoaded', function () {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // universal 3D tilt — every card type sitewide
  if (!reduced) {
    document.querySelectorAll('.gig-card-v2, .listing-card, .kpi-tile, .stat-card, .mini-stat-card, .gig-box').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        const r = card.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform = 'perspective(700px) rotateY(' + (px * 6) + 'deg) rotateX(' + (-py * 6) + 'deg) translateY(-4px)';
      });
      card.addEventListener('mouseleave', function () { card.style.transform = ''; });
    });

    // magnetic buttons sitewide
    document.querySelectorAll('.btn-primary, .btn-secondary, .balance-chip').forEach(function (btn) {
      btn.addEventListener('mousemove', function (e) {
        const r = btn.getBoundingClientRect();
        const x = (e.clientX - r.left - r.width / 2) * 0.22;
        const y = (e.clientY - r.top - r.height / 2) * 0.22;
        btn.style.transform = 'translate(' + x + 'px,' + y + 'px)';
      });
      btn.addEventListener('mouseleave', function () { btn.style.transform = ''; });
    });
  }

  // universal count-up — any element with a numeric value (₦ / % / plain), sitewide
  document.querySelectorAll('.kpi-tile-value, .balance-amount, .glass-balance-amount, .card-value').forEach(function (el) {
    const raw = el.textContent.trim();
    const num = parseFloat(raw.replace(/[^0-9.]/g, ''));
    if (isNaN(num)) return;
    const prefix = raw.match(/^[^\d]*/)[0];
    const suffix = raw.match(/[^\d]*$/)[0];
    const isInt = !raw.includes('.');
    el.textContent = prefix + '0' + suffix;
    const obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        obs.unobserve(el);
        const start = performance.now(), dur = 1100;
        function tick(now) {
          const p = Math.min(1, (now - start) / dur);
          const eased = 1 - Math.pow(1 - p, 3);
          const val = num * eased;
          el.textContent = prefix + (isInt ? Math.floor(val).toLocaleString() : val.toFixed(1)) + suffix;
          if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
      });
    }, { threshold: 0.4 });
    obs.observe(el);
  });

  // staggered reveal timing for any grid of cards, sitewide
  document.querySelectorAll('.grid').forEach(function (grid) {
    Array.from(grid.children).forEach(function (child, i) {
      if (child.classList.contains('reveal-on-scroll')) {
        child.style.transitionDelay = (i % 8) * 0.06 + 's';
      }
    });
  });

  // modal open/close class helper — works with your existing onclick("...classList.add('open')") calls
  document.querySelectorAll('.modal-overlay').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.classList.remove('open');
    });
  });
});
