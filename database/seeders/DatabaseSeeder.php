<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, call the RoleSeeder to ensure all roles exist in the database.
        $this->call([
            RoleSeeder::class,
            BrandsSeeder::class,
            CategoriesSeeder::class,
            PaymentMethodSeeder::class,
        ]);
        
        // Create the 'Admin' user and assign the 'admin' role.
        $adminUser = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123'),
        ]);
        $adminUser->assignRole('admin');

        // Create the 'Test' user and assign the 'customer' role.
        $testUser = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => Hash::make('test'),
        ]);
        $testUser->assignRole('manager');
        
    }
}