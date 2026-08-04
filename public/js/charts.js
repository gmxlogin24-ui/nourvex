function renderEarningsTrend(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                borderColor: '#c2410c', borderWidth: 2, pointHoverRadius: 4,
                backgroundColor: 'rgba(194,65,12,0.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#8a8a90', font: { size: 10 } } },
                y: { display: false }
            }
        }
    });
}

function renderEarningsBreakdown(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: ['#c2410c', '#f59e0b', '#fdba74'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('navSearch');
    const results = document.getElementById('searchResults');
    if (!input) return;

    let timeout = null;
    input.addEventListener('input', function () {
        clearTimeout(timeout);
        const q = input.value.trim();
        if (q.length < 2) {
            results.classList.add('hidden');
            return;
        }
        timeout = setTimeout(() => {
            fetch('/search?q=' + encodeURIComponent(q))
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        results.innerHTML = '<div class="search-item text-muted">No results</div>';
                    } else {
                        results.innerHTML = data.map(item =>
                            `<a href="${item.url}" class="search-item">
                                <span class="search-type">${item.type}</span>
                                <span>${item.title}</span>
                                <span class="text-accent text-xs ml-auto">${item.meta}</span>
                            </a>`
                        ).join('');
                    }
                    results.classList.remove('hidden');
                });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#navSearch') && !e.target.closest('#searchResults')) {
            results.classList.add('hidden');
        }
    });
});

