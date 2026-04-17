<?php

namespace Database\Factories;

use App\Models\Invoices;
use App\Models\Customer;
use App\Models\Team;
use App\Models\PaymentTerm;
use App\Models\SalesOrder;
use App\Models\SalesPerson;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoicesFactory extends Factory
{
    protected $model = Invoices::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV-' . $this->faker->numerify('####-####'),
            'type' => $this->faker->randomElement(['tax', 'proforma']),
            'order_number' => SalesOrder::factory(),
            'invoice_date' => $this->faker->date(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'payment_terms_id' => PaymentTerm::factory(),
            'sales_person_id' => SalesPerson::factory(),
            'items' => [],
            'sub_total' => $this->faker->randomFloat(2, 100, 50000),
            'total' => $this->faker->randomFloat(2, 100, 50000),
            'balance_due' => $this->faker->randomFloat(2, 0, 50000),
            'discount' => 0,
            'adjustment' => 0,
            'status' => $this->faker->randomElement(['sent', 'partial', 'paid', 'overdue']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
