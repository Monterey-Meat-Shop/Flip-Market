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
        // Check if a 'Guest' user already exists
        if (User::where('email', 'guest@example.com')->doesntExist()) {
            $user = User::create([
                'name'       => 'Guest', // optional if your users table has it
                'last_name'  => '',
                'email'      => 'guest@example.com',
                'password'   => Hash::make('password'),
                'is_active'  => true,
            ]);

            Customer::create([
                'user_id'    => $user->id,
                'first_name' => 'Guest',
                'last_name'  => '',
                'phone'      => '',
            ]);
        }
    }
}
