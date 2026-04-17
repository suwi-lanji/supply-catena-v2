<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Vendor;
use App\Models\Team;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'vendor_id' => Vendor::factory(),
            'bill_number' => 'BL-' . $this->faker->numerify('####-####'),
            'order_number' => PurchaseOrder::factory(),
            'bill_date' => $this->faker->date(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'payment_terms' => $this->faker->randomElement(['Net 15', 'Net 30', 'Net 45']),
            'subject' => $this->faker->sentence(),
            'items' => [],
            'sub_total' => $this->faker->randomFloat(2, 100, 50000),
            'total' => $this->faker->randomFloat(2, 100, 50000),
            'balance_due' => $this->faker->randomFloat(2, 0, 50000),
            'discount' => 0,
            'adjustment' => 0,
            'status' => $this->faker->randomElement(['open', 'partial', 'paid']),
            'notes' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
