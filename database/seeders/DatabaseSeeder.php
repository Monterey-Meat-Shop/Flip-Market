<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

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
        ]);

        // Create or update admin to avoid duplicate key errors
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'last_name' => 'Admin',
                'email_verified_at' => now(),
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password123')),
                'remember_token' => Str::random(10),
            ]
        );

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
    }
}
