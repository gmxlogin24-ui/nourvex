(function () {
  function init() {
    const burger = document.getElementById('snBurger');
    const drawer = document.getElementById('snDrawer');
    const backdrop = document.getElementById('snBackdrop');
    const themeBtn = document.getElementById('snThemeBtn');
    if (!burger || !drawer || !backdrop) { return; }

    let isOpen = false;

    function setOpen(opening) {
      isOpen = opening;
      burger.classList.toggle('sn-open', isOpen);
      drawer.classList.toggle('sn-open', isOpen);
      backdrop.classList.toggle('sn-open', isOpen);
      document.body.classList.toggle('sn-lock', isOpen);
    }

    burger.addEventListener('click', function (e) {
      e.stopPropagation();
      setOpen(!isOpen);
    });
    backdrop.addEventListener('click', function () { setOpen(false); });

    themeBtn?.addEventListener('click', function (e) {
      e.stopPropagation();
      const html = document.documentElement;
      const isDark = html.getAttribute('data-theme') === 'dark';
      html.setAttribute('data-theme', isDark ? 'light' : 'dark');
      localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
