(function () {
    // Outer corner radius, matches .d2-card's 32px.
    var R = 32;
    // Radius of the one true concave/reflex corner: where the leg's right
    // edge meets the horizontal step under the top piece.
    var NOTCH = 32;

    function isDark() {
        return document.body.dataset.theme === 'dark';
    }
    function fillColor() {
        return isDark() ? '#1c2536' : '#f6f9fd';
    }

    // topW/totalH = full outer box. topH = height of the top piece.
    // legW = width of the leg (measured, not assumed from flex-basis).
    function buildPath(topW, topH, legW, totalH) {
        var r = Math.min(R, topH, legW, totalH - topH);
        var n = Math.min(NOTCH, topH, totalH - topH, topW - legW);
        // Two corners that were previously left sharp (90-degree, no curve):
        // rTR = top piece's own bottom-right corner, just before the notch.
        // rBR = leg's bottom-right corner, right next to small-card.
        var rTR = Math.min(R, topH, topW - legW - n);
        var rBR = Math.min(R, totalH - topH, legW - r);
        return [
            'M ' + r + ' 0',
            'L ' + (topW - r) + ' 0',
            'A ' + r + ' ' + r + ' 0 0 1 ' + topW + ' ' + r,
            'L ' + topW + ' ' + (topH - rTR),
            'A ' + rTR + ' ' + rTR + ' 0 0 1 ' + (topW - rTR) + ' ' + topH,
            'L ' + (legW + n) + ' ' + topH,
            // The one concave fillet. If this reads as a bump curving the
            // wrong way instead of a smooth inward scoop, flip the sweep
            // flag below from 0 to 1 and re-screenshot.
            'A ' + n + ' ' + n + ' 0 0 0 ' + legW + ' ' + (topH + n),
            'L ' + legW + ' ' + (totalH - rBR),
            'A ' + rBR + ' ' + rBR + ' 0 0 1 ' + (legW - rBR) + ' ' + totalH,
            'L ' + r + ' ' + totalH,
            'A ' + r + ' ' + r + ' 0 0 1 0 ' + (totalH - r),
            'L 0 ' + r,
            'A ' + r + ' ' + r + ' 0 0 1 ' + r + ' 0',
            'Z'
        ].join(' ');
    }

    function render() {
        var wrap = document.getElementById('d2UnionShape');
        if (!wrap) return;
        var svg = wrap.querySelector('.d2-union-svg');
        var top = wrap.querySelector('.d2-union-top-content');
        var leg = wrap.querySelector('.d2-union-leg-content');
        if (!svg || !top || !leg) return;

        var wrapRect = wrap.getBoundingClientRect();
        var topRect = top.getBoundingClientRect();
        var legRect = leg.getBoundingClientRect();

        var topW = wrapRect.width;
        var topH = topRect.height;
        var legW = legRect.right - wrapRect.left;
        var totalH = wrapRect.height;

        if (topW <= 0 || totalH <= 0) return;

        svg.setAttribute('viewBox', '0 0 ' + topW + ' ' + totalH);
        svg.setAttribute('width', topW);
        svg.setAttribute('height', totalH);

        var path = svg.querySelector('path');
        if (!path) {
            path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            svg.appendChild(path);
        }
        path.setAttribute('d', buildPath(topW, topH, legW, totalH));
        path.setAttribute('fill', fillColor());
    }

    window.addEventListener('load', render);
    window.addEventListener('resize', render);

    document.addEventListener('DOMContentLoaded', function () {
        var wrapEl = document.getElementById('d2UnionShape');
        if (wrapEl && window.ResizeObserver) {
            new ResizeObserver(render).observe(wrapEl);
        }
        render();
    });

    // Re-render if the theme toggle flips body[data-theme].
    var themeObserver = new MutationObserver(render);
    themeObserver.observe(document.body, { attributes: true, attributeFilter: ['data-theme'] });
})();
