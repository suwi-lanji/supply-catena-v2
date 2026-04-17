<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\LedgerAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
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

        $accounts = $this->getChartOfAccounts();

        foreach ($accounts as $account) {
            LedgerAccount::create([
                'team_id' => $company->id,
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'sub_type' => $account['sub_type'],
                'description' => $account['description'] ?? null,
                'opening_balance' => $account['opening_balance'] ?? 0,
                'current_balance' => $account['opening_balance'] ?? 0,
                'is_active' => true,
                'is_system' => $account['is_system'] ?? false,
            ]);
        }

        $this->command->info('Chart of Accounts created with ' . count($accounts) . ' accounts');
    }

    /**
     * Get comprehensive chart of accounts for mining supply company
     */
    protected function getChartOfAccounts(): array
    {
        return [
            // ==================== ASSETS ====================
            // Current Assets (1000-1999)
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => 'asset', 'sub_type' => 'cash', 'opening_balance' => 50000, 'is_system' => true],
            ['code' => '1010', 'name' => 'Petty Cash - Kitwe', 'type' => 'asset', 'sub_type' => 'cash', 'opening_balance' => 5000],
            ['code' => '1100', 'name' => 'ZANACO Current Account', 'type' => 'asset', 'sub_type' => 'bank', 'opening_balance' => 850000, 'is_system' => true],
            ['code' => '1110', 'name' => 'Stanbic Business Account', 'type' => 'asset', 'sub_type' => 'bank', 'opening_balance' => 425000],
            ['code' => '1120', 'name' => 'Standard Chartered USD Account', 'type' => 'asset', 'sub_type' => 'bank', 'opening_balance' => 150000],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'sub_type' => 'accounts_receivable', 'opening_balance' => 1250000, 'is_system' => true],
            ['code' => '1210', 'name' => 'Kansanshi Mining Plc', 'type' => 'asset', 'sub_type' => 'accounts_receivable', 'opening_balance' => 450000],
            ['code' => '1220', 'name' => 'Mopani Copper Mines', 'type' => 'asset', 'sub_type' => 'accounts_receivable', 'opening_balance' => 380000],
            ['code' => '1230', 'name' => 'Konkola Copper Mines', 'type' => 'asset', 'sub_type' => 'accounts_receivable', 'opening_balance' => 420000],
            ['code' => '1300', 'name' => 'Inventory', 'type' => 'asset', 'sub_type' => 'inventory', 'opening_balance' => 2750000, 'is_system' => true],
            ['code' => '1310', 'name' => 'Mining Equipment Inventory', 'type' => 'asset', 'sub_type' => 'inventory', 'opening_balance' => 1200000],
            ['code' => '1320', 'name' => 'Safety Equipment Inventory', 'type' => 'asset', 'sub_type' => 'inventory', 'opening_balance' => 450000],
            ['code' => '1330', 'name' => 'Spare Parts Inventory', 'type' => 'asset', 'sub_type' => 'inventory', 'opening_balance' => 650000],
            ['code' => '1340', 'name' => 'Consumables Inventory', 'type' => 'asset', 'sub_type' => 'inventory', 'opening_balance' => 450000],
            ['code' => '1400', 'name' => 'VAT Input', 'type' => 'asset', 'sub_type' => 'current_asset', 'opening_balance' => 125000],
            ['code' => '1500', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'sub_type' => 'current_asset', 'opening_balance' => 35000],
            ['code' => '1510', 'name' => 'Prepaid Insurance', 'type' => 'asset', 'sub_type' => 'current_asset', 'opening_balance' => 35000],

            // Fixed Assets (1600-1999)
            ['code' => '1600', 'name' => 'Fixed Assets', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => 0],
            ['code' => '1610', 'name' => 'Office Building', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => 2500000],
            ['code' => '1620', 'name' => 'Warehouse Building', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => 1800000],
            ['code' => '1630', 'name' => 'Delivery Vehicles', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => 850000],
            ['code' => '1640', 'name' => 'Office Equipment', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => 250000],
            ['code' => '1650', 'name' => 'Warehouse Equipment', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => 450000],
            ['code' => '1700', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'sub_type' => 'fixed_asset', 'opening_balance' => -650000],

            // ==================== LIABILITIES ====================
            // Current Liabilities (2000-2499)
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'sub_type' => 'accounts_payable', 'opening_balance' => 750000, 'is_system' => true],
            ['code' => '2010', 'name' => 'Sandvik Zambia', 'type' => 'liability', 'sub_type' => 'accounts_payable', 'opening_balance' => 280000],
            ['code' => '2020', 'name' => 'Atlas Copco Zambia', 'type' => 'liability', 'sub_type' => 'accounts_payable', 'opening_balance' => 220000],
            ['code' => '2030', 'name' => 'CAT Equipment Zambia', 'type' => 'liability', 'sub_type' => 'accounts_payable', 'opening_balance' => 250000],
            ['code' => '2100', 'name' => 'VAT Output', 'type' => 'liability', 'sub_type' => 'current_liability', 'opening_balance' => 185000],
            ['code' => '2200', 'name' => 'PAYE Payable', 'type' => 'liability', 'sub_type' => 'current_liability', 'opening_balance' => 45000],
            ['code' => '2210', 'name' => 'NAPSA Payable', 'type' => 'liability', 'sub_type' => 'current_liability', 'opening_balance' => 32000],
            ['code' => '2220', 'name' => 'NHIMA Payable', 'type' => 'liability', 'sub_type' => 'current_liability', 'opening_balance' => 18500],
            ['code' => '2300', 'name' => 'Accrued Expenses', 'type' => 'liability', 'sub_type' => 'current_liability', 'opening_balance' => 65000],
            ['code' => '2400', 'name' => 'Customer Deposits', 'type' => 'liability', 'sub_type' => 'current_liability', 'opening_balance' => 125000],

            // Long-term Liabilities (2500-2999)
            ['code' => '2500', 'name' => 'Bank Loan - ZANACO', 'type' => 'liability', 'sub_type' => 'long_term_liability', 'opening_balance' => 500000],
            ['code' => '2510', 'name' => 'Vehicle Finance - Stanbic', 'type' => 'liability', 'sub_type' => 'long_term_liability', 'opening_balance' => 350000],

            // ==================== EQUITY ====================
            ['code' => '3000', 'name' => "Owner's Capital", 'type' => 'equity', 'sub_type' => 'capital', 'opening_balance' => 4500000, 'is_system' => true],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'sub_type' => 'retained_earnings', 'opening_balance' => 2850000],
            ['code' => '3200', 'name' => "Owner's Drawings", 'type' => 'equity', 'sub_type' => 'drawings', 'opening_balance' => 0],
            ['code' => '3300', 'name' => 'Current Year Earnings', 'type' => 'equity', 'sub_type' => 'retained_earnings', 'opening_balance' => 0],

            // ==================== REVENUE ====================
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'revenue', 'sub_type' => 'sales', 'is_system' => true],
            ['code' => '4010', 'name' => 'Mining Equipment Sales', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4020', 'name' => 'Safety Equipment Sales', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4030', 'name' => 'Spare Parts Sales', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4040', 'name' => 'Consumables Sales', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4100', 'name' => 'Sales Returns and Allowances', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4200', 'name' => 'Sales Discounts', 'type' => 'revenue', 'sub_type' => 'discount_received'],
            ['code' => '4300', 'name' => 'Interest Income', 'type' => 'revenue', 'sub_type' => 'other_income'],
            ['code' => '4400', 'name' => 'Foreign Exchange Gain', 'type' => 'revenue', 'sub_type' => 'other_income'],
            ['code' => '4900', 'name' => 'Other Income', 'type' => 'revenue', 'sub_type' => 'other_income'],

            // ==================== EXPENSES ====================
            // Cost of Goods Sold (5000-5999)
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold', 'is_system' => true],
            ['code' => '5010', 'name' => 'COGS - Mining Equipment', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],
            ['code' => '5020', 'name' => 'COGS - Safety Equipment', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],
            ['code' => '5030', 'name' => 'COGS - Spare Parts', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],
            ['code' => '5040', 'name' => 'COGS - Consumables', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],
            ['code' => '5100', 'name' => 'Freight In', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],
            ['code' => '5200', 'name' => 'Import Duties', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],

            // Operating Expenses (6000-6999)
            ['code' => '6000', 'name' => 'Salaries and Wages', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6010', 'name' => 'PAYE Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6020', 'name' => 'NAPSA Contribution', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6030', 'name' => 'NHIMA Contribution', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6100', 'name' => 'Rent Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6200', 'name' => 'Utilities Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6210', 'name' => 'ZESCO Electricity', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6220', 'name' => 'Water Utility', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6300', 'name' => 'Office Supplies', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6400', 'name' => 'Depreciation Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6500', 'name' => 'Bad Debt Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6600', 'name' => 'Bank Charges', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6700', 'name' => 'Discount Allowed', 'type' => 'expense', 'sub_type' => 'discount_allowed'],
            ['code' => '6800', 'name' => 'Transportation Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6810', 'name' => 'Fuel and Oil', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6820', 'name' => 'Vehicle Maintenance', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6830', 'name' => 'Vehicle Insurance', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6900', 'name' => 'Professional Fees', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6910', 'name' => 'Audit Fees', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6920', 'name' => 'Legal Fees', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6930', 'name' => 'Consultancy Fees', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6940', 'name' => 'Insurance Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6950', 'name' => 'Communication Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6960', 'name' => 'Marketing and Advertising', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6970', 'name' => 'ZRA Levy', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6980', 'name' => 'Foreign Exchange Loss', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6990', 'name' => 'Miscellaneous Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
        ];
    }
}
