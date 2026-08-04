document.addEventListener('click', function (e) {
  const menu = document.getElementById('mobileSettingsMenu');
  if (!menu) return;
  const trigger = e.target.closest('button[onclick*="mobileSettingsMenu"]');
  if (!trigger && !menu.contains(e.target)) {
    menu.classList.remove('show');
  }
});
