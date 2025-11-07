<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Address;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Metro Manila addresses for each customer
        $addresses = [
            ['customerID' => 1, 'address_line_1' => '123 Mabini St', 'address_line_2' => 'Brgy. 1', 'city' => 'Manila', 'province' => 'Metro Manila', 'postal_code' => '1000'],
            ['customerID' => 2, 'address_line_1' => '456 Taft Ave', 'address_line_2' => null, 'city' => 'Pasay', 'province' => 'Metro Manila', 'postal_code' => '1300'],
            ['customerID' => 3, 'address_line_1' => '789 Roxas Blvd', 'address_line_2' => 'Unit 5', 'city' => 'Pasay', 'province' => 'Metro Manila', 'postal_code' => '1301'],
            ['customerID' => 4, 'address_line_1' => '12 Ortigas Ave', 'address_line_2' => 'Brgy. Ugong', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'postal_code' => '1550'],
            ['customerID' => 5, 'address_line_1' => '34 EDSA', 'address_line_2' => 'Corner Quezon Ave', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'postal_code' => '1100'],
            ['customerID' => 6, 'address_line_1' => '56 Taft Ave', 'address_line_2' => null, 'city' => 'Manila', 'province' => 'Metro Manila', 'postal_code' => '1001'],
            ['customerID' => 7, 'address_line_1' => '78 Commonwealth Ave', 'address_line_2' => 'Brgy. Holy Spirit', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'postal_code' => '1119'],
            ['customerID' => 8, 'address_line_1' => '90 España Blvd', 'address_line_2' => null, 'city' => 'Manila', 'province' => 'Metro Manila', 'postal_code' => '1015'],
            ['customerID' => 9, 'address_line_1' => '21 Ayala Ave', 'address_line_2' => 'Makati Central', 'city' => 'Makati', 'province' => 'Metro Manila', 'postal_code' => '1200'],
            ['customerID' => 10, 'address_line_1' => '33 Alabang Zapote Rd', 'address_line_2' => null, 'city' => 'Muntinlupa', 'province' => 'Metro Manila', 'postal_code' => '1770'],
        ];

        foreach ($addresses as $addr) {
            Address::create($addr);
        }
    }
}
