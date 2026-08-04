document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.hero-slider').forEach(function (slider) {
    var slides = Array.from(slider.querySelectorAll('.hero-slide'));
    var dots = Array.from(slider.querySelectorAll('.hero-dot'));
    if (slides.length < 2) return;

    var current = 0;

    function show(index) {
      slides[current].classList.remove('is-active');
      dots[current] && dots[current].classList.remove('is-active');
      current = index;
      slides[current].classList.add('is-active');
      dots[current] && dots[current].classList.add('is-active');
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function (e) {
        e.preventDefault();
        show(i);
        resetTimer();
      });
    });

    var timer = setInterval(function () {
      show((current + 1) % slides.length);
    }, 4500);

    function resetTimer() {
      clearInterval(timer);
      timer = setInterval(function () {
        show((current + 1) % slides.length);
      }, 4500);
    }
  });
});
