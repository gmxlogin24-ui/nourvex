{{-- Turns the trust strip under the hero into a right-to-left marquee --}}
<script>
(function () {
    function build() {
        var strip = document.querySelector('.lp-trust');
        var inner = strip && strip.querySelector('.lp-trust-in');
        if (!inner || inner.getAttribute('data-mq')) return;
        inner.setAttribute('data-mq', '1');

        var items = Array.prototype.slice.call(inner.children);
        var track = document.createElement('div');
        track.className = 'lp-trust-track';
        var a = document.createElement('div');
        a.className = 'lp-trust-set';
        items.forEach(function (it) { a.appendChild(it); });
        var b = a.cloneNode(true);
        b.setAttribute('aria-hidden', 'true');
        track.appendChild(a);
        track.appendChild(b);
        inner.appendChild(track);
        strip.classList.add('lp-trust-mq');

        function icons() { if (window.lucide && lucide.createIcons) lucide.createIcons(); }
        icons();
        window.addEventListener('load', icons);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build); else build();
})();
</script>
