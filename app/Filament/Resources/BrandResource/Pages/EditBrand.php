<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                        ->label('Archive')
                        ->modalHeading('Archive Brand')
                        ->modalDescription('Are you sure you want to archive this brand? You can restore it later if needed.')
                        ->modalSubmitActionLabel('Archive') 
                        ->modalCancelActionLabel('Cancel') 
                        ->color('danger')
                        ->icon('heroicon-o-archive-box'),
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
            ->title('Brand updated')
            ->body('The brand has been updated successfully.');
    }
}