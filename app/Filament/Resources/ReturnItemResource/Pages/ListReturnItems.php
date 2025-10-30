<?php

namespace App\Filament\Resources\ReturnItemResource\Pages;

use App\Filament\Resources\ReturnItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnItems extends ListRecords
{
    protected static string $resource = ReturnItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
