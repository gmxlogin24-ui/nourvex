document.addEventListener('DOMContentLoaded', () => {
  const pinWrap = document.getElementById('thHeroPinWrap');
  const hero = document.getElementById('thHero');
  const panel = document.getElementById('thHeroPanel');
  const panelInner = document.querySelector('.th-hero-panel-inner');
  const headline = document.getElementById('thHeroHeadline');
  const stats = document.getElementById('thHeroStats');
  const statsFrame = document.getElementById('thHeroStatsFrame');
  const captionLeft = document.querySelector('.th-caption-left');
  const captionRight = document.querySelector('.th-caption-right');
  const captionBottomLeft = document.querySelector('.th-caption-bottom-left');
  const captionBottomRight = document.querySelector('.th-caption-bottom-right');
  const scrollBtn = document.getElementById('thHeroScroll');
  const afterHero = document.getElementById('thAfterHero');
  if (!panel || !headline) return;

  const isMobile = window.matchMedia('(max-width:760px)').matches;
  const finalPanelHeight = isMobile ? 30 : 24;
  const blurAmount = isMobile ? '10px' : '16px';

  scrollBtn?.addEventListener('click', () => afterHero?.scrollIntoView({ behavior: 'smooth' }));

  const lines = headline.innerHTML.split(/<br\s*\/?>/i);
  headline.innerHTML = lines.map(line =>
    line.split('').map(c => c === ' ' ? ' ' : '<span class="th-char">' + c + '</span>').join('')
  ).join('<br>');
  const chars = headline.querySelectorAll('.th-char');
  const statCards = stats ? stats.querySelectorAll('.th-stat-card') : [];

  if (!window.gsap) {
    panel.style.height = finalPanelHeight + '%';
    if (statsFrame) statsFrame.style.opacity = 1;
    statCards.forEach(c => { c.style.opacity = 1; c.style.filter = 'none'; c.style.transform = 'none'; });
    return;
  }

  gsap.set(panel, { height: '100%' });
  gsap.set(chars, { yPercent: 120, opacity: 0 });
  gsap.set(statCards, { filter: `blur(${blurAmount})`, opacity: 0, y: 10 });
  gsap.set(statsFrame, { opacity: 0 });

  /* ---------- Intro reveal on load: panel to 24/30%, headline in, stat frame in ---------- */
  const intro = gsap.timeline({ delay: 0.4 });
  intro
    .to(panel, { height: finalPanelHeight + '%', duration: 3.5, ease: 'power3.inOut', force3D: true })
    .to(chars, { yPercent: 0, opacity: 1, duration: 1.1, stagger: 0.035, ease: 'power3.out', force3D: true }, '-=1.2')
    .to(statsFrame, { opacity: 1, duration: 1, ease: 'power2.out' }, '-=0.6')
    .to(statCards, { filter: 'blur(0px)', opacity: 1, y: 0, duration: 1.2, stagger: 0.3, ease: 'power2.out' }, '-=0.4');

  /* ---------- Pinned scroll: hero freezes on screen; scrolling drives panel to 0%,
     headline fades out, captions fade in. Fully reversible — scrolling back up
     restores the panel and headline before the page is allowed to continue scrolling. ---------- */
  if (window.ScrollTrigger && pinWrap) {
    gsap.registerPlugin(ScrollTrigger);

    intro.eventCallback('onComplete', () => {
      gsap.timeline({
        scrollTrigger: {
          trigger: pinWrap,
          start: 'top top',
          end: 'bottom bottom',
          scrub: 1,
          pin: hero,
          anticipatePin: 1,
        }
      })
      .to(panel, { height: '0%', ease: 'none' }, 0)
      .to(panelInner, { opacity: 0, ease: 'none' }, 0)
      .to([captionLeft, captionRight, captionBottomLeft, captionBottomRight], { opacity: 1, y: 0, ease: 'none' }, 0.5)
      .to(statsFrame, { y: isMobile ? '-14vh' : '-18vh', ease: 'none' }, 0.5);
    });
  }
});
