<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->optional()->numerify('########'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
