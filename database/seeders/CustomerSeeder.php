<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Customer 1
        $user1 = User::create([
            'name'      => 'Juan',
            'last_name' => 'Dela Cruz',
            'email'     => 'juan@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user1->assignRole('customer');
        Customer::create([
            'user_id'    => $user1->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'phone'      => '09123456701',
        ]);

        // Customer 2
        $user2 = User::create([
            'name'      => 'Maria',
            'last_name' => 'Santos',
            'email'     => 'maria@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user2->assignRole('customer');
        Customer::create([
            'user_id'    => $user2->id,
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'phone'      => '09123456702',
        ]);

        // Customer 3
        $user3 = User::create([
            'name'      => 'Pedro',
            'last_name' => 'Reyes',
            'email'     => 'pedro@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user3->assignRole('customer');
        Customer::create([
            'user_id'    => $user3->id,
            'first_name' => 'Pedro',
            'last_name'  => 'Reyes',
            'phone'      => '09123456703',
        ]);

        // Customer 4
        $user4 = User::create([
            'name'      => 'Ana',
            'last_name' => 'Lopez',
            'email'     => 'ana@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user4->assignRole('customer');
        Customer::create([
            'user_id'    => $user4->id,
            'first_name' => 'Ana',
            'last_name'  => 'Lopez',
            'phone'      => '09123456704',
        ]);

        // Customer 5
        $user5 = User::create([
            'name'      => 'Carlos',
            'last_name' => 'Torres',
            'email'     => 'carlos@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user5->assignRole('customer');
        Customer::create([
            'user_id'    => $user5->id,
            'first_name' => 'Carlos',
            'last_name'  => 'Torres',
            'phone'      => '09123456705',
        ]);

        // Customer 6
        $user6 = User::create([
            'name'      => 'Lucia',
            'last_name' => 'Garcia',
            'email'     => 'lucia@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user6->assignRole('customer');
        Customer::create([
            'user_id'    => $user6->id,
            'first_name' => 'Lucia',
            'last_name'  => 'Garcia',
            'phone'      => '09123456706',
        ]);

        // Customer 7
        $user7 = User::create([
            'name'      => 'Ramon',
            'last_name' => 'Cruz',
            'email'     => 'ramon@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user7->assignRole('customer');
        Customer::create([
            'user_id'    => $user7->id,
            'first_name' => 'Ramon',
            'last_name'  => 'Cruz',
            'phone'      => '09123456707',
        ]);

        // Customer 8
        $user8 = User::create([
            'name'      => 'Elena',
            'last_name' => 'Torres',
            'email'     => 'elena@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user8->assignRole('customer');
        Customer::create([
            'user_id'    => $user8->id,
            'first_name' => 'Elena',
            'last_name'  => 'Torres',
            'phone'      => '09123456708',
        ]);

        // Customer 9
        $user9 = User::create([
            'name'      => 'Miguel',
            'last_name' => 'Santos',
            'email'     => 'miguel@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user9->assignRole('customer');
        Customer::create([
            'user_id'    => $user9->id,
            'first_name' => 'Miguel',
            'last_name'  => 'Santos',
            'phone'      => '09123456709',
        ]);

        // Customer 10
        $user10 = User::create([
            'name'      => 'Isabel',
            'last_name' => 'Reyes',
            'email'     => 'isabel@gmail.com',
            'password'  => Hash::make('customer123'),
        ]);
        $user10->assignRole('customer');
        Customer::create([
            'user_id'    => $user10->id,
            'first_name' => 'Isabel',
            'last_name'  => 'Reyes',
            'phone'      => '09123456710',
        ]);
    }
}
