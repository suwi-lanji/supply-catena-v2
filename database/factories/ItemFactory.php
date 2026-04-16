<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'item_type' => 'goods',
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->ean13(),
            'unit' => 'each',
            'selling_price' => $this->faker->randomFloat(2, 10, 1000),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'track_inventory_for_this_item' => true,
            'opening_stock' => $this->faker->numberBetween(0, 1000),
            'reorder_level' => $this->faker->numberBetween(5, 50),
            'returnable_item' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
