<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            BrandsSeeder::class,
            CategoriesSeeder::class,
            PaymentMethodSeeder::class,
            GuestCustomerSeeder::class,
            // CustomerSeeder::class,
            // AddressSeeder::class,
            ProductSeeder::class,
            // CashierSalesSeeder::class,
        ]);

        // Admin user
        $adminUser = User::factory()->create([
            'name'       => 'Admin',
            'last_name'  => '',
            'email'      => 'admin@gmail.com',
            'password'   => Hash::make('123'),
        ]);
        $adminUser->assignRole('admin');

        // Manager user
        $managerUser = User::factory()->create([
            'name'       => 'Manager',
            'last_name'  => '',
            'email'      => 'manager@gmail.com',
            'password'   => Hash::make('manager'),
        ]);
        $managerUser->assignRole('manager');

        // Cashier user
        $cashierUser = User::factory()->create([
            'name'       => 'Cashier',
            'last_name'  => '',
            'email'      => 'cashier@gmail.com',
            'password'   => Hash::make('cashier'),
        ]);
        $cashierUser->assignRole('cashier');
    }
}
