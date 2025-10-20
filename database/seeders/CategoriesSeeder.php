<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'Basketball',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Running',
                'created_at' => Carbon::now()
                , 'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Sneaker',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'Skaters',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],

            [
                'name' => 'High Tops',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
