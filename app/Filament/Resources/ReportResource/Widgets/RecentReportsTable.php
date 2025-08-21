<?php


namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Report;

class RecentReportsTable extends Widget
{
    protected static string $view = 'filament.widgets.recent-reports-table';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $rows = Report::query()
            ->select(['id', 'title', 'status', 'report_date', 'created_at', 'is_visible'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        return [
            'reports' => $rows,
        ];
    }
}