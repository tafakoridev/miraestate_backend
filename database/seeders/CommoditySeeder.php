<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commodity;

class CommoditySeeder extends Seeder
{
    public function run()
    {
        Commodity::factory()->count(30)->create();
    }
}
