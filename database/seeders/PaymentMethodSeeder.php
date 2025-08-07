<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Carbon;     

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_method')->insert([ 
            [
                'method_name' => 'Gcash', 
                'is_active' => true,      
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],
            [
                'method_name' => 'Bank Transfer', 
                'is_active' => true,
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],
            [
                'method_name' => 'Cash on Delivery', 
                'is_active' => false,
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}