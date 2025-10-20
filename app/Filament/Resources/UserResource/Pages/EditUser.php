<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Customer;
use App\Models\Address;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
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
            ->title('User updated')
            ->body('The user has been updated successfully.');
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $user = $this->record;

        // Convert role IDs → role names
        $roleNames = Role::whereIn('id', (array) ($data['roles'] ?? []))
            ->pluck('name')
            ->toArray();

        // Only save customer + address if role = customer
        if (in_array('customer', $roleNames)) {
            // Create or update customer
            $customer = Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['name'] ?? null,
                    'last_name'  => $data['last_name'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                ]
            );

            // Create or update address for that customer
            if ($customer) {
                Address::updateOrCreate(
                    ['customerID' => $customer->customerID],
                    [
                        'address_line_1' => $data['address_line_1'] ?? null,
                        'address_line_2' => $data['address_line_2'] ?? null,
                        'city'           => $data['city'] ?? null,
                        'province'       => $data['province'] ?? null,
                        'postal_code'    => $data['postal_code'] ?? null,
                    ]
                );

            }
        }
    }
}
