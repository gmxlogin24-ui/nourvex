document.addEventListener('DOMContentLoaded', () => {
  const shell = document.querySelector('.auth-reveal-content');
  if (shell) {
    const runReveal = () => {
      shell.classList.add('is-ready');
      if (window.gsap) {
        const blocks = shell.querySelectorAll(':scope > *');
        gsap.set(blocks, { opacity: 0, y: 16 });
        gsap.to(blocks, { opacity: 1, y: 0, duration: 1.1, stagger: 0.12, ease: 'power3.out', delay: 0.15 });
      }
    };
    requestAnimationFrame(() => requestAnimationFrame(runReveal));
  }

  document.querySelectorAll('.hoverfade-text').forEach(el => {
    if (el.dataset.hfDone) return;
    el.dataset.hfDone = '1';
    const isDesktop = window.matchMedia('(min-width:901px)').matches;
    if (!isDesktop) return;
    const text = el.textContent;
    el.innerHTML = text.split('').map(c => c === ' ' ? ' ' : '<span class="hf-char">' + c + '</span>').join('');
    el.querySelectorAll('.hf-char').forEach(span => {
      span.addEventListener('mouseenter', () => { span.style.opacity = '0'; });
      span.addEventListener('mouseleave', () => { span.style.opacity = '1'; });
    });
  });

  const cursorFacers = document.querySelectorAll('[data-face-cursor]');
  if (cursorFacers.length && window.matchMedia('(min-width:901px)').matches) {
    const state = new Map();
    cursorFacers.forEach(el => state.set(el, { active: false, timer: null }));

    document.addEventListener('mousemove', (e) => {
      cursorFacers.forEach(el => {
        const rect = el.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        const dist = Math.hypot(e.clientX - cx, e.clientY - cy);
        const proximity = 260;
        if (dist < proximity) {
          const angle = Math.atan2(e.clientY - cy, e.clientX - cx) * (180 / Math.PI);
          const tilt = Math.max(-14, Math.min(14, (angle / 180) * 14));
          el.style.transform = 'rotate(' + tilt.toFixed(2) + 'deg)';
          el.style.transition = 'transform 0.4s ease-out';
          const s = state.get(el);
          if (s.timer) clearTimeout(s.timer);
          s.timer = setTimeout(() => { el.style.transform = 'rotate(0deg)'; }, 7000);
        }
      });
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.registerPlugin(ScrollTrigger);

  gsap.utils.toArray('.auth-parallax-blob-DISABLED').forEach((el, i) => {
    gsap.to(el, {
      y: i % 2 === 0 ? 120 : -120,
      ease: 'none',
      scrollTrigger: { trigger: document.body, start: 'top top', end: 'bottom bottom', scrub: 2.5 }
    });
  });

  gsap.utils.toArray('h2.font-semibold, h2.text-xl.font-bold, .dash-header-row').forEach(el => {
    gsap.to(el, {
      y: -18, ease: 'none',
      scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: 2.5 }
    });
  });

  gsap.utils.toArray('.reveal-on-scroll').forEach(el => {
    el.classList.add('story-section');
    ScrollTrigger.create({
      trigger: el,
      start: 'top 88%',
      onEnter: () => el.classList.add('story-in'),
      once: true
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.utils.toArray('.scroll-hide-over').forEach(el => {
    gsap.to(el, {
      y: -70,
      ease: 'none',
      scrollTrigger: { trigger: el, start: 'top bottom', end: 'top 40%', scrub: 1.5 }
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('appSidebar');
  const nav = sidebar ? sidebar.querySelector('.neu-sidebar-nav') : null;
  if (sidebar) requestAnimationFrame(() => sidebar.classList.add('sb-ready'));

  if (nav) {
    const pill = document.createElement('div');
    pill.className = 'sidebar-active-pill';
    nav.prepend(pill);

    function movePill() {
      const active = nav.querySelector('a.active');
      if (!active) { pill.style.opacity = '0'; return; }
      pill.style.height = active.offsetHeight + 'px';
      pill.style.top = active.offsetTop + 'px';
      pill.style.opacity = '1';
    }
    movePill();
    window.addEventListener('resize', movePill);
    // Toggle logic now lives ONLY in app-authenticated.blade.php's inline script
    // (this duplicate handler used to cancel every click out — both listeners
    // fired on the same click and flipped the attribute back and forth).
    // Kept here only to still reposition the active-nav pill after resize.
        // Toggle logic now lives ONLY in app-authenticated.blade.php's inline script
    // (this duplicate handler used to cancel every click out — both listeners
    // fired on the same click and flipped the attribute back and forth).
    // Kept here only to still reposition the active-nav pill after resize.
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
      setTimeout(movePill, 320);
    });
  }

  document.querySelectorAll('.neu-sidebar-nav a, .neu-sidebar-nav button').forEach(el => {
    if (el.querySelector('.sidebar-tooltip')) return;
    const label = el.querySelector('.sidebar-link-label');
    if (!label) return;
    const tip = document.createElement('span');
    tip.className = 'sidebar-tooltip';
    tip.textContent = label.textContent.trim();
    el.appendChild(tip);
  });
});

document.addEventListener('DOMContentLoaded', () => {
  // ---------- Robust theme toggle: strip any existing listeners first, rebind clean ----------
  document.querySelectorAll('.sidebar-theme-btn-inline, .gn-theme-btn-inline').forEach(btn => {
    const clone = btn.cloneNode(true);
    btn.parentNode.replaceChild(clone, btn);
  });
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.sidebar-theme-btn-inline, .gn-theme-btn-inline');
    if (!btn) return;
    e.preventDefault();
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    html.setAttribute('data-theme', isDark ? 'light' : 'dark');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
  });

  // ---------- Align rail icons to their matching sidebar labels, using real measured positions ----------
  function alignRailIcons() {
    const panel = document.querySelector('.sidebar-content-panel');
    const rail = document.querySelector('.sidebar-icon-rail');
    if (!panel || !rail) return;
    const navLinks = panel.querySelectorAll('.neu-sidebar-nav > a');
    const railIcons = rail.querySelectorAll('.rail-nav-icon');
    if (!navLinks.length || navLinks.length !== railIcons.length) return;
    railIcons.forEach(icon => { icon.style.marginTop = '0px'; });
    navLinks.forEach((link, i) => {
      const icon = railIcons[i];
      const linkRect = link.getBoundingClientRect();
      const iconRect = icon.getBoundingClientRect();
      const delta = (linkRect.top + linkRect.height / 2) - (iconRect.top + iconRect.height / 2);
      icon.style.marginTop = delta + 'px';
    });
  }
  setTimeout(alignRailIcons, 150);
  window.addEventListener('resize', () => setTimeout(alignRailIcons, 60));
  document.getElementById('sidebarToggle')?.addEventListener('click', () => setTimeout(alignRailIcons, 340));
});

document.addEventListener('DOMContentLoaded', () => {
  // Re-run alignment after fonts/layout fully settle (fixes MENU-label offset drift)
  window.addEventListener('load', () => setTimeout(() => {
    const evt = new Event('resize');
    window.dispatchEvent(evt);
  }, 100));
});

// ===== Dashboard masonry v2: real DOM packing, not a CSS guess.
// Moves each card (the actual element, not a clone, so its scripts/
// listeners keep working) into whichever of two columns is currently
// shorter. Rebuilds on resize; tears itself down below the 900px
// breakpoint so mobile keeps its normal single-column stack. =====
(function(){
  const FULL_WIDTH = '#dashboardWelcomeHeader, .desktop-plan-pill, #dashAdSlot';
  let wrap = null;

  function teardown(){
    if (!wrap) return;
    const main = document.querySelector('main');
    wrap.querySelectorAll('.dashboard-masonry-col').forEach(col => {
      Array.from(col.children).forEach(child => main.insertBefore(child, wrap));
    });
    wrap.remove();
    wrap = null;
  }

  function build(){
    if (wrap) return;
    const main = document.querySelector('main');
    if (!main) return;
    const items = Array.from(main.children).filter(el => !el.matches(FULL_WIDTH));
    if (!items.length) return;

    wrap = document.createElement('div');
    wrap.id = 'dashboardMasonryWrap';
    wrap.style.cssText = 'display:flex;gap:1.25rem;align-items:flex-start;';

    const colA = document.createElement('div');
    const colB = document.createElement('div');
    [colA, colB].forEach(c => {
      c.className = 'dashboard-masonry-col';
      c.style.cssText = 'display:flex;flex-direction:column;gap:1.25rem;flex:1 1 0;min-width:0;';
    });

    main.insertBefore(wrap, items[0]);
    wrap.appendChild(colA);
    wrap.appendChild(colB);

    let hA = 0, hB = 0;
    items.forEach(item => {
      const target = hA <= hB ? colA : colB;
      target.appendChild(item);
      const h = item.getBoundingClientRect().height;
      if (target === colA) hA += h; else hB += h;
    });
  }

  function refresh(){
    teardown();
    if (window.innerWidth >= 900) build();
  }

  function ready(){ setTimeout(refresh, 50); }
  if (document.fonts && document.fonts.ready) { document.fonts.ready.then(ready); } else { ready(); }
  if (document.readyState !== 'complete') { window.addEventListener('load', ready); }

  let resizeT;
  window.addEventListener('resize', () => { clearTimeout(resizeT); resizeT = setTimeout(refresh, 200); });
})();
