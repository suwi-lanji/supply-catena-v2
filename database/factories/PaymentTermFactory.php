<?php

namespace Database\Factories;

use App\Models\PaymentTerm;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTermFactory extends Factory
{
    protected $model = PaymentTerm::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->randomElement(['Net 15', 'Net 30', 'Net 45', 'Net 60']),
            'account_type' => $this->faker->randomElement(['Business', 'Personal']),
            'bank' => $this->faker->randomElement(['ZANACO', 'Standard Chartered', 'Barclays', 'Stanbic']),
            'account_name' => $this->faker->company(),
            'account_number' => $this->faker->numerify('############'),
            'branch' => $this->faker->city() . ' Branch',
            'swift_code' => $this->faker->regexify('[A-Z]{4}ZMLX'),
            'branch_number' => $this->faker->numerify('###'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
