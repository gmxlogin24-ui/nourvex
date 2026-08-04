document.addEventListener('DOMContentLoaded', () => {
  gsap.registerPlugin(ScrollTrigger);
  const isMobile = window.matchMedia('(max-width:760px)').matches;

  /* preloader — auto counts, no scroll needed */
  const plCount = document.getElementById('hpPlCount');
  const preloader = document.getElementById('hpPreloader');
  const counter = {val:0};
  gsap.to(counter, {
    val:100, duration: isMobile ? 0.8 : 1.4, ease:'power2.inOut',
    onUpdate: () => plCount.textContent = Math.floor(counter.val),
    onComplete: () => {
      gsap.to(preloader, {yPercent:-100, duration:.6, ease:'power3.inOut', delay:.1, onComplete: () => {
        preloader.style.display = 'none';
        playHeroEntrance();
      }});
    }
  });

  function playHeroEntrance(){
    if (window.lucide) lucide.createIcons();
    const headline = document.getElementById('hpHeroHeadline');
    if (headline) {
      const lines = headline.innerHTML.split(/<br\s*\/?>/i);
      headline.innerHTML = lines.map(function(line){
        return line.split('').map(function(c){
          return c === ' ' ? ' ' : '<span class="hp-char">' + c + '</span>';
        }).join('');
      }).join('<br>');
      const chars = headline.querySelectorAll('.hp-char');
      gsap.set(chars, {willChange:'transform,opacity'});
      gsap.from(chars, {
        yPercent:120, opacity:0, duration:.55, stagger:0.008, ease:'power3.out', force3D:true,
        onComplete: () => gsap.set(chars, {willChange:'auto'})
      });
    }
    gsap.from('.hp-eyebrow, .hp-cta-row', {opacity:0, y:16, duration:.6, stagger:.12, delay:.2, force3D:true});
    startCycleLine();
  }

  /* cycling subline — auto-plays forever, no scroll */
  function startCycleLine(){
    const el = document.getElementById('hpCycleLine');
    if (!el) return;
    const phrases = ['Post a gig in 60 seconds.', "Sell what you're not using.", 'Get paid straight to your wallet.'];
    let i = 0;
    el.textContent = phrases[0];
    setInterval(() => {
      gsap.to(el, {opacity:0, y:-8, duration:.4, force3D:true, onComplete:() => {
        i = (i+1) % phrases.length;
        el.textContent = phrases[i];
        gsap.fromTo(el, {opacity:0,y:8}, {opacity:1,y:0,duration:.4, force3D:true});
      }});
    }, 3200);
  }

  /* cursor — desktop/mouse only, skipped entirely on mobile */
  if (!isMobile) {
    const dot = document.getElementById('hpCursorDot');
    const ring = document.getElementById('hpCursorRing');
    if (dot && ring && window.matchMedia('(min-width:761px)').matches) {
      window.addEventListener('mousemove', e => {
        gsap.to(dot, {x:e.clientX, y:e.clientY, duration:.05});
        gsap.to(ring, {x:e.clientX, y:e.clientY, duration:.25});
      });
      document.addEventListener('mouseover', e => {
        if (e.target.closest('.hp-cursor-hover')) ring.classList.add('hp-hover');
      });
      document.addEventListener('mouseout', e => {
        if (e.target.closest('.hp-cursor-hover')) ring.classList.remove('hp-hover');
      });
    }

    /* magnetic buttons — desktop/mouse only */
    document.querySelectorAll('.hp-btn').forEach(btn => {
      btn.addEventListener('mousemove', e => {
        const r = btn.getBoundingClientRect();
        gsap.to(btn, {x:(e.clientX-r.left-r.width/2)*0.25, y:(e.clientY-r.top-r.height/2)*0.25, duration:.3});
      });
      btn.addEventListener('mouseleave', () => gsap.to(btn, {x:0,y:0,duration:.4,ease:'elastic.out(1,0.4)'}));
    });
  }

  /* progress bar */
  gsap.to('#hpProgressBar', {scaleX:1, ease:'none', scrollTrigger:{trigger:document.body, start:'top top', end:'bottom bottom', scrub:.3}});

  /* ambient blobs — lighter blur + fewer active on mobile */
  gsap.to('#hpBlob1', {x: isMobile ? 30 : 60, y: isMobile ? 20 : 40, duration: isMobile ? 12 : 9, repeat:-1, yoyo:true, ease:'sine.inOut', force3D:true});
  if (!isMobile) {
    gsap.to('#hpBlob2', {x:-50, y:60, duration:11, repeat:-1, yoyo:true, ease:'sine.inOut', force3D:true});
    gsap.to('#hpBlob3', {x:40, y:-50, duration:8, repeat:-1, yoyo:true, ease:'sine.inOut', force3D:true});
  } else {
    const b2 = document.getElementById('hpBlob2'), b3 = document.getElementById('hpBlob3');
    if (b2) b2.style.display = 'none';
    if (b3) b3.style.display = 'none';
  }

  /* scroll reveals */
  gsap.utils.toArray('.hp-plan-card').forEach((el,i) => {
    gsap.from(el, {y:40, opacity:0, duration:.6, delay:i*0.04, ease:'power3.out', force3D:true, scrollTrigger:{trigger:el, start:'top 88%'}});
  });

  /* stats count-up */
  document.querySelectorAll('.hp-stat-num').forEach(el => {
    const target = parseFloat(el.dataset.target);
    const suffix = el.dataset.suffix || '';
    const decimal = parseInt(el.dataset.decimal || '0');
    ScrollTrigger.create({
      trigger: el, start:'top 90%', once:true,
      onEnter: () => {
        const obj = {val:0};
        gsap.to(obj, {val:target, duration:1.6, ease:'power2.out', onUpdate:() => {
          el.textContent = decimal ? obj.val.toFixed(decimal) + suffix : Math.floor(obj.val).toLocaleString() + suffix;
        }});
      }
    });
  });

  /* horizontal pinned steps — desktop only; plain stacked scroll on mobile (no pin/scrub cost) */
  const track = document.getElementById('hpStepsTrack');
  const dotsWrap = document.getElementById('hpStepsDots');
  if (track && !isMobile) {
    const cards = track.querySelectorAll('.hp-step-card');
    cards.forEach(() => dotsWrap.insertAdjacentHTML('beforeend', '<span></span>'));
    const dots = dotsWrap.querySelectorAll('span');
    gsap.to(track, {
      x: () => -(track.scrollWidth - window.innerWidth + 88), ease:'none',
      scrollTrigger:{
        trigger:'#hpStepsPin', start:'top top',
        end: () => '+=' + (track.scrollWidth - window.innerWidth + 88),
        scrub:.5, pin:true, invalidateOnRefresh:true,
        onUpdate: self => {
          const idx = Math.min(cards.length-1, Math.floor(self.progress * cards.length));
          dots.forEach((d,i) => d.classList.toggle('active', i===idx));
        }
      }
    });
  }
});
