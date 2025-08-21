<div class="bg-white rounded-lg shadow-sm p-4">
    <div class="text-sm text-gray-600 font-medium">Reports (last 7 days)</div>
    <canvas id="reports-line-chart"
            data-labels="{{ json_encode($labels) }}"
            data-values="{{ json_encode($data) }}"
            class="mt-3"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('reports-line-chart');
    if (!el) return;
    const labels = JSON.parse(el.dataset.labels);
    const data = JSON.parse(el.dataset.values);
    if (typeof Chart === 'undefined') return;
    new Chart(el, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Reports',
                data,
                backgroundColor: 'rgba(59,130,246,0.08)',
                borderColor: '#3b82f6',
                tension: 0.3,
                fill: true,
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, precision: 0 } }
        }
    });
});
</script>