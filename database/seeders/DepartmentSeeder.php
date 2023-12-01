<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        // Adjust the number of records you want to seed
        Department::factory()->count(4)->create();
    }
}
