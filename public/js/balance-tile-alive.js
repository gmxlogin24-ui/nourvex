document.addEventListener('DOMContentLoaded', function () {
  const tile = document.querySelector('.kpi-tile.featured');
  if (!tile || typeof gsap === 'undefined') return;

  const isTouch = matchMedia('(pointer: coarse)').matches;

  // Magnetic tilt following cursor
  if (!isTouch) {
    let rect = tile.getBoundingClientRect();
    tile.addEventListener('mouseenter', () => { rect = tile.getBoundingClientRect(); });
    tile.addEventListener('mousemove', (e) => {
      const px = (e.clientX - rect.left) / rect.width - 0.5;
      const py = (e.clientY - rect.top) / rect.height - 0.5;
      gsap.to(tile, {
        rotateY: px * 10,
        rotateX: -py * 10,
        duration: 0.4,
        ease: 'power2.out',
        transformPerspective: 700
      });
      gsap.to(tile, {
        '--mx': (px + 0.5) * 100 + '%',
        '--my': (py + 0.5) * 100 + '%',
        duration: 0.2
      });
    });
    tile.addEventListener('mouseleave', () => {
      gsap.to(tile, { rotateY: 0, rotateX: 0, duration: 0.6, ease: 'power3.out' });
    });
  }

  // Digit roll-in on load (feels like it's "counting itself up", not robotic snap)
  const valueEl = tile.querySelector('.kpi-tile-value');
  if (valueEl) {
    const finalText = valueEl.textContent;
    const match = finalText.match(/[\d,]+\.\d{2}/);
    if (match) {
      const target = parseFloat(match[0].replace(/,/g, ''));
      const counter = { val: 0 };
      gsap.to(counter, {
        val: target,
        duration: 1.3,
        ease: 'power2.out',
        onUpdate: () => {
          valueEl.textContent = finalText.replace(match[0], counter.val.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }
      });
    }
  }
});
