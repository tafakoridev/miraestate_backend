<?php

namespace Database\Factories;

use App\Models\Commodity;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Ybazli\Faker\Facades\Faker;

class CommodityFactory extends Factory
{
    protected $model = Commodity::class;

    public function randomImage()
    {
        $imageUrl = 'https://picsum.photos/600';

        // Download image using curl
        $imageData = file_get_contents($imageUrl);

        // Generate a unique filename
        $filename = 'commodity_picture_' . uniqid() . '.jpg';
        // Save the image to the storage directory
        Storage::disk('public')->put('commodity_pictures/' . $filename, $imageData);

        // Return the filename with extension
        return '/storage/commodity_pictures/'.$filename;
    }
    public function definition()
    {
        $categoriesIds = Category::pluck('id')->all();
        $citiesIds = City::pluck('id')->all();
        $usersIds = User::pluck('id')->all();
        $agentIds = User::where('role', 'agent')->pluck('id')->all();

        return [
            'category_id' => $categoriesIds[$this->faker->numberBetween(0, count($categoriesIds) - 1)],
            'title' => Faker::sentence(),
            'description' => Faker::paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 500), // Adjust the price range as needed
            'city_id' => 1,//$citiesIds[$this->faker->numberBetween(0, count($citiesIds) - 1)],
            'picture' => $this->faker->imageUrl(),//$this->faker->imageUrl(), // Replace with logic to generate or store images
            'agent_id' => $agentIds[$this->faker->numberBetween(0, count($agentIds) - 1)],
            'user_id' => $usersIds[$this->faker->numberBetween(0, count($usersIds) - 1)],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
