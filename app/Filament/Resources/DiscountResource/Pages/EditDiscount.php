<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDiscount extends EditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make()
            //             ->label('Archive')
            //             ->modalHeading('Archive Discount')
            //             ->modalDescription('Are you sure you want to archive this discount? You can restore it later if needed.')
            //             ->modalSubmitActionLabel('Archive') 
            //             ->modalCancelActionLabel('Cancel') 
            //             ->color('danger')
            //             ->icon('heroicon-o-archive-box'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Discount updated')
            ->body('The discount has been updated successfully.');
    }
}
