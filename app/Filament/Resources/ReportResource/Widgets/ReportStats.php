<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Report;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ReportStats extends BaseWidget
{
    // protected ?string $heading = 'Reports Overview';

    // protected function getStats(): array
    // // {
    // //     $ttl = 30;

    // //     $table = (new Report())->getTable();
    // //     if (! Schema::hasTable($table)) {
    // //         return [
    // //             Stat::make('reports_total', 'Reports')->value('0')->description('Total reports'),
    //         ];
    //     }

    //     $total = Cache::remember("reportstats:reports_count", $ttl, fn () => Report::count());
    //     $published = Schema::hasColumn($table, 'status') ? Cache::remember("reportstats:reports_published", $ttl, fn () => Report::where('status', 'published')->count()) : 0;
    //     $draft = Schema::hasColumn($table, 'status') ? Cache::remember("reportstats:reports_draft", $ttl, fn () => Report::where('status', 'draft')->count()) : 0;
    //     $visible = Schema::hasColumn($table, 'is_visible') ? Cache::remember("reportstats:reports_visible", $ttl, fn () => Report::where('is_visible', true)->count()) : 0;

    //     return [
    //         Stat::make('reports_total', 'Reports')->value((string) $total)->description('Total reports'),
    //         Stat::make('reports_published', 'Published')->value((string) $published)->description('Published reports'),
    //         Stat::make('reports_draft', 'Draft')->value((string) $draft)->description('Draft reports'),
    //         Stat::make('reports_visible', 'Visible')->value((string) $visible)->description('Visible in dashboard'),
    //     ];
    // }
}