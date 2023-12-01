<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ybazli\Faker\Facades\Faker;

class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    public function definition()
    {

        return [
            'name' => Faker::word(),
            'en_name' => $this->faker->word,
            'area_code' => $this->faker->randomNumber(2),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'approved' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
