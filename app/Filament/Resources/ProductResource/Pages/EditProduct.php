<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                        ->label('Archive')
                        ->modalHeading('Archive Product')
                        ->modalDescription('Are you sure you want to archive this product? You can restore it later if needed.')
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
            ->title('Product updated')
            ->body('The product has been updated successfully.');
    }
}
