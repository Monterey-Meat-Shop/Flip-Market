<?php

namespace App\Filament\Resources\ReturnRequestResource\Widgets;

use App\Models\ReturnRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReturnStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $pendingReturns = ReturnRequest::where('return_status', 'pending')->count();
        $approvedReturns = ReturnRequest::where('return_status', 'approved')->count();
        $completedReturns = ReturnRequest::where('return_status', 'completed')->count();
        $rejectedReturns = ReturnRequest::where('return_status', 'rejected')->count();
        
        $totalReturns = ReturnRequest::count();
        $returnRate = $totalReturns > 0 
            ? round(($totalReturns / \App\Models\Order::count()) * 100, 1) 
            : 0;

        return [
            Stat::make('Pending Returns', $pendingReturns)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, $pendingReturns])
                ->url(route('filament.admin.resources.return-requests.index', ['activeTab' => 'pending'])),
            
            Stat::make('Approved Returns', $approvedReturns)
                ->description('Ready for processing')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->chart([2, 4, 3, 5, 4, 6, $approvedReturns])
                ->url(route('filament.admin.resources.return-requests.index', ['activeTab' => 'approved'])),
            
            Stat::make('Completed Returns', $completedReturns)
                ->description('Successfully processed')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 6, $completedReturns]),
            
            Stat::make('Return Rate', $returnRate . '%')
                ->description('Total orders returned')
                ->descriptionIcon($returnRate > 10 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($returnRate > 10 ? 'danger' : 'success'),
            Stat::make('total_returns')
                ->label('Total Returns')
                ->value(ReturnRequest::count()),
        ];
    }
}