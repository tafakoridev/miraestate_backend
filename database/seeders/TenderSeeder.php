<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tender;

class TenderSeeder extends Seeder
{
    public function run()
    {
        Tender::factory()->count(10)->create();
    }
}
