<?php

namespace Database\Factories;

use App\Models\SalesPerson;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesPersonFactory extends Factory
{
    protected $model = SalesPerson::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
