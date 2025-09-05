<div class="orders-sales-chart-widget" wire:poll.5000ms>
    @php
        $totalRevenue = is_array($sales) ? array_sum($sales) : 0;
        $totalOrders = is_array($orders) ? array_sum($orders) : 0;
        $lastRevenue = (is_array($sales) && count($sales) > 0) ? $sales[count($sales) - 1] : 0;
        $prevRevenue = (is_array($sales) && count($sales) > 1) ? $sales[count($sales) - 2] : 0;
        if ($prevRevenue == 0.0) {
            $revenueChangePct = $lastRevenue === 0.0 ? 0.0 : 100.0;
        } else {
            $revenueChangePct = ($lastRevenue - $prevRevenue) / abs($prevRevenue) * 100.0;
        }
        $revenueUp = $lastRevenue >= $prevRevenue;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        {{-- Sales card (compact) --}}
        <div class="rounded-lg overflow-hidden border border-slate-700 bg-slate-900">
            <div class="px-3 py-2 bg-gradient-to-r from-emerald-600 to-emerald-650">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-emerald-100/80">Sales</div>
                        <div class="mt-1 text-lg font-bold text-white">₱{{ number_format($totalRevenue, 2) }}</div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="inline-flex items-center rounded-full bg-white/8 px-2 py-0.5 text-xs">
                            @if($revenueUp)
                                <span class="text-emerald-100 mr-1">▲ {{ number_format($revenueChangePct,1) }}%</span>
                            @else
                                <span class="text-amber-300 mr-1">▼ {{ number_format(abs($revenueChangePct),1) }}%</span>
                            @endif
                            <span class="text-emerald-100/80 text-[10px]">vs last</span>
                        </div>

                        <div class="text-right">
                            <div class="text-xs text-emerald-100/70">Orders</div>
                            <div class="text-white text-sm font-medium">{{ number_format($totalOrders) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-2 bg-slate-800">
                <div wire:ignore style="height:120px;">
                    <canvas id="salesChart_{{ \Illuminate\Support\Str::random(8) }}" class="w-full h-full"></canvas>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-sm text-emerald-200">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs">Sales</span>
                        <span class="ml-1 text-white text-xs font-medium">{{ number_format($lastRevenue, 0) }}</span>
                    </div>
                    <div class="text-xs text-slate-400">12m</div>
                </div>
            </div>
        </div>

        {{-- Orders card (compact) --}}
        <div class="rounded-lg overflow-hidden border border-slate-700 bg-slate-900">
            <div class="px-3 py-2 bg-gradient-to-r from-indigo-600 to-indigo-650">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-indigo-100/80">Orders</div>
                        <div class="mt-1 text-lg font-semibold text-white">{{ number_format($totalOrders) }}</div>
                    </div>
                    <div class="text-xs text-indigo-100/80">12m</div>
                </div>
            </div>

            <div class="p-2 bg-slate-800">
                <div wire:ignore style="height:120px;">
                    <canvas id="ordersChart_{{ \Illuminate\Support\Str::random(8) }}" class="w-full h-full"></canvas>
                </div>

                <div class="mt-2 flex items-center space-x-3">
                    <div class="w-8 h-3 rounded-full overflow-hidden bg-indigo-900/10">
                        <div class="wave-orders h-full" style="width:140%;"></div>
                    </div>
                    <div class="text-sm text-indigo-200 text-xs">
                        <span>Orders</span>
                        <span class="ml-1 text-white text-xs font-medium">{{ number_format($orders[count($orders)-1] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .orders-sales-chart-widget { color: #dbeafe; }
        .wave-sales { background: linear-gradient(90deg, rgba(52,211,153,0.18), rgba(52,211,153,0.06) 40%, rgba(52,211,153,0.00) 100%); animation: wave-move 2.4s ease-in-out infinite; }
        .wave-orders { background: linear-gradient(90deg, rgba(99,102,241,0.18), rgba(99,102,241,0.06) 40%, rgba(99,102,241,0.00) 100%); animation: wave-move 2.2s ease-in-out infinite; }
        @keyframes wave-move { 0% { transform: translateX(-35%);} 50% { transform: translateX(-10%);} 100% { transform: translateX(-35%);} }
        .chart-glow { filter: drop-shadow(0 6px 12px rgba(16,185,129,0.08)); }
    </style>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endonce

    <script>
    (function(){
        const labels = @json($labels);
        const sales = @json($sales);
        const orders = @json($orders);
        const salesUp = @json($salesUp ?? false);

        function makeChart(elSelector, cfg) {
            const el = document.querySelector(elSelector);
            if (!el) return;
            const ctx = el.getContext('2d');
            if (el.__chart__) el.__chart__.destroy();

            const grad = ctx.createLinearGradient(0, 0, 0, el.height);
            grad.addColorStop(0, cfg.gradientFrom);
            grad.addColorStop(1, cfg.gradientTo);

            el.__chart__ = new Chart(ctx, {
                type: cfg.type,
                data: cfg.data(grad),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: 'rgba(148,163,184,0.45)', maxRotation: 0, minRotation: 0 } },
                        y: { grid: { color: 'rgba(148,163,184,0.04)' }, ticks: { color: 'rgba(148,163,184,0.45)', beginAtZero: true } }
                    },
                    elements: { point: { radius: 2, hoverRadius: 4 }, bar: { borderRadius: 6 } }
                }
            });
            el.classList.add('chart-glow');
        }

        function render() {
            const salesCanvas = document.querySelector('canvas[id^="salesChart_"]');
            if (salesCanvas) {
                makeChart('#' + salesCanvas.id, {
                    type: 'line',
                    gradientFrom: 'rgba(16,185,129,0.18)',
                    gradientTo: 'rgba(16,185,129,0.02)',
                    data: (gradient) => ({ labels: labels, datasets: [{ label: 'Sales', data: sales, borderColor: '#10b981', backgroundColor: gradient, fill: true, tension: 0.36, pointBackgroundColor: '#0f172a', pointBorderColor: '#10b981', pointBorderWidth: 1 }]})
                });
            }

            const ordersCanvas = document.querySelector('canvas[id^="ordersChart_"]');
            if (ordersCanvas) {
                makeChart('#' + ordersCanvas.id, {
                    type: 'bar',
                    gradientFrom: 'rgba(99,102,241,0.12)',
                    gradientTo: 'rgba(99,102,241,0.02)',
                    data: (gradient) => ({ labels: labels, datasets: [{ label: 'Orders', data: orders, backgroundColor: '#6366f1', borderRadius: 6, barPercentage: 0.6, categoryPercentage: 0.5 }]})
                });
            }
        }

        if (window.Chart) { render(); }
        document.addEventListener('livewire:load', function () {
            render();
            if (window.Livewire) {
                Livewire.hook('message.processed', () => { render(); });
            }
        });

        const s = document.querySelector('script[src*="chart.umd.min.js"]');
        if (!s) {
            const s2 = document.createElement('script');
            s2.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            s2.onload = () => document.dispatchEvent(new Event('livewire:load'));
            document.head.appendChild(s2);
        }
    })();
    </script>
</div>