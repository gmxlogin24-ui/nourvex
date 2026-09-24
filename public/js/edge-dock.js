document.addEventListener('DOMContentLoaded', function () {
    const dock = document.getElementById('edgeDock');
    const btn = document.getElementById('edgeDockBtn');
    const mnavBar = document.getElementById('mnavBar');
    if (!dock || !btn) return;

    const savedTop = localStorage.getItem('edgeDockTop');
    if (savedTop) dock.style.top = savedTop;

    let dragging = false, moved = false, startY = 0, startTop = 0;

    function clampTop(top) {
        const min = 80;
        const max = window.innerHeight - 80;
        return Math.min(Math.max(top, min), max);
    }

    function onPointerDown(e) {
        dragging = true; moved = false;
        startY = (e.touches ? e.touches[0].clientY : e.clientY);
        startTop = dock.getBoundingClientRect().top + dock.offsetHeight / 2;
        dock.classList.add('dragging');
    }
    function onPointerMove(e) {
        if (!dragging) return;
        const y = (e.touches ? e.touches[0].clientY : e.clientY);
        const delta = y - startY;
        if (Math.abs(delta) > 6) moved = true;
        const newCenter = clampTop(startTop + delta);
        dock.style.top = newCenter + 'px';
        dock.style.transform = 'translateY(-50%)';
    }
    function onPointerUp() {
        if (!dragging) return;
        dragging = false;
        dock.classList.remove('dragging');
        if (moved) {
            localStorage.setItem('edgeDockTop', dock.style.top);
        } else {
            document.body.classList.toggle('edge-dock-open');
        }
    }

    dock.addEventListener('mousedown', onPointerDown);
    dock.addEventListener('touchstart', onPointerDown, { passive: true });
    window.addEventListener('mousemove', onPointerMove);
    window.addEventListener('touchmove', onPointerMove, { passive: true });
    window.addEventListener('mouseup', onPointerUp);
    window.addEventListener('touchend', onPointerUp);

    document.addEventListener('click', function (e) {
        if (!document.body.classList.contains('edge-dock-open')) return;
        if (mnavBar && mnavBar.contains(e.target)) return;
        if (dock.contains(e.target)) return;
        document.body.classList.remove('edge-dock-open');
    });
});
