<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->randomElement(['Main Warehouse', 'Secondary Warehouse', 'Distribution Center']) . ' ' . $this->faker->city(),
            'location' => $this->faker->address(),
            'is_default' => $this->faker->boolean(20),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
