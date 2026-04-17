<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'vendor_id' => Vendor::factory(),
            'purchase_order_number' => 'PO-' . $this->faker->numerify('####-####'),
            'reference_number' => 'PO-' . $this->faker->numerify('####-####'),
            'purchase_order_date' => $this->faker->date(),
            'expected_delivery_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'order_status' => $this->faker->randomElement(['OPEN', 'CLOSED', 'CANCELLED']),
            'items' => [],
            'sub_total' => $this->faker->randomFloat(2, 100, 50000),
            'total' => $this->faker->randomFloat(2, 100, 50000),
            'discount' => 0,
            'adjustment' => 0,
            'delivery_street' => $this->faker->streetAddress(),
            'delivery_city' => $this->faker->city(),
            'delivery_province' => $this->faker->state(),
            'delivery_country' => $this->faker->country(),
            'delivery_phone' => $this->faker->phoneNumber(),
            'payment_terms' => $this->faker->randomElement(['Net 15', 'Net 30', 'Net 45']),
            'shipment_preference' => $this->faker->randomElement(['Standard', 'Express']),
            'customer_notes' => $this->faker->sentence(),
            'terms_and_conditions' => [],
            'received' => $this->faker->boolean(),
            'billed' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
