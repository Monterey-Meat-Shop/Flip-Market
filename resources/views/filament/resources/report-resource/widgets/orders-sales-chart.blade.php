<div class="orders-sales-chart-widget" wire:poll.5000ms>
    @php
        $labels = $labels ?: ['Week 1','Week 2','Week 3','Week 4','Week 5','Week 6'];
        $sales = (is_array($sales) && count($sales) > 0) ? $sales : [1200, 1800, 1600, 2000, 2500, 3200];
        $orders = (is_array($orders) && count($orders) > 0) ? $orders : [5, 9, 7, 12, 14, 18];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Sales (Blue Border & Glow) --}}
        <div class="rounded-2xl bg-slate-900/70 backdrop-blur-xl border border-blue-400/40 shadow-xl hover:shadow-blue-500/30 transition duration-500">
            <div class="flex justify-center px-5 py-3 border-b border-blue-400/20 bg-gradient-to-r from-blue-600/20 to-blue-500/10 rounded-t-2xl">
                <h3 class="text-sm font-bold text-blue-300">Sales Overview</h3>
            </div>
            <div class="p-5">
                <div wire:ignore style="height:240px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Orders (Blue Border & Glow) --}}
        <div class="rounded-2xl bg-slate-900/70 backdrop-blur-xl border border-blue-400/40 shadow-xl hover:shadow-blue-500/30 transition duration-500">
            <div class="flex justify-center px-5 py-3 border-b border-blue-400/20 bg-gradient-to-r from-blue-600/20 to-blue-500/10 rounded-t-2xl">
                <h3 class="text-sm font-bold text-blue-300">Orders Overview</h3>
            </div>
            <div class="p-5">
                <div wire:ignore style="height:240px;">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endonce

    <script>
    (function(){
        const labels = @json($labels);
        const sales = @json($sales);
        const orders = @json($orders);

        function makeChart(el, cfg) {
            if (!el) return;
            const ctx = el.getContext('2d');
            if (el.__chart__) el.__chart__.destroy();

            el.__chart__ = new Chart(ctx, {
                type: cfg.type,
                data: cfg.data(ctx),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1200, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            borderColor: '#334155',
                            borderWidth: 1,
                            titleColor: '#f9fafb',
                            bodyColor: '#e2e8f0',
                            padding: 12,
                            displayColors: false,
                        }
                    },
                    scales: cfg.scales,
                }
            });
        }

        function renderCharts() {
            // Sales Chart
            makeChart(document.getElementById("salesChart"), {
                type: 'line',
                data: (ctx) => {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 240);
                    gradient.addColorStop(0, 'rgba(59,130,246,0.45)'); // blue gradient
                    gradient.addColorStop(1, 'rgba(59,130,246,0.05)');
                    return {
                        labels,
                        datasets: [{
                            label: 'Sales',
                            data: sales,
                            borderColor: '#3b82f6',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.45,
                            borderWidth: 2.5,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#0f172a',
                            pointBorderWidth: 2,
                        }]
                    };
                },
                scales: {
                    x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,0.08)' } },
                    y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,0.08)' } }
                }
            });

            // Orders Chart
            makeChart(document.getElementById("ordersChart"), {
                type: 'bar',
                data: (ctx) => ({
                    labels,
                    datasets: [{
                        label: 'Orders',
                        data: orders,
                        backgroundColor: (context) => {
                            const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 240);
                            gradient.addColorStop(0, 'rgba(59,130,246,0.9)');
                            gradient.addColorStop(0.5, 'rgba(96,165,250,0.6)');
                            gradient.addColorStop(1, 'rgba(147,197,253,0.3)');
                            return gradient;
                        },
                        borderRadius: 14,
                        barPercentage: 0.55,
                        categoryPercentage: 0.45,
                    }]
                }),
                scales: {
                    x: { ticks: { color: '#9ca3af' }, grid: { display: false } },
                    y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,0.08)' } }
                }
            });
        }

        if (window.Chart) renderCharts();
        document.addEventListener('livewire:load', renderCharts);
        if (window.Livewire) Livewire.hook('message.processed', renderCharts);
    })();
    </script>
</div>
