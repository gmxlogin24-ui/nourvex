@extends('layouts.app-authenticated')
@section('content')
<main class="max-w-6xl mx-auto p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;' : '' }}">
    <div class="mb-6 reveal-on-scroll">
        <h1 class="text-2xl font-bold">Money Tracker</h1>
        <p class="text-muted text-sm">Your income, spending, and category breakdown in one place.</p>
    </div>

    <div class="kpi-row reveal-on-scroll">
        <div class="kpi-tile featured">
            <div class="kpi-tile-top"><div class="kpi-tile-icon"><i data-lucide="wallet" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value">{{ number_format($balance * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.6em;font-weight:600;opacity:.7">≈ {{ number_format(($balance) * config('qcoin.q_per_naira_display', 10)) }} Q</small></p>
            <p class="kpi-tile-label">Current Balance</p>
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top"><div class="kpi-tile-icon"><i data-lucide="arrow-down-circle" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value" style="color:var(--color-accent3);">{{ number_format(($totalIn) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalIn) }}</small></p>
            <p class="kpi-tile-label">Total Money In</p>
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top"><div class="kpi-tile-icon"><i data-lucide="arrow-up-circle" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value" style="color:#ef4444;">{{ number_format(($totalOut) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalOut) }}</small></p>
            <p class="kpi-tile-label">Total Money Out</p>
        </div>
        <div class="kpi-tile">
            <div class="kpi-tile-top"><div class="kpi-tile-icon"><i data-lucide="trending-up" class="w-4 h-4"></i></div></div>
            <p class="kpi-tile-value">{{ number_format(($totalIn - $totalOut) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalIn - $totalOut) }}</small></p>
            <p class="kpi-tile-label">Net Flow</p>
        </div>
    </div>

    <div class="chart-card reveal-on-scroll mb-6">
        <h3>Monthly Cash Flow</h3>
        <p class="text-xs text-muted mb-2">Last 6 months</p>
        <div class="analytics-canvas-wrap"><canvas id="mtMonthlyChart"></canvas></div>
    </div>

    <div class="ring-card reveal-on-scroll mb-6">
        <h3 class="font-semibold mb-3">Spending by Category</h3>
        <div class="ring-card-body">
            <div class="ring-chart-wrap"><canvas id="mtCategoryRing"></canvas>
                <div class="ring-chart-center"><strong id="mtCategoryCenter">—</strong><span>top category</span></div>
            </div>
            <div class="ring-legend" id="mtCategoryLegend"></div>
        </div>
    </div>

    <div class="stat-card reveal-on-scroll">
        <h3 class="font-semibold mb-3">Recent Transactions</h3>
        @foreach($transactions as $t)
        <div class="tx-table-row">
            <span>{{ ucfirst(str_replace('_', ' ', $t->type)) }}</span>
            <span class="text-muted text-xs">{{ $t->created_at->format('M j, Y') }}</span>
            <span class="text-right font-medium" style="color:{{ $t->amount >= 0 ? 'var(--color-accent3)' : '#ef4444' }};">{{ number_format(($t->amount) * config('qcoin.q_per_naira_display', 10)) }} Q</span>
        </div>
        @endforeach
    </div>
</main>

<script>
(function () {
    if (typeof Chart === 'undefined') return;
    const monthly = @json($monthly);
    const ctx = document.getElementById('mtMonthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthly.map(m => m.ym),
                datasets: [
                    { label: 'In', data: monthly.map(m => m.inflow), backgroundColor: '#22c55e', borderRadius: 6 },
                    { label: 'Out', data: monthly.map(m => m.outflow), backgroundColor: '#ef4444', borderRadius: 6 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const categoryTotals = @json($categoryTotals);
    const labels = Object.keys(categoryTotals);
    const values = Object.values(categoryTotals);
    const colors = ['#6ea8ff', '#f4a261', '#4ade80', '#f472b6', '#a78bfa', '#fb923c'];
    const ringCtx = document.getElementById('mtCategoryRing');
    if (ringCtx && labels.length) {
        new Chart(ringCtx, { type: 'doughnut', data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0 }] }, options: { plugins: { legend: { display: false } }, cutout: '72%', responsive: true, maintainAspectRatio: false } });
        const legend = document.getElementById('mtCategoryLegend');
        labels.forEach((label, i) => {
            const row = document.createElement('div');
            row.className = 'ring-legend-row';
            row.innerHTML = '<span><span class="ring-legend-dot" style="background:' + colors[i % colors.length] + ';"></span>' + label.replace(/_/g, ' ') + '</span><strong>' + Math.round(Number(values[i]) * {{ (float) config('qcoin.q_per_naira_display', 10) }}).toLocaleString() + ' Q <small style="opacity:.4;font-weight:500">₦' + Number(values[i]).toLocaleString() + '</small></strong>';
            legend.appendChild(row);
        });
        document.getElementById('mtCategoryCenter').textContent = labels[0]?.replace(/_/g, ' ') || '—';
    }
})();
</script>
@endsection
