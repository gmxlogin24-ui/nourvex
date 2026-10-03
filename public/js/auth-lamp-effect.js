// Drives --lamp-intensity (0..1) on .auth-split-shell as the auth form is
// filled in. Every required field (except radio/checkbox) contributes an
// equal share of the glow as soon as it has a value; the password field
// contributes its own share progressively based on strength rather than
// just "filled or not". Generic by design, so it works unchanged on
// login.blade.php and every register-* variant sharing layouts.auth.
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var shell = document.querySelector('.auth-split-shell');
    var form = document.querySelector('.auth-form-card form');
    if (!shell || !form) return;

    function setIntensity(v) {
      shell.style.setProperty('--lamp-intensity', v);
    }

    function passwordScore(val) {
      if (!val) return 0;
      var steps = 0;
      if (val.length >= 1) steps++;
      if (val.length >= 6) steps++;
      if (/[A-Z]/.test(val)) steps++;
      if (/\d/.test(val)) steps++;
      if (val.length >= 10) steps++;
      return Math.min(1, steps / 5);
    }

    var fields = Array.prototype.slice.call(
      form.querySelectorAll('input[required]:not([type="radio"]):not([type="checkbox"]), select[required]')
    );

    function fieldFraction(el) {
      if (el.type === 'password') return passwordScore(el.value);
      return el.value && el.value.trim() !== '' ? 1 : 0;
    }

    function recompute() {
      if (!fields.length) return;
      var total = fields.reduce(function (sum, el) { return sum + fieldFraction(el); }, 0);
      setIntensity(Math.min(1, total / fields.length));
    }

    fields.forEach(function (el) {
      el.addEventListener('input', recompute);
      el.addEventListener('change', recompute);
    });

    recompute();
  });
})();
