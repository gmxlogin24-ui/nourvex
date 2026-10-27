<script>
(function () {
  var w = document.createElement('div');
  w.className = 'pd-bgw';
  document.body.appendChild(w);
  document.body.classList.add('pd-white');
  var pb = document.querySelector('.pd-bar');
  if (pb) {
    if (pb.parentNode !== document.body) { document.body.appendChild(pb); }
    document.body.classList.add('pd-has-bar');
  }
  if (window.lucide) lucide.createIcons();
})();
</script>
