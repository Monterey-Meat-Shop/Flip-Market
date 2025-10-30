<?php

namespace App\Filament\Resources\ReturnItemResource\Pages;

use App\Filament\Resources\ReturnItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnItem extends EditRecord
{
    protected static string $resource = ReturnItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
