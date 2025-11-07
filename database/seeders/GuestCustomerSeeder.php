<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GuestCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create Guest User
        $guestUser = User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest',
                'last_name' => '',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Get or create Guest Customer
        // Make sure to use the correct primary key
        $guestCustomer = Customer::firstOrCreate(
            ['user_id' => $guestUser->id],
            [
                'first_name' => 'Guest',
                'last_name' => '',
                'phone' => '',
            ]
        );

        // Optional: output the Guest Customer ID for verification
        $customerID = $guestCustomer->customerID ?? $guestCustomer->id;
        $this->command->info("Guest customer ID: $customerID");
    }
}
