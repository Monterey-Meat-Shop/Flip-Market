<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Customer;
use App\Models\Address;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $user = $this->record;

        // Get the selected role IDs and check if customer role is selected
        $selectedRoleIds = $data['roles'] ?? [];
        $customerRoleId = Role::where('name', 'customer')->value('id');
        
        // Ensure selectedRoleIds is an array
        if (!is_array($selectedRoleIds)) {
            $selectedRoleIds = [$selectedRoleIds];
        }
        
        if (in_array($customerRoleId, $selectedRoleIds)) {
            // Create or update customer
            $customer = Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['name'] ?? null,
                    'last_name'  => $data['last_name'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                ]
            );

            // Create or update address
            if ($customer) {
                Address::updateOrCreate(
                    ['customer_id' => $customer->id],
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