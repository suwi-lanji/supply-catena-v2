<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder creates a comprehensive demo for Copperbelt Mining Supplies Ltd,
     * a mining supply company in Zambia's Copperbelt Province.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('=========================================');
        $this->command->info('  SUPPLY CATENA DEMO DATA SEEDER');
        $this->command->info('  Mining Supply Company Demo');
        $this->command->info('=========================================');
        $this->command->info('');

        // Step 1: Create company and users
        $this->command->info('Step 1: Creating company and users...');
        $this->call(DemoCompanySeeder::class);

        // Step 2: Create chart of accounts
        $this->command->info('');
        $this->command->info('Step 2: Creating Chart of Accounts...');
        $this->call(ChartOfAccountsSeeder::class);

        // Step 3: Create customers
        $this->command->info('');
        $this->command->info('Step 3: Creating customers...');
        $this->call(CustomersSeeder::class);

        // Step 4: Create vendors
        $this->command->info('');
        $this->command->info('Step 4: Creating vendors...');
        $this->call(VendorsSeeder::class);

        // Step 5: Create items/inventory
        $this->command->info('');
        $this->command->info('Step 5: Creating items and inventory...');
        $this->call(ItemsSeeder::class);

        // Step 6: Create warehouses
        $this->command->info('');
        $this->command->info('Step 6: Creating warehouses...');
        $this->call(WarehousesSeeder::class);

        // Step 7: Create payment terms
        $this->command->info('');
        $this->command->info('Step 7: Creating payment terms...');
        $this->call(PaymentTermsSeeder::class);

        // Step 8: Create sales persons
        $this->command->info('');
        $this->command->info('Step 8: Creating sales persons...');
        $this->call(SalesPersonsSeeder::class);

        // Step 9: Create transactions
        $this->command->info('');
        $this->command->info('Step 9: Creating transactions...');
        $this->call(TransactionsSeeder::class);

        $this->command->info('');
        $this->command->info('=========================================');
        $this->command->info('  DEMO DATA SEEDING COMPLETE!');
        $this->command->info('=========================================');
        $this->command->info('');
        $this->command->info('Demo Company: Copperbelt Mining Supplies Ltd');
        $this->command->info('');
        $this->command->info('Test Users:');
        $this->command->info('  - admin@copperbeltmining.com (password)');
        $this->command->info('  - accountant@copperbeltmining.com (password)');
        $this->command->info('  - sales@copperbeltmining.com (password)');
        $this->command->info('  - purchasing@copperbeltmining.com (password)');
        $this->command->info('  - warehouse@copperbeltmining.com (password)');
        $this->command->info('  - salesrep1@copperbeltmining.com (password)');
        $this->command->info('  - salesrep2@copperbeltmining.com (password)');
        $this->command->info('');
        $this->command->info('Data Created:');
        $this->command->info('  - 1 Company (Team)');
        $this->command->info('  - 7 Users with different roles');
        $this->command->info('  - 60+ Chart of Accounts');
        $this->command->info('  - 13 Customers (Mining companies)');
        $this->command->info('  - 30 Vendors (Suppliers)');
        $this->command->info('  - 35+ Items (Mining supplies)');
        $this->command->info('  - 3 Warehouses');
        $this->command->info('  - 6 Payment Terms');
        $this->command->info('  - 4 Sales Persons');
        $this->command->info('  - Quotations, Sales Orders, Invoices');
        $this->command->info('  - Purchase Orders, Bills');
        $this->command->info('  - Payments Received & Made');
        $this->command->info('');
    }
}
