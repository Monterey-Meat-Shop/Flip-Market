<?php

namespace App\Filament\Resources\ReturnRequestResource\Pages;

use App\Filament\Resources\ReturnRequestResource;
use App\Model\ReturnRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListReturnRequests extends ListRecords
{
    protected static string $resource = ReturnRequestResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('export')
    //             ->label('Export Returns')
    //             ->icon('heroicon-o-arrow-down-tray')
    //             ->color('success')
    //             ->action(function () {
    //                 // Add export logic here if needed
    //             }),
    //     ];
    // }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Returns')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at'))
                ->badge(fn () => $this->getModel()::whereNull('deleted_at')->count()),
            
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('return_status', 'pending')->whereNull('deleted_at'))
                ->badge(fn () => $this->getModel()::where('return_status', 'pending')->whereNull('deleted_at')->count())
                ->badgeColor('warning'),
    
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('return_status', 'approved')->whereNull('deleted_at'))
                ->badge(fn () => $this->getModel()::where('return_status', 'approved')->whereNull('deleted_at')->count())
                ->badgeColor('info'),
    
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('return_status', 'rejected')->whereNull('deleted_at'))
                ->badge(fn () => $this->getModel()::where('return_status', 'rejected')->whereNull('deleted_at')->count())
                ->badgeColor('danger'),
    
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('return_status', 'completed')->whereNull('deleted_at'))
                ->badge(fn () => $this->getModel()::where('return_status', 'completed')->whereNull('deleted_at')->count())
                ->badgeColor('success'),

            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(fn () => $this->getModel()::onlyTrashed()->count())
                ->badgeColor('gray'),
        ];
    }
}