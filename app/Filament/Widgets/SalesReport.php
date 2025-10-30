<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesReport extends ChartWidget
{
    protected static ?string $heading = 'Sales Overview';
    public static ?int $sort = 2;

    // 👇 This makes "daily" the default selected filter
    protected static ?string $pollingInterval = null;
    public ?string $filter = 'daily';

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Daily',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
        ];
    }

    protected function getData(): array
    {
        switch ($this->filter) {
            case 'daily':
                $trend = Trend::model(Order::class)
                    ->between(
                        start: now()->startOfMonth(),
                        end: now()->endOfMonth(),
                    )
                    ->perDay()
                    ->sum('total_amount');
                $label = 'Daily Sales (This Month)';
                break;

            case 'monthly':
                $trend = Trend::model(Order::class)
                    ->between(
                        start: now()->startOfYear(),
                        end: now()->endOfYear(),
                    )
                    ->perMonth()
                    ->sum('total_amount');
                $label = 'Monthly Sales (This Year)';
                break;

            case 'yearly':
                $trend = Trend::model(Order::class)
                    ->between(
                        start: now()->subYear(0)->startOfYear(),
                        end: now()->addYears(5)->endOfYear(),
                    )
                    ->perYear()
                    ->sum('total_amount');
                $label = 'Yearly Sales (2025-2029)';
                break;

            default:
                $trend = collect();
                $label = 'No Data';
        }

        $labels = $trend->map(fn (TrendValue $v) => $v->date);
        $values = $trend->map(fn (TrendValue $v) => round($v->aggregate, 2));

        return [
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $values,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.3)',
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
