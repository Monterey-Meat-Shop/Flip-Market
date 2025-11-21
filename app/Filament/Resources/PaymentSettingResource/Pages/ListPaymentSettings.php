<?php

namespace App\Filament\Resources\PaymentSettingResource\Pages;

use App\Filament\Resources\PaymentSettingResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\PaymentSetting;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentSettings extends ListRecords
{
    protected static string $resource = PaymentSettingResource::class;

    // Remove Create button
    protected function getHeaderActions(): array
    {
        return [];
    }

    // ✅ Fix method signature (MUST return ?Builder)
    public function getTableQuery(): ?Builder
    {
        return PaymentSetting::query()->limit(1);
    }

    // Must be public
    public function mount(): void
    {
        parent::mount();

        // Auto-create if empty
        if (PaymentSetting::count() === 0) {
            PaymentSetting::create([]);
        }
    }
}
