<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentExpertise;

class AgentExpertiseSeeder extends Seeder
{
    public function run()
    {
        AgentExpertise::factory()->count(10)->create();
    }
}
