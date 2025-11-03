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
        ]);

        // ADMIN (idempotent)
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'              => 'Admin',
                'last_name'         => 'Admin',
                'email_verified_at' => now(),
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'password123')),
                'remember_token'    => Str::random(10),
            ]
        );

        // Assign role safely (Spatie)
        if (method_exists($admin, 'syncRoles')) {
            $admin->syncRoles(['admin']);
        } elseif (method_exists($admin, 'assignRole')) {
            // Fallback if syncRoles isn't available
            if (!$admin->hasRole('admin')) {
                $admin->assignRole('admin');
            }
        }

        // MANAGER (idempotent)
        $manager = User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name'              => 'Manager',
                'last_name'         => '',
                'email_verified_at' => now(),
                'password'          => Hash::make(env('MANAGER_PASSWORD', 'manager')),
                'remember_token'    => Str::random(10),
            ]
        );

        if (method_exists($manager, 'syncRoles')) {
            $manager->syncRoles(['manager']);
        } elseif (method_exists($manager, 'assignRole')) {
            if (!$manager->hasRole('manager')) {
                $manager->assignRole('manager');
            }
        }

        // If you still want random users, do it without fixed emails:
        // User::factory(10)->create(); // ensures unique emails via factory definition
    }
}
