
<div class="rounded-lg shadow-sm bg-white p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-700">Orders & Payments (last 7 days)</h3>
        <div class="text-xs text-gray-500">Realtime overview</div>
    </div>

    <div style="height:220px; min-height:160px;">
        <canvas id="reportsChartCanvas_{{ \Illuminate\Support\Str::random(6) }}" class="w-full h-full"></canvas>
    </div>

    <script>
        (function() {
            const labels = @json($labels);
            const orders = @json($ordersSeries);
            const payments = @json($paymentsSeries);
            const canvas = document.querySelector('#reportsChartCanvas_{{ \Illuminate\Support\Str::random(6) }}') ||
                           document.querySelector('canvas[id^="reportsChartCanvas_"]');

            function renderChart(el) {
                if (!el) return;
                const ctx = el.getContext('2d');
                if (!ctx) return;

                if (el.__chart__) { el.__chart__.destroy(); }

                el.__chart__ = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Orders',
                                data: orders,
                                borderColor: '#0ea5a4',
                                backgroundColor: 'rgba(14,165,164,0.08)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            },
                            {
                                label: 'Payments',
                                data: payments,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.06)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6b7280' } },
                            y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#6b7280' } }
                        }
                    }
                });
            }

            if (window.Chart) {
                // Chart.js already loaded
                renderChart(document.querySelector('canvas[id^="reportsChartCanvas_"]'));
                return;
            }

            // load Chart.js from CDN and render when ready
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            s.onload = function() {
                renderChart(document.querySelector('canvas[id^="reportsChartCanvas_"]'));
            };
            document.head.appendChild(s);
        })();
    </script>
</div>