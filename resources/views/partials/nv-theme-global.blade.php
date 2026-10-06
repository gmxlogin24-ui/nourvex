{{-- NOURVEX shell: one light/dark theme for every page that uses layouts.landing --}}
<link rel="stylesheet" href="{{ asset('css/nv-shell.css') }}?v={{ @filemtime(public_path('css/nv-shell.css')) }}">
<script>
(function () {
    var root = document.documentElement;
    var light = true;
    try { if (localStorage.getItem('nvTheme') === 'dark') light = false; } catch (e) {}
    if (!/^\/(login|register|forgot|reset|password)/.test(location.pathname)) root.classList.add('nv-skin');

    function apply(l) {
        light = l;
        root.classList.toggle('nv-light', l);
        root.classList.toggle('nv-dark', !l);
        var m = document.querySelector('main.lp');
        if (m) m.classList.toggle('lp-light', l);
        try { localStorage.setItem('nvTheme', l ? 'light' : 'dark'); } catch (e) {}
    }
    apply(light);
    document.addEventListener('DOMContentLoaded', function () {
        var m = document.querySelector('main.lp');
        if (m) m.classList.toggle('lp-light', light);
    });
    document.addEventListener('click', function (e) {
        if (e.target.closest('#snThemeBtn')) apply(!light);
    }, true);
})();
</script>
