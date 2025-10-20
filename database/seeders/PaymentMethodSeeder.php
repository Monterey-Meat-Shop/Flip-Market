<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create payment methods using updateOrCreate to avoid duplicates
        $paymentMethods = [
            ['method_name' => 'Cash', 'is_active' => true],
            ['method_name' => 'Gcash', 'is_active' => true],
            ['method_name' => 'Bank Transfer', 'is_active' => true],
            ['method_name' => 'Cash on Delivery', 'is_active' => true],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['method_name' => $method['method_name']], // Find by method_name
                $method // Create or update with this data
            );
        }
    }
}