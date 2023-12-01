<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        // Adjust the number of records you want to seed
        Province::factory()->count(10)->create();
    }
}
