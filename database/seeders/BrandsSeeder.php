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
            ['name' => 'Nike', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Adidas','created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Puma','created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Vans','created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Converse','created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
