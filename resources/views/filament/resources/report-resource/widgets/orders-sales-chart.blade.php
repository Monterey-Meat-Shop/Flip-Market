<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white dark:bg-gray-50 rounded-lg shadow-sm border p-3">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-900">Sales Performance</h3>
                <p class="text-xs text-gray-500">Monthly sales trends</p>
            </div>
            <div class="text-xs text-gray-500">Last 12 months</div>
        </div>

        <div style="height:160px;">
            <canvas id="salesChart_{{ \Illuminate\Support\Str::random(8) }}" class="w-full h-full"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-50 rounded-lg shadow-sm border p-3">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-900">Order Analytics</h3>
                <p class="text-xs text-gray-500">Monthly order volume</p>
            </div>
            <div class="text-xs text-gray-500">Last 12 months</div>
        </div>

        <div style="height:160px;">
            <canvas id="ordersChart_{{ \Illuminate\Support\Str::random(8) }}" class="w-full h-full"></canvas>
        </div>
    </div>
</div>

<script>
(function(){
    const labels = @json($labels);
    const sales = @json($sales);
    const orders = @json($orders);

    function makeChart(id, cfg) {
        const el = document.querySelector(id);
        if (!el) return;
        const ctx = el.getContext('2d');
        if (el.__chart__) el.__chart__.destroy();

        // create gradient for line fills
        const gradient = ctx.createLinearGradient(0, 0, 0, el.height);
        gradient.addColorStop(0, cfg.gradientFrom);
        gradient.addColorStop(1, cfg.gradientTo);

        const options = {
            type: cfg.type,
            data: cfg.data(gradient),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#fff',
                        titleColor: '#111827',
                        bodyColor: '#111827',
                        borderColor: 'rgba(0,0,0,0.06)',
                        borderWidth: 1,
                        padding: 8,
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        displayColors: true,
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed.y ?? ctx.parsed;
                                return ctx.dataset.label ? ctx.dataset.label + ': ' + Number(v).toLocaleString() : Number(v).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7280', padding: 6, maxRotation: 0, autoSkip: true }
                    },
                    y: {
                        grid: { color: 'rgba(15,23,42,0.04)', borderDash: [4,4] },
                        ticks: { color: '#6b7280', padding: 6, beginAtZero: true }
                    }
                },
                elements: {
                    point: { radius: cfg.pointRadius ?? 2, hoverRadius: 4 }
                }
            }
        };

        el.__chart__ = new Chart(ctx, options);
    }

    function render() {
        makeChart('canvas[id^="salesChart_"]', {
            type: 'line',
            gradientFrom: 'rgba(17,24,39,0.06)',
            gradientTo: 'rgba(17,24,39,0.00)',
            pointRadius: 3,
            data: (gradient) => ({
                labels: labels,
                datasets: [{
                    label: 'Sales',
                    data: sales,
                    borderColor: '#0f172a',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#0f172a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1
                }]
            })
        });

        makeChart('canvas[id^="ordersChart_"]', {
            type: 'bar',
            gradientFrom: 'rgba(59,130,246,0.06)',
            gradientTo: 'rgba(59,130,246,0.00)',
            pointRadius: 0,
            data: (gradient) => ({
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: orders,
                    backgroundColor: '#0f172a',
                    borderRadius: 6,
                    barPercentage: 0.65,
                    categoryPercentage: 0.5
                }]
            })
        });
    }

    if (window.Chart) { render(); return; }

    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
    s.onload = render;
    document.head.appendChild(s);
})();
</script>