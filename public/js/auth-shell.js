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

  gsap.utils.toArray('.auth-parallax-blob').forEach((el, i) => {
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
    document.getElementById('sidebarToggle')?.addEventListener('click', () => setTimeout(movePill, 320));
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
