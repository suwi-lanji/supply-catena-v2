<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'customer_id' => Customer::factory(),
            'sales_order_number' => 'SO-' . $this->faker->numerify('####-####'),
            'sales_order_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['confirmed', 'processing', 'shipped', 'delivered']),
            'items' => [],
            'sub_total' => $this->faker->randomFloat(2, 100, 50000),
            'total' => $this->faker->randomFloat(2, 100, 50000),
            'discount' => 0,
            'adjustment' => 0,
            'shipment_charges' => $this->faker->randomFloat(2, 0, 500),
            'terms_and_conditions' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
