<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ybazli\Faker\Facades\Faker;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
      
        return [
            'title' => Faker::word(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
