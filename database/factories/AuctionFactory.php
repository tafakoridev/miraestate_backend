<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ybazli\Faker\Facades\Faker;

class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition()
    {
        $usersIds = User::pluck('id')->all();
        $agentIds = User::where('role', 'agent')->pluck('id')->all();
        $departmentIds = Department::pluck('id')->all();
        return [
            'user_id' => $usersIds[$this->faker->numberBetween(0, count($usersIds) - 1)], // Assuming your user_id ranges from 1 to 100
            'department_id' => $departmentIds[$this->faker->numberBetween(0, count($departmentIds) - 1)], // Assuming your department_id ranges from 1 to 10
            'agent_id' => $agentIds[$this->faker->numberBetween(0, count($agentIds) - 1)], // Assuming your agent_id ranges from 1 to 50
            'title' => Faker::sentence(),
            'description' => Faker::paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
