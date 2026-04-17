<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\SalesPerson;
use Illuminate\Database\Seeder;

class SalesPersonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Team::where('name', 'Copperbelt Mining Supplies Ltd')->first();

        if (!$company) {
            $this->command->error('Company not found. Run DemoCompanySeeder first.');
            return;
        }

        $salesPersons = [
            [
                'name' => 'Ruth Phiri',
                'email' => 'salesrep1@copperbeltmining.com',
            ],
            [
                'name' => 'Steven Mulenga',
                'email' => 'salesrep2@copperbeltmining.com',
            ],
            [
                'name' => 'Peter Chisanga',
                'email' => 'sales@copperbeltmining.com',
            ],
            [
                'name' => 'Grace Ng\'andu',
                'email' => 'grace.ngandu@copperbeltmining.com',
            ],
        ];

        foreach ($salesPersons as $person) {
            SalesPerson::create(array_merge($person, ['team_id' => $company->id]));
        }

        $this->command->info('Sales Persons created: ' . count($salesPersons));
    }
}
