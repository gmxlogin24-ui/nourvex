document.addEventListener('DOMContentLoaded', function () {
    const targets = document.querySelectorAll('.reveal-on-scroll');
    if (!targets.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0, rootMargin: '0px 0px -20px 0px' });

    targets.forEach(el => observer.observe(el));

    // Safety net: force-reveal everything after 1.5s no matter what,
    // so a failed observer/layout quirk can never leave content permanently invisible
    setTimeout(function () {
        document.querySelectorAll('.reveal-on-scroll:not(.is-visible)').forEach(el => {
            el.classList.add('is-visible');
        });
    }, 1500);
});
