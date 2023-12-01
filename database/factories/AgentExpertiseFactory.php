<?php

namespace Database\Factories;

use App\Models\AgentExpertise;
use App\Models\User;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentExpertiseFactory extends Factory
{
    protected $model = AgentExpertise::class;

    public function definition()
    {
        $agentIds = User::where('role', 'agent')->pluck('id')->all();
        $fieldType = $this->faker->randomElement(['App\Models\Category', 'App\Models\Department']);

        if ($fieldType === 'App\Models\Category') {
            $fieldIds = Category::pluck('id')->all();
        } else {
            $fieldIds = Department::pluck('id')->all();
        }

        return [
            'expertiese_id' => $agentIds[$this->faker->numberBetween(0, count($agentIds) - 1)],
            'field_id' => $fieldIds[$this->faker->numberBetween(0, count($fieldIds) - 1)],
            'field_type' => $fieldType,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
