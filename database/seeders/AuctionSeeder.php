<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Auction;

class AuctionSeeder extends Seeder
{
    public function run()
    {
        Auction::factory()->count(10)->create();
    }
}
