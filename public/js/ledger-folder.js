document.addEventListener('DOMContentLoaded', function () {
  var folder = document.getElementById('ledgerCard');
  if (!folder) return;

  var isTouch = matchMedia('(pointer:coarse)').matches;

  if (!isTouch) {
    folder.addEventListener('mouseenter', function () {
      folder.classList.add('is-peek');
    });
    folder.addEventListener('mouseleave', function () {
      folder.classList.remove('is-peek', 'is-open');
    });
    return;
  }

  var downX = 0, downY = 0, downT = 0;
  var tapTimer = null, tapCount = 0;

  folder.addEventListener('pointerdown', function (e) {
    downX = e.clientX; downY = e.clientY; downT = Date.now();
  });

  folder.addEventListener('pointerup', function (e) {
    var dist = Math.hypot(e.clientX - downX, e.clientY - downY);
    var elapsed = Date.now() - downT;
    if (dist > 12 || elapsed > 400) return;

    tapCount++;
    if (tapCount === 1) {
      tapTimer = setTimeout(function () {
        tapCount = 0;
        if (folder.classList.contains('is-open')) {
          folder.classList.remove('is-open', 'is-peek');
        } else {
          folder.classList.toggle('is-peek');
        }
      }, 280);
    } else if (tapCount === 2) {
      clearTimeout(tapTimer);
      tapCount = 0;
      if (folder.classList.contains('is-open')) {
        folder.classList.remove('is-open', 'is-peek');
      } else {
        folder.classList.remove('is-peek');
        folder.classList.add('is-open');
      }
    }
  });

  document.addEventListener('pointerdown', function (e) {
    if (folder.classList.contains('is-open') && !folder.contains(e.target)) {
      folder.classList.remove('is-open', 'is-peek');
    }
  });
});
