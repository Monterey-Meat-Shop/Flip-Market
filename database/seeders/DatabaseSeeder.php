<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123'),
        ]);

        User::factory()->create([
            'name' => 'Castro',
            'email' => 'casti@gmail.com',
            'password' => Hash::make('123'),
        ]);

        $this->call([
            //ProductSeeder::class,
            BrandsSeeder::class,
            CategoriesSeeder::class
        ]);

        
    }
}
