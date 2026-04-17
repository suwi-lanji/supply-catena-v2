<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Team;
use App\Models\PaymentTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'customer_type' => 'business',
            'salutation' => $this->faker->randomElement(['Mr', 'Mrs', 'Ms', 'Dr']),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => $this->faker->company(),
            'company_display_name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'payment_terms' => 'Net 30',
            'billing_country' => $this->faker->country(),
            'billing_province' => $this->faker->state(),
            'billing_city' => $this->faker->city(),
            'billing_phone' => $this->faker->phoneNumber(),
            'billing_street_1' => $this->faker->streetAddress(),
            'billing_street_2' => '',
            'shipping_country' => $this->faker->country(),
            'shipping_province' => $this->faker->state(),
            'shipping_city' => $this->faker->city(),
            'shipping_phone' => $this->faker->phoneNumber(),
            'shipping_street_1' => $this->faker->streetAddress(),
            'shipping_street_2' => '',
            'remarks' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
