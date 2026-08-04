document.addEventListener('DOMContentLoaded', function () {
  var group = document.getElementById('mascotGroup');
  if (!group) return;

  var pupils = Array.from(group.querySelectorAll('.pupil'));
  var currentState = 'idle';
  var revertTimer = null;

  function setState(state, autoRevertMs) {
    group.classList.remove('state-idle', 'state-lookaway', 'state-frown', 'state-smile');
    currentState = state;
    group.classList.add('state-' + state);

    if (state === 'frown') {
      group.classList.remove('is-shaking');
      void group.offsetWidth;
      group.classList.add('is-shaking');
      setTimeout(function () { group.classList.remove('is-shaking'); }, 550);
    }

    if (revertTimer) { clearTimeout(revertTimer); revertTimer = null; }
    if (autoRevertMs) {
      revertTimer = setTimeout(function () {
        setState('idle');
      }, autoRevertMs);
    }
  }

  setState('idle');

  document.addEventListener('mousemove', function (e) {
    if (currentState !== 'idle') return;
    pupils.forEach(function (pupil) {
      var rect = pupil.parentElement.getBoundingClientRect();
      var cx = rect.left + rect.width / 2;
      var cy = rect.top + rect.height / 2;
      var angle = Math.atan2(e.clientY - cy, e.clientX - cx);
      var dist = Math.min(3, Math.hypot(e.clientX - cx, e.clientY - cy) / 40);
      var dx = Math.cos(angle) * dist;
      var dy = Math.sin(angle) * dist;
      pupil.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
    });
  });

  if (group.dataset.hasErrors === 'true') {
    setState('frown', 3500);
  }

  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    input.addEventListener('focus', function () { setState('lookaway'); });
    input.addEventListener('blur', function () { setState('idle'); });
  });

  document.querySelectorAll('input[type="email"], input[type="text"][required]').forEach(function (input) {
    input.addEventListener('input', function () {
      if (input.type === 'password') return;
      if (input.value.length === 0) { setState('idle'); return; }
      if (input.checkValidity()) {
        setState('smile', 1200);
      } else if (input.value.length > 3) {
        setState('frown', 1800);
      }
    });
  });
});
