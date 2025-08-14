<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class GuestCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if a 'Guest' customer already exists to prevent duplicates.
        if (Customer::where('first_name', 'Guest')->doesntExist()) 
        {
            Customer::create([
                'first_name' => 'Guest',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'password' => Hash::make(''),
            ]);

        }
    }
}
