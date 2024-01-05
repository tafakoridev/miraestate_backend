<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentExpertise;
use App\Models\AgentInformation;
use App\Models\Auction;
use App\Models\Category;
use App\Models\City;
use App\Models\Commodity;
use App\Models\Province;
use App\Models\Tender;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users
        User::factory()->count(12)->create();
        Province::factory()->count(12)->create();
        City::factory()->count(12)->create();

 

  

        // Seed categories
        Category::factory()->count(12)->create();

      

  // Seed commodities
  Commodity::factory()->count(12)->create();
      // Seed auctions
      Auction::factory()->count(12)->create();
        // Seed tenders
        Tender::factory()->count(12)->create();
               // Seed agent expertises
               AgentExpertise::factory()->count(12)->create();
              // AgentInformation::factory()->count(12)->create();
    }
}
