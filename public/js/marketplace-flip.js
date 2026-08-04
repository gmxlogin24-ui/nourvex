document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.flip-inner').forEach(function (card, i) {
    setInterval(function () {
      card.classList.toggle('is-flipped');
    }, 3200 + (i * 400)); // slight stagger between slots so they don't flip in perfect unison
  });
});
