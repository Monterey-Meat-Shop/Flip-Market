<?php

namespace App\Filament\Resources\SalesResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // Stat::make('Total Sales')
            //     ->value('$12,430')
            //     ->description('Last 30 days'),

            // Stat::make('Orders')
            //     ->value('342')
            //     ->description('This month'),

            // Stat::make('New Users')
            //     ->value('58')
            //     ->description('Signups'),

            // Stat::make('Conversion')
            //     ->value('6.2%')
            //     ->description('Rate'),
        ];
    }
}

/**
 * Provide a compatibility alias in the global widgets namespace so
 * code that expects App\Filament\Widgets\StatsOverview still resolves.
 * Keeping this file in the SalesResource widgets folder avoids moving files.
 */
namespace App\Filament\Widgets;

class StatsOverview extends \App\Filament\Resources\SalesResource\Widgets\StatsOverview
{
    // intentionally empty — extends implementation above
}
