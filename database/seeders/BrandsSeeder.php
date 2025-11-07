<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('brands')->insert([
            [
                'name' => 'Nike', 
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Adidas',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Puma',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Vans','
                created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Converse',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Jordan',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Under Armour',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'New Balance',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Reebok',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Anta',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
