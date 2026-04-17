<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Team;
use App\Models\PaymentTerm;
use App\Models\DeliveryMethod;
use App\Models\SalesPerson;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'customer_id' => Customer::factory(),
            'quotation_number' => 'QO-' . $this->faker->numerify('####-####'),
            'reference_number' => 'REF-' . $this->faker->numerify('####'),
            'quotation_date' => $this->faker->date(),
            'expected_shippment_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'payment_term_id' => PaymentTerm::factory(),
            'delivery_method_id' => DeliveryMethod::factory(),
            'sales_person_id' => SalesPerson::factory(),
            'items' => [],
            'sub_total' => $this->faker->randomFloat(2, 100, 10000),
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'discount' => 0,
            'adjustment' => 0,
            'shipment_charges' => 0,
            'status' => $this->faker->randomElement(['pending', 'sent', 'accepted', 'rejected']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
