<?php

namespace App\Filament\Resources\ReturnRequestResource\Pages;

use App\Filament\Resources\ReturnRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditReturnRequest extends EditRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Archive')
                ->visible(fn ($record) => in_array($record->return_status, ['completed', 'rejected']))
                ->modalHeading('Archive Return Request')
                ->modalDescription('Are you sure you want to archive this return request? You can restore it later if needed.')
                ->modalSubmitActionLabel('Archive') 
                ->modalCancelActionLabel('Cancel') 
                ->color('danger')
                ->icon('heroicon-o-archive-box'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Return request updated')
            ->body('The return request has been updated successfully.');
    }
}
