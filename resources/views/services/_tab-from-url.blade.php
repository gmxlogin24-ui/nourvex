<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        var t = new URLSearchParams(window.location.search).get('tab');
        var i = { airtime: 0, data: 1, cable: 2 }[t];
        var b = document.querySelectorAll('.svc-tab-btn')[i];
        if (b) b.click();
    } catch (e) {}
});
</script>
