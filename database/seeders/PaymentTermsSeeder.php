<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentTermsSeeder extends Seeder
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

        $terms = [
            [
                'name' => 'Due on Receipt',
                'account_type' => 'Current',
                'bank' => 'ZANACO',
                'account_name' => 'Copperbelt Mining Supplies Ltd',
                'account_number' => '0123456789012',
                'branch' => 'Kitwe Main Branch',
                'swift_code' => 'ZABOZMLX',
                'branch_number' => '001',
            ],
            [
                'name' => 'Net 15',
                'account_type' => 'Current',
                'bank' => 'ZANACO',
                'account_name' => 'Copperbelt Mining Supplies Ltd',
                'account_number' => '0123456789012',
                'branch' => 'Kitwe Main Branch',
                'swift_code' => 'ZABOZMLX',
                'branch_number' => '001',
            ],
            [
                'name' => 'Net 30',
                'account_type' => 'Current',
                'bank' => 'Stanbic Bank',
                'account_name' => 'Copperbelt Mining Supplies Ltd',
                'account_number' => '0140001234567',
                'branch' => 'Kitwe Branch',
                'swift_code' => 'SBICZMLX',
                'branch_number' => '002',
            ],
            [
                'name' => 'Net 45',
                'account_type' => 'Current',
                'bank' => 'Stanbic Bank',
                'account_name' => 'Copperbelt Mining Supplies Ltd',
                'account_number' => '0140001234567',
                'branch' => 'Kitwe Branch',
                'swift_code' => 'SBICZMLX',
                'branch_number' => '002',
            ],
            [
                'name' => 'Net 60',
                'account_type' => 'Current',
                'bank' => 'Standard Chartered',
                'account_name' => 'Copperbelt Mining Supplies Ltd',
                'account_number' => '0100123456789',
                'branch' => 'Kitwe Branch',
                'swift_code' => 'SCBLZMLX',
                'branch_number' => '003',
            ],
            [
                'name' => 'USD Account',
                'account_type' => 'Foreign Currency',
                'bank' => 'Standard Chartered',
                'account_name' => 'Copperbelt Mining Supplies Ltd',
                'account_number' => '0100123456790',
                'branch' => 'Kitwe Branch',
                'swift_code' => 'SCBLZMLX',
                'branch_number' => '003',
            ],
        ];

        foreach ($terms as $term) {
            PaymentTerm::create(array_merge($term, ['team_id' => $company->id]));
        }

        $this->command->info('Payment Terms created: ' . count($terms));
    }
}
