<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\PaymentTerm;
use App\Models\SalesPerson;
use App\Models\SalesOrder;
use App\Models\Quotation;
use App\Models\Invoices;
use App\Models\Bill;
use App\Models\PurchaseOrder;
use App\Models\PaymentsReceived;
use App\Models\PaymentsMade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionsSeeder extends Seeder
{
    protected $team;
    protected $admin;
    protected $paymentTerms;
    protected $salesPersons;
    protected $customers;
    protected $vendors;
    protected $items;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->team = Team::where('name', 'Copperbelt Mining Supplies Ltd')->first();

        if (!$this->team) {
            $this->command->error('Company not found. Run DemoCompanySeeder first.');
            return;
        }

        $this->admin = User::where('email', 'admin@copperbeltmining.com')->first();
        $this->paymentTerms = PaymentTerm::where('team_id', $this->team->id)->pluck('id', 'name');
        $this->salesPersons = SalesPerson::where('team_id', $this->team->id)->pluck('id', 'name');
        $this->customers = Customer::where('team_id', $this->team->id)->get()->keyBy('company_display_name');
        $this->vendors = Vendor::where('team_id', $this->team->id)->get()->keyBy('vendor_display_name');
        $this->items = Item::where('team_id', $this->team->id)->get()->keyBy('sku');

        // Create transactions in order
        $this->createQuotations();
        $this->createSalesOrders();
        $this->createInvoices();
        $this->createPurchaseOrders();
        $this->createBills();
        $this->createPaymentsReceived();
        $this->createPaymentsMade();

        $this->command->info('All transactions created successfully');
    }

    protected function createQuotations(): void
    {
        $quotations = [
            [
                'customer' => 'Kansanshi Mining Plc',
                'date' => Carbon::now()->subDays(30),
                'reference' => 'Q-2024-0001',
                'status' => 'accepted',
                'items' => [
                    ['sku' => 'SD-T38-366', 'quantity' => 100, 'rate' => 1850],
                    ['sku' => 'SD-BB76-T38', 'quantity' => 200, 'rate' => 850],
                ],
            ],
            [
                'customer' => 'Mopani Copper Mines',
                'date' => Carbon::now()->subDays(25),
                'reference' => 'Q-2024-0002',
                'status' => 'accepted',
                'items' => [
                    ['sku' => 'PPE-HH-001', 'quantity' => 200, 'rate' => 185],
                    ['sku' => 'KOE-LAMP-001', 'quantity' => 50, 'rate' => 2850],
                ],
            ],
            [
                'customer' => 'Lubambe Copper Mine',
                'date' => Carbon::now()->subDays(5),
                'reference' => 'Q-2024-0003',
                'status' => 'pending',
                'items' => [
                    ['sku' => 'WP-AH4X3', 'quantity' => 2, 'rate' => 185000],
                    ['sku' => 'WP-IMP-4X3', 'quantity' => 4, 'rate' => 28500],
                ],
            ],
        ];

        $count = 0;
        foreach ($quotations as $quoteData) {
            $customer = $this->customers[$quoteData['customer']] ?? null;
            if (!$customer) continue;

            $items = [];
            $subtotal = 0;

            foreach ($quoteData['items'] as $itemData) {
                $item = $this->items[$itemData['sku']] ?? null;
                if (!$item) continue;

                $amount = $itemData['quantity'] * $itemData['rate'];
                $items[] = [
                    'item' => $item->id,
                    'name' => $item->name,
                    'description' => $item->sales_description ?? '',
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            }

            Quotation::create([
                'team_id' => $this->team->id,
                'quotation_number' => $quoteData['reference'],
                'reference_number' => $quoteData['reference'],
                'customer_id' => $customer->id,
                'quotation_date' => $quoteData['date'],
                'expected_shippment_date' => Carbon::now()->addDays(14),
                'status' => $quoteData['status'],
                'items' => $items,
                'sub_total' => $subtotal,
                'total' => $subtotal,
                'discount' => 0,
                'adjustment' => 0,
                'shipment_charges' => 0,
                'terms_and_conditions' => [['terms_and_conditions' => 'Payment due within 30 days of invoice date.']],
                'payment_term_id' => $this->paymentTerms['Net 30'] ?? null,
            ]);
            $count++;
        }

        $this->command->info("Quotations created: {$count}");
    }

    protected function createSalesOrders(): void
    {
        $orders = [
            [
                'customer' => 'Kansanshi Mining Plc',
                'date' => Carbon::now()->subDays(28),
                'reference' => 'SO-2024-0001',
                'status' => 'delivered',
                'items' => [
                    ['sku' => 'SD-T38-366', 'quantity' => 100, 'rate' => 1850],
                    ['sku' => 'SD-BB76-T38', 'quantity' => 200, 'rate' => 850],
                ],
            ],
            [
                'customer' => 'Mopani Copper Mines',
                'date' => Carbon::now()->subDays(22),
                'reference' => 'SO-2024-0002',
                'status' => 'delivered',
                'items' => [
                    ['sku' => 'PPE-HH-001', 'quantity' => 200, 'rate' => 185],
                    ['sku' => 'KOE-LAMP-001', 'quantity' => 50, 'rate' => 2850],
                ],
            ],
            [
                'customer' => 'Konkola Copper Mines',
                'date' => Carbon::now()->subDays(10),
                'reference' => 'SO-2024-0003',
                'status' => 'confirmed',
                'items' => [
                    ['sku' => 'CAT-C15-OF', 'quantity' => 50, 'rate' => 1250],
                    ['sku' => 'CAT-AF-001', 'quantity' => 25, 'rate' => 2850],
                ],
            ],
            [
                'customer' => 'Chambishi Metals',
                'date' => Carbon::now()->subDays(7),
                'reference' => 'SO-2024-0004',
                'status' => 'confirmed',
                'items' => [
                    ['sku' => 'SH-RIM-R4', 'quantity' => 10, 'rate' => 12500],
                    ['sku' => 'SH-TEL-S2', 'quantity' => 5, 'rate' => 14500],
                ],
            ],
        ];

        $count = 0;
        foreach ($orders as $orderData) {
            $customer = $this->customers[$orderData['customer']] ?? null;
            if (!$customer) continue;

            $items = [];
            $subtotal = 0;

            foreach ($orderData['items'] as $itemData) {
                $item = $this->items[$itemData['sku']] ?? null;
                if (!$item) continue;

                $amount = $itemData['quantity'] * $itemData['rate'];
                $items[] = [
                    'item' => $item->id,
                    'name' => $item->name,
                    'description' => $item->sales_description ?? '',
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            }

            SalesOrder::create([
                'team_id' => $this->team->id,
                'sales_order_number' => $orderData['reference'],
                'customer_id' => $customer->id,
                'sales_order_date' => $orderData['date'],
                'status' => $orderData['status'],
                'items' => $items,
                'sub_total' => $subtotal,
                'total' => $subtotal,
                'adjustment' => 0,
                'shipment_charges' => 0,
                'discount' => 0,
                'terms_and_conditions' => [['terms_and_conditions' => 'Delivery within 14 days.']],
            ]);
            $count++;
        }

        $this->command->info("Sales Orders created: {$count}");
    }

    protected function createInvoices(): void
    {
        $invoices = [
            [
                'customer' => 'Kansanshi Mining Plc',
                'date' => Carbon::now()->subDays(25),
                'due_date' => Carbon::now()->addDays(5),
                'reference' => 'INV-2024-0001',
                'order_number' => 'SO-2024-0001',
                'type' => 'tax',
                'status' => 'partial',
                'items' => [
                    ['sku' => 'SD-T38-366', 'quantity' => 100, 'rate' => 1850, 'tax' => 16],
                    ['sku' => 'SD-BB76-T38', 'quantity' => 200, 'rate' => 850, 'tax' => 16],
                ],
            ],
            [
                'customer' => 'Mopani Copper Mines',
                'date' => Carbon::now()->subDays(18),
                'due_date' => Carbon::now()->addDays(12),
                'reference' => 'INV-2024-0002',
                'order_number' => 'SO-2024-0002',
                'type' => 'tax',
                'status' => 'sent',
                'items' => [
                    ['sku' => 'PPE-HH-001', 'quantity' => 200, 'rate' => 185, 'tax' => 16],
                    ['sku' => 'KOE-LAMP-001', 'quantity' => 50, 'rate' => 2850, 'tax' => 16],
                ],
            ],
            [
                'customer' => 'Konkola Copper Mines',
                'date' => Carbon::now()->subDays(12),
                'due_date' => Carbon::now()->addDays(18),
                'reference' => 'INV-2024-0003',
                'order_number' => 'SO-2024-0003',
                'type' => 'tax',
                'status' => 'sent',
                'items' => [
                    ['sku' => 'CAT-C15-OF', 'quantity' => 50, 'rate' => 1250, 'tax' => 16],
                    ['sku' => 'CAT-AF-001', 'quantity' => 25, 'rate' => 2850, 'tax' => 16],
                ],
            ],
            [
                'customer' => 'Chambishi Metals',
                'date' => Carbon::now()->subDays(8),
                'due_date' => Carbon::now()->addDays(22),
                'reference' => 'INV-2024-0004',
                'order_number' => 'SO-2024-0004',
                'type' => 'tax',
                'status' => 'sent',
                'items' => [
                    ['sku' => 'SH-RIM-R4', 'quantity' => 10, 'rate' => 12500, 'tax' => 16],
                    ['sku' => 'SH-TEL-S2', 'quantity' => 5, 'rate' => 14500, 'tax' => 16],
                ],
            ],
        ];

        $count = 0;
        foreach ($invoices as $invoiceData) {
            $customer = $this->customers[$invoiceData['customer']] ?? null;
            if (!$customer) continue;

            // Find the sales order
            $salesOrder = SalesOrder::where('sales_order_number', $invoiceData['order_number'])
                ->where('team_id', $this->team->id)
                ->first();

            $items = [];
            $subtotal = 0;
            $taxTotal = 0;

            foreach ($invoiceData['items'] as $itemData) {
                $item = $this->items[$itemData['sku']] ?? null;
                if (!$item) continue;

                $amount = $itemData['quantity'] * $itemData['rate'];
                $tax = ($itemData['tax'] / 100) * $amount;
                $items[] = [
                    'item' => $item->id,
                    'name' => $item->name,
                    'description' => $item->sales_description ?? '',
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'tax' => $itemData['tax'],
                    'amount' => $amount + $tax,
                ];
                $subtotal += $amount;
                $taxTotal += $tax;
            }

            $total = $subtotal + $taxTotal;

            Invoices::create([
                'team_id' => $this->team->id,
                'invoice_number' => $invoiceData['reference'],
                'customer_id' => $customer->id,
                'invoice_date' => $invoiceData['date'],
                'due_date' => $invoiceData['due_date'],
                'order_number' => $salesOrder?->id,
                'type' => $invoiceData['type'],
                'status' => $invoiceData['status'],
                'items' => $items,
                'sub_total' => $subtotal,
                'total' => $total,
                'balance_due' => $total,
                'payment_terms_id' => $this->paymentTerms['Net 30'] ?? null,
            ]);
            $count++;
        }

        $this->command->info("Invoices created: {$count}");
    }

    protected function createPurchaseOrders(): void
    {
        $orders = [
            [
                'vendor' => 'Sandvik Zambia',
                'date' => Carbon::now()->subDays(35),
                'reference' => 'PO-2024-0001',
                'status' => 'OPEN',
                'items' => [
                    ['sku' => 'SD-T38-366', 'quantity' => 200, 'rate' => 1200],
                    ['sku' => 'SD-BB76-T38', 'quantity' => 300, 'rate' => 520],
                ],
            ],
            [
                'vendor' => 'Safety Africa',
                'date' => Carbon::now()->subDays(30),
                'reference' => 'PO-2024-0002',
                'status' => 'OPEN',
                'items' => [
                    ['sku' => 'PPE-HH-001', 'quantity' => 500, 'rate' => 95],
                    ['sku' => 'PPE-BOOT-001', 'quantity' => 200, 'rate' => 780],
                ],
            ],
            [
                'vendor' => 'SKF Zambia',
                'date' => Carbon::now()->subDays(20),
                'reference' => 'PO-2024-0003',
                'status' => 'OPEN',
                'items' => [
                    ['sku' => 'SKF-22320', 'quantity' => 10, 'rate' => 11000],
                    ['sku' => 'SKF-6310', 'quantity' => 50, 'rate' => 650],
                ],
            ],
        ];

        $count = 0;
        foreach ($orders as $orderData) {
            $vendor = $this->vendors[$orderData['vendor']] ?? null;
            if (!$vendor) continue;

            $items = [];
            $subtotal = 0;

            foreach ($orderData['items'] as $itemData) {
                $item = $this->items[$itemData['sku']] ?? null;
                if (!$item) continue;

                $amount = $itemData['quantity'] * $itemData['rate'];
                $items[] = [
                    'item' => $item->id,
                    'name' => $item->name,
                    'description' => $item->purchases_description ?? '',
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            }

            PurchaseOrder::create([
                'team_id' => $this->team->id,
                'purchase_order_number' => $orderData['reference'],
                'reference_number' => $orderData['reference'],
                'vendor_id' => $vendor->id,
                'purchase_order_date' => $orderData['date'],
                'expected_delivery_date' => Carbon::now()->addDays(14),
                'order_status' => $orderData['status'],
                'items' => $items,
                'subtotal' => $subtotal,
                'sub_total' => $subtotal,
                'total' => $subtotal,
                'discount' => 0,
                'adjustment' => 0,
                'delivery_street' => '2896 Bwana Mkubwa Road',
                'delivery_city' => 'Kitwe',
                'delivery_province' => 'Copperbelt',
                'delivery_country' => 'Zambia',
                'delivery_phone' => '+260 212 456 789',
                'payment_terms' => 'Net 30',
                'shipment_preference' => 'Standard',
                'customer_notes' => '',
                'terms_and_conditions' => [],
                'received' => false,
                'billed' => false,
            ]);
            $count++;
        }

        $this->command->info("Purchase Orders created: {$count}");
    }

    protected function createBills(): void
    {
        $bills = [
            [
                'vendor' => 'Sandvik Zambia',
                'date' => Carbon::now()->subDays(30),
                'due_date' => Carbon::now()->subDays(15),
                'reference' => 'BILL-2024-0001',
                'po_reference' => 'PO-2024-0001',
                'vendor_invoice' => 'SV-INV-45678',
                'status' => 'paid',
                'items' => [
                    ['sku' => 'SD-T38-366', 'quantity' => 200, 'rate' => 1200],
                    ['sku' => 'SD-BB76-T38', 'quantity' => 300, 'rate' => 520],
                ],
            ],
            [
                'vendor' => 'Safety Africa',
                'date' => Carbon::now()->subDays(25),
                'due_date' => Carbon::now()->subDays(10),
                'reference' => 'BILL-2024-0002',
                'po_reference' => 'PO-2024-0002',
                'vendor_invoice' => 'SA-INV-12345',
                'status' => 'paid',
                'items' => [
                    ['sku' => 'PPE-HH-001', 'quantity' => 500, 'rate' => 95],
                    ['sku' => 'PPE-BOOT-001', 'quantity' => 200, 'rate' => 780],
                ],
            ],
            [
                'vendor' => 'CAT Equipment Zambia',
                'date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(20),
                'reference' => 'BILL-2024-0003',
                'po_reference' => null,
                'vendor_invoice' => 'CAT-INV-78901',
                'status' => 'open',
                'items' => [
                    ['sku' => 'CAT-C15-OF', 'quantity' => 100, 'rate' => 750],
                    ['sku' => 'CAT-AF-001', 'quantity' => 50, 'rate' => 1650],
                ],
            ],
        ];

        $count = 0;
        foreach ($bills as $billData) {
            $vendor = $this->vendors[$billData['vendor']] ?? null;
            if (!$vendor) continue;

            // Find purchase order if referenced
            $purchaseOrder = null;
            if ($billData['po_reference']) {
                $purchaseOrder = PurchaseOrder::where('purchase_order_number', $billData['po_reference'])
                    ->where('team_id', $this->team->id)
                    ->first();
            }

            $items = [];
            $subtotal = 0;

            foreach ($billData['items'] as $itemData) {
                $item = $this->items[$itemData['sku']] ?? null;
                if (!$item) continue;

                $amount = $itemData['quantity'] * $itemData['rate'];
                $items[] = [
                    'item' => $item->id,
                    'name' => $item->name,
                    'description' => $item->purchases_description ?? '',
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            }

            Bill::create([
                'team_id' => $this->team->id,
                'bill_number' => $billData['reference'],
                'order_number' => $purchaseOrder?->id ?? 1,
                'vendor_id' => $vendor->id,
                'bill_date' => $billData['date'],
                'due_date' => $billData['due_date'],
                'payment_terms' => 'Net 30',
                'status' => $billData['status'],
                'items' => $items,
                'sub_total' => $subtotal,
                'total' => $subtotal,
                'balance_due' => $billData['status'] === 'paid' ? 0 : $subtotal,
            ]);
            $count++;
        }

        $this->command->info("Bills created: {$count}");
    }

    protected function createPaymentsReceived(): void
    {
        $payments = [
            [
                'customer' => 'Kansanshi Mining Plc',
                'date' => Carbon::now()->subDays(10),
                'reference' => 'PAY-2024-0001',
                'amount' => 250000,
                'method' => 'Bank Transfer',
            ],
            [
                'customer' => 'Mopani Copper Mines',
                'date' => Carbon::now()->subDays(5),
                'reference' => 'PAY-2024-0002',
                'amount' => 100000,
                'method' => 'Bank Transfer',
            ],
        ];

        $count = 0;
        foreach ($payments as $paymentData) {
            $customer = $this->customers[$paymentData['customer']] ?? null;
            if (!$customer) continue;

            PaymentsReceived::create([
                'team_id' => $this->team->id,
                'payment_number' => $paymentData['reference'],
                'customer_id' => $customer->id,
                'payment_date' => $paymentData['date'],
                'amount_received' => $paymentData['amount'],
                'bank_charges' => 0,
                'payment_mode' => $paymentData['method'],
                'paid_through' => 'ZANACO',
                'reference_number' => $paymentData['reference'],
                'notes' => 'Payment received for invoices',
                'items' => [],
            ]);
            $count++;
        }

        $this->command->info("Payments Received created: {$count}");
    }

    protected function createPaymentsMade(): void
    {
        $payments = [
            [
                'vendor' => 'Sandvik Zambia',
                'date' => Carbon::now()->subDays(18),
                'reference' => 'PM-2024-0001',
                'amount' => 400000,
                'method' => 'Bank Transfer',
            ],
            [
                'vendor' => 'Safety Africa',
                'date' => Carbon::now()->subDays(12),
                'reference' => 'PM-2024-0002',
                'amount' => 200000,
                'method' => 'Bank Transfer',
            ],
        ];

        $count = 0;
        foreach ($payments as $paymentData) {
            $vendor = $this->vendors[$paymentData['vendor']] ?? null;
            if (!$vendor) continue;

            PaymentsMade::create([
                'team_id' => $this->team->id,
                'payment_number' => $paymentData['reference'],
                'vendor_id' => $vendor->id,
                'payment_date' => $paymentData['date'],
                'payment_made' => $paymentData['amount'],
                'payment_mode' => $paymentData['method'],
                'paid_through' => 'ZANACO',
                'clear_applied_amount' => false,
                'reference_number' => $paymentData['reference'],
                'notes' => 'Payment made for bills',
                'items' => [],
            ]);
            $count++;
        }

        $this->command->info("Payments Made created: {$count}");
    }
}
