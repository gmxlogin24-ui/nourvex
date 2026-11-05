<link rel="stylesheet" href="{{ asset('css/preloader-k.css') }}?v={{ @filemtime(public_path('css/preloader-k.css')) }}">
<div id="nvkPre" class="nvk-pre" role="presentation" aria-hidden="true" hidden>
  <div class="nvk-stage">
    <i class="nvk-glow"></i>
    <div class="nvk-box">
      <img class="nvk-logo" src="{{ asset('images/naurvex-logo-512.png') }}" alt="" draggable="false">
      <i class="nvk-sheen"></i>
    </div>
  </div>
  <b class="nvk-name">NAURVEX</b>
  <span class="nvk-bar"><i></i></span>
</div>
<script>
(function () {
  var root = document.getElementById('nvkPre');
  if (!root) { return; }
  var force = /[?&]preloader\b/.test(location.search);
  try {
    if (!force && sessionStorage.getItem('nvkPre')) { root.remove(); return; }
    sessionStorage.setItem('nvkPre', '1');
  } catch (e) {}
  root.hidden = false;
  document.documentElement.classList.add('nvk-lock');
  var box = root.querySelector('.nvk-box'), glow = root.querySelector('.nvk-glow'), sheen = root.querySelector('.nvk-sheen'), bar = root.querySelector('.nvk-bar i');
  var done = false, loaded = document.readyState === 'complete', minDone = false;
  var hasG = !!(window.gsap && gsap.timeline);
  function kill() { try { root.remove(); } catch (e) {} document.documentElement.classList.remove('nvk-lock'); }
  function finish() {
    if (done) { return; }
    done = true;
    if (hasG) {
      gsap.timeline({ onComplete: kill })
        .to(box, { scale: 1.14, duration: 0.5, ease: 'power2.in' }, 0)
        .to(root, { opacity: 0, duration: 0.45, ease: 'power1.inOut' }, 0.15);
    } else {
      root.style.transition = 'opacity .4s'; root.style.opacity = '0'; setTimeout(kill, 450);
    }
  }
  function check() { if (loaded && minDone) { finish(); } }
  window.addEventListener('load', function () { loaded = true; check(); });
  setTimeout(function () { minDone = true; check(); }, 1500);
  setTimeout(finish, 5000);
  root.addEventListener('click', finish);
  if (hasG) {
    gsap.timeline()
      .to(box, { opacity: 1, scale: 1, duration: 0.8, ease: 'power3.out' }, 0)
      .fromTo(sheen, { xPercent: -130 }, { xPercent: 130, duration: 0.9, ease: 'power1.inOut' }, 0.5)
      .to(glow, { opacity: 1, scale: 1.12, duration: 0.5, yoyo: true, repeat: 1, ease: 'sine.inOut' }, 0.9);
    gsap.timeline().fromTo(bar, { scaleX: 0 }, { scaleX: 1, duration: 1.4, ease: 'power1.inOut' }, 0);
  } else {
    box.style.opacity = '1'; box.style.transform = 'none';
  }
})();
</script>
