<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Report;
use Illuminate\Support\Facades\Cache;

class ReportsLineChart extends Widget
{
    protected static string $view = 'filament.widgets.reports-line-chart';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $ttl = 60;
        $days = 7;

        $data = Cache::remember('reportstats:reports_last7', $ttl, function () use ($days) {
            $labels = [];
            $counts = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->startOfDay();
                $labels[] = $date->format('M d');
                $counts[] = Report::whereDate('created_at', $date)->count();
            }
            return compact('labels', 'counts');
        });

        return [
            'labels' => $data['labels'],
            'data' => $data['counts'],
        ];
    }
}