<?php

namespace Database\Factories;

use App\Models\DeliveryMethod;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryMethodFactory extends Factory
{
    protected $model = DeliveryMethod::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->randomElement(['Standard', 'Express', 'Same Day', 'Next Day']),
            'description' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
