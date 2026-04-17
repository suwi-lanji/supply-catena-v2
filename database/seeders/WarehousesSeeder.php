<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehousesSeeder extends Seeder
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

        $warehouses = [
            [
                'name' => 'Main Warehouse - Kitwe',
                'attention' => 'Warehouse Manager',
                'street_1' => '2896 Bwana Mkubwa Road',
                'street_2' => 'Industrial Area',
                'city' => 'Kitwe',
                'province' => 'Copperbelt',
                'country' => 'Zambia',
                'phone' => '+260 212 456 789',
                'email' => 'warehouse@copperbeltmining.com',
                'is_primary' => true,
            ],
            [
                'name' => 'Equipment Depot - Ndola',
                'attention' => 'Depot Supervisor',
                'street_1' => '45 Industrial Road',
                'street_2' => 'Light Industrial Area',
                'city' => 'Ndola',
                'province' => 'Copperbelt',
                'country' => 'Zambia',
                'phone' => '+260 212 567 890',
                'email' => 'ndola.depot@copperbeltmining.com',
                'is_primary' => false,
            ],
            [
                'name' => 'Safety Equipment Store',
                'attention' => 'Safety Store Manager',
                'street_1' => '12 Safety Street',
                'street_2' => 'Central Business District',
                'city' => 'Kitwe',
                'province' => 'Copperbelt',
                'country' => 'Zambia',
                'phone' => '+260 212 678 901',
                'email' => 'safety.store@copperbeltmining.com',
                'is_primary' => false,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create(array_merge($warehouse, ['team_id' => $company->id]));
        }

        $this->command->info('Warehouses created: ' . count($warehouses));
    }
}
