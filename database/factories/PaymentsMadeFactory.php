<?php

namespace Database\Factories;

use App\Models\PaymentsMade;
use App\Models\Vendor;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentsMadeFactory extends Factory
{
    protected $model = PaymentsMade::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'vendor_id' => Vendor::factory(),
            'payment_number' => 'PM-' . $this->faker->numerify('####-####'),
            'payment_date' => $this->faker->date(),
            'payment_made' => $this->faker->randomFloat(2, 100, 50000),
            'payment_mode' => $this->faker->randomElement(['Bank Transfer', 'Cash', 'Cheque', 'Card']),
            'paid_through' => $this->faker->randomElement(['ZANACO', 'Standard Chartered', 'Stanbic', 'Cash']),
            'reference_number' => $this->faker->numerify('REF-####'),
            'notes' => $this->faker->sentence(),
            'items' => [],
            'clear_applied_amount' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
