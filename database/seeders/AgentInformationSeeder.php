<?php

namespace Database\Seeders;

use Database\Factories\AgentInformationFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AgentInformationFactory::factory()->count(10)->create();
    }
}
