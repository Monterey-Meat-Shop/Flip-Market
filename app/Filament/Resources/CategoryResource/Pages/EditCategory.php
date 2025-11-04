<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;


class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->label('Archive')
                        ->modalHeading('Archive Category')
                        ->modalDescription('Are you sure you want to archive this category? You can restore it later if needed.')
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
            ->title('Category updated')
            ->body('The category has been updated successfully.');
    }
}
