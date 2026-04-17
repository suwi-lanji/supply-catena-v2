<?php

namespace Database\Factories;

use App\Models\PaymentsReceived;
use App\Models\Customer;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentsReceivedFactory extends Factory
{
    protected $model = PaymentsReceived::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'customer_id' => Customer::factory(),
            'payment_number' => 'PAY-' . $this->faker->numerify('####-####'),
            'payment_date' => $this->faker->date(),
            'amount_received' => $this->faker->randomFloat(2, 100, 100000),
            'bank_charges' => $this->faker->randomFloat(2, 0, 50),
            'payment_mode' => $this->faker->randomElement(['Bank Transfer', 'Cash', 'Cheque', 'Card']),
            'paid_through' => $this->faker->randomElement(['ZANACO', 'Standard Chartered', 'Stanbic', 'Cash']),
            'reference_number' => $this->faker->numerify('REF-####'),
            'notes' => $this->faker->sentence(),
            'items' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
