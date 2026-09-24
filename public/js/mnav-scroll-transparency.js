document.addEventListener('DOMContentLoaded', function () {
    const bar = document.getElementById('mnavBar');
    if (!bar) return;

    let scrollTimeout = null;

    window.addEventListener('scroll', function () {
        bar.classList.add('mnav-bar-scrolling');
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function () {
            bar.classList.remove('mnav-bar-scrolling');
        }, 250);
    }, { passive: true });
});
