<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  // In your UserSeeder.php

public function run()
{
    \App\Models\User::factory()->count(50)->create();
}

}
