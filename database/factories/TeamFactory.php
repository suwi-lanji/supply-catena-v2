<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'portal_name' => $this->faker->company(),
            'industry' => $this->faker->randomElement(['Retail', 'Manufacturing', 'Services', 'Technology']),
            'business_location' => $this->faker->city(),
            'street_1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'phone' => $this->faker->phoneNumber(),
            'logo' => $this->faker->imageUrl(200, 200, 'business', true, 'logo'),
            'email' => $this->faker->companyEmail(),
            'has_warehouses' => true,
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
