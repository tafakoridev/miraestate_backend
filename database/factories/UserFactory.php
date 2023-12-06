<?php
namespace Database\Factories;

use App\Models\AgentInformation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $role = $this->faker->randomElement(['admin', 'agent', 'customer']);

        return [
            'name' => $this->faker->name,
            'phonenumber' => $this->faker->unique()->phoneNumber,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'national_code' => $this->faker->unique()->numerify('##########'),
            'role' => $role,
            'state' => $this->faker->randomElement(['enabled', 'disabled']),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            if ($user->role === 'agent') {
                AgentInformation::factory()->create([
                    'agent_id' => $user->id,
                    'rate' => rand(0, 100),
                    'profile_photo_url' => '/profileplaceholder.png'
                ]);
            }
        });
    }
}



