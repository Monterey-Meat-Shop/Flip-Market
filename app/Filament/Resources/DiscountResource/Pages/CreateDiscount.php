<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Filament\Notifications\Notification;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Discount created')
            ->body('The discount has been created successfully.');
    }
}
