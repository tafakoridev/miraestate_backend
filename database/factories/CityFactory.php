<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ybazli\Faker\Facades\Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provinceIds = Province::pluck('id')->all();
        return [
            'name' => Faker::word(),
            'en_name' => $this->faker->word,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'approved' => $this->faker->boolean,
            'province_id' => $provinceIds[$this->faker->numberBetween(0, count($provinceIds) - 1)],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
