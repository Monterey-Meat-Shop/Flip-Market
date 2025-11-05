<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Archive')
                        ->modalHeading('Archive Payment Method')
                        ->modalDescription('Are you sure you want to archive this payment method? You can restore it later if needed.')
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
            ->title('Payment Method updated')
            ->body('The payment method has been updated successfully.');
    }
}
