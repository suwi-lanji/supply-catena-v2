<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Bill;
use App\Models\Brand;
use App\Models\CreditNotes;
use App\Models\Customer;
use App\Models\DatabaseNotification;
use App\Models\DeliveryMethod;
use App\Models\Expense;
use App\Models\InventoryAdjustment;
use App\Models\Invoices;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemsPurchased;
use App\Models\ItemsSold;
use App\Models\Manufacturer;
use App\Models\Notification;
use App\Models\Packages;
use App\Models\PaymentTerm;
use App\Models\PaymentsMade;
use App\Models\PaymentsReceived;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderShipment;
use App\Models\PurchaseReceives;
use App\Models\PurchasesAccount;
use App\Models\SalesAccount;
use App\Models\SalesOrder;
use App\Models\SalesReceipt;
use App\Models\SalesReturns;
use App\Models\SalesPerson;
use App\Models\Setting;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Template;
use App\Models\TransferOrder;
use App\Models\Vendor;
use App\Models\VendorCredit;
use App\Models\Warehouse;
use App\Models\Quotation;
use App\Models\Shipments;
use App\Models\User;

class MigrateDataFromSQLiteToPostgreSQL extends Command
{
    protected $signature = 'db:migrate-sqlite-to-postgres';
    protected $description = 'Migrate data from SQLite to PostgreSQL';

    public function handle()
    {
        // Get the list of tables from SQLite
        $tableOrder = [
            // Core Entities
            'users', 'teams', 'team_user', 'team_admin',

            // Roles and Permissions
            'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions',
            'vendors', 'vendor_credits', 'customers',
            // Supporting Tables
            'bills', 'brands', 'delivery_methods', 'failed_jobs', 'job_batches', 'jobs', 'manufucturers',
            'password_reset_tokens', 'payment_terms','sales_accounts', 'sales_persons', 'purchases_accounts',
            'cache', 'cache_locks', 'sessions',
            // Inventory & Items
            'warehouses', 'item', 'warehouse_items', 'item_groups', 'item_group_item',  'inventory_adjustments', 'transfer_orders',

            // Vendors and Customers


            // Sales and Purchases
            'sales_orders', 'sales_receipts', 'sales_returns',
            'quotations', 'packages', 'invoices', 'credit_notes','payments_receiveds', 'payments_mades',
            'purchase_orders', 'purchase_order_shipments', 'purchase_receives',

            // Expenses and Shipments
            'expenses', 'shipments',


        ];
        foreach ($tableOrder as $tableName) {

            if ($tableName == "sqlite_sequence") {
                continue;
            }

            // Fetch all records from the SQLite table
            $data = DB::connection('sqlite')->table($tableName)->get();

            // Migrate data based on the model associated with the table
            switch ($tableName) {
                case 'bills':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Bill::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'brands':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Brand::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'credit_notes':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        CreditNotes::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'customers':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Customer::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'database_notifications':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        DatabaseNotification::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'delivery_methods':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        DeliveryMethod::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'expenses':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Expense::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'inventory_adjustments':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        InventoryAdjustment::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'invoices':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Invoices::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'item':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Item::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'item_groups':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        ItemGroup::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'items_purchased':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        ItemsPurchased::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'items_sold':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        ItemsSold::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'manufacturers':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Manufacturer::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'notifications':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Notification::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'packages':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Packages::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'payment_terms':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PaymentTerm::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'payments_mades':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PaymentsMade::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'payments_receiveds':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PaymentsReceived::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'purchase_orders':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PurchaseOrder::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'purchase_order_shipments':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PurchaseOrderShipment::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'purchase_receives':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PurchaseReceives::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'purchases_accounts':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        PurchasesAccount::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'sales_accounts':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        SalesAccount::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'sales_orders':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        SalesOrder::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'sales_receipts':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        SalesReceipt::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'sales_returns':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        SalesReturns::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'sales_people':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        SalesPerson::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'settings':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Setting::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'teams':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Team::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'teams_users':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        TeamUser::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'templates':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Template::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'transfer_orders':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        TransferOrder::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'vendor_credits':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        VendorCredit::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'vendors':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Vendor::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'warehouses':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        unset($record['id']);
                        Warehouse::firstOrCreate(['name' => $record["name"], 'team_id' => $record['team_id']], (array) $record);
                    }
                    break;

                case 'quotations':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Quotation::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'shipments':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        Shipments::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;

                case 'users':
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        User::firstOrCreate(['id' => $record["id"]], (array) $record);
                    }
                    break;
                case 'warehouse_items':
                    $this->info("Hello World");
                    break;
                // Add any additional models here as necessary

                default:
                    // Use the DB facade for tables without models
                    foreach ($data as $record) {
                        $record = $this->processRecord((array) $record);
                        DB::table($tableName)->insert((array) $record);
                    }
                    break;
            }

            // Output message
            $this->info("Migrated data for table: {$tableName}");
        }


        $this->info('Data migration completed.');
    }
    protected function processRecord(array $record): array
    {
        foreach ($record as $key => $value) {
            // Check if the value is a string and JSON-decodable
            if (is_string($value) && $this->isJson($value)) {
                $record[$key] = json_decode($value, true); // Decode JSON into array
            }
        }

        return $record;
    }
    /**
     * Check if a given string is JSON-decodable.
     *
     * @param string $string
     * @return bool
     */
    protected function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
