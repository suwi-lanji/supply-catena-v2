<?php

namespace App\Services\Sales;

use App\Models\Invoices;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Team;
use App\Models\JournalEntry;
use App\Services\BaseService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\ChartOfAccountsService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class InvoiceService extends BaseService
{
    protected JournalEntryService $journalEntryService;
    protected ChartOfAccountsService $chartOfAccountsService;
    protected InventoryService $inventoryService;

    public function __construct(
        JournalEntryService $journalEntryService,
        ChartOfAccountsService $chartOfAccountsService,
        InventoryService $inventoryService
    ) {
        $this->journalEntryService = $journalEntryService;
        $this->chartOfAccountsService = $chartOfAccountsService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new invoice.
     *
     * @param Team $team
     * @param array $data
     * @return Invoices
     * @throws Exception
     */
    public function create(Team $team, array $data): Invoices
    {
        return $this->transaction(function () use ($team, $data) {
            // Validate customer exists
            $customer = Customer::findOrFail($data['customer_id']);
            if ($customer->team_id !== $team->id) {
                throw new Exception('Customer does not belong to this team.');
            }

            // Create the invoice
            $invoice = new Invoices();
            $invoice->team_id = $team->id;
            $invoice->customer_id = $data['customer_id'];
            $invoice->invoice_date = $data['invoice_date'] ?? now();
            $invoice->due_date = $data['due_date'] ?? now()->addDays(30);
            $invoice->status = Invoices::STATUS_DRAFT;
            $invoice->notes = $data['notes'] ?? null;
            $invoice->terms = $data['terms'] ?? null;

            // Calculate totals
            $this->calculateTotals($invoice, $data['items']);

            $invoice->save();

            // Create invoice items
            $this->createInvoiceItems($invoice, $data['items']);

            $this->logAction('invoice_created', [
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'total' => $invoice->total,
            ]);

            return $invoice;
        });
    }

    /**
     * Update an invoice.
     *
     * @param Invoices $invoice
     * @param array $data
     * @return Invoices
     * @throws Exception
     */
    public function update(Invoices $invoice, array $data): Invoices
    {
        if ($invoice->status !== Invoices::STATUS_DRAFT) {
            throw new Exception('Only draft invoices can be updated.');
        }

        return $this->transaction(function () use ($invoice, $data) {
            // Update basic fields
            if (isset($data['customer_id'])) {
                $invoice->customer_id = $data['customer_id'];
            }
            if (isset($data['invoice_date'])) {
                $invoice->invoice_date = $data['invoice_date'];
            }
            if (isset($data['due_date'])) {
                $invoice->due_date = $data['due_date'];
            }
            if (isset($data['notes'])) {
                $invoice->notes = $data['notes'];
            }
            if (isset($data['terms'])) {
                $invoice->terms = $data['terms'];
            }

            // Update items if provided
            if (isset($data['items'])) {
                $invoice->items()->delete();
                $this->createInvoiceItems($invoice, $data['items']);
            }

            // Recalculate totals
            $this->calculateTotals($invoice, $data['items'] ?? []);

            $invoice->save();

            $this->logAction('invoice_updated', [
                'invoice_id' => $invoice->id,
            ]);

            return $invoice;
        });
    }

    /**
     * Send/issue an invoice.
     *
     * @param Invoices $invoice
     * @param int $userId
     * @return Invoices
     * @throws Exception
     */
    public function send(Invoices $invoice, int $userId): Invoices
    {
        if ($invoice->status !== Invoices::STATUS_DRAFT) {
            throw new Exception('Only draft invoices can be sent.');
        }

        return $this->transaction(function () use ($invoice, $userId) {
            $invoice->status = Invoices::STATUS_SENT;
            $invoice->balance_due = $invoice->total;
            $invoice->save();

            // Create journal entry for the invoice
            $this->createInvoiceJournalEntry($invoice, $invoice->team, $userId);

            // Decrement inventory for each item
            foreach ($invoice->items as $item) {
                if ($item->item_id) {
                    $this->inventoryService->decrementStock(
                        $item->item,
                        $item->quantity,
                        'invoice',
                        $invoice->id,
                        "Invoice #{$invoice->invoice_number}"
                    );
                }
            }

            $this->logAction('invoice_sent', [
                'invoice_id' => $invoice->id,
                'sent_by' => $userId,
            ]);

            return $invoice;
        });
    }

    /**
     * Apply a payment to an invoice.
     *
     * @param Invoices $invoice
     * @param float $amount
     * @param int $paymentId
     * @return Invoices
     */
    public function applyPayment(Invoices $invoice, float $amount, int $paymentId): Invoices
    {
        return $this->transaction(function () use ($invoice, $amount, $paymentId) {
            $invoice->balance_due = max(0, $invoice->balance_due - $amount);

            if ($invoice->balance_due <= 0) {
                $invoice->status = Invoices::STATUS_PAID;
            } elseif ($amount > 0) {
                $invoice->status = Invoices::STATUS_PARTIAL;
            }

            $invoice->save();

            $this->logAction('invoice_payment_applied', [
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_id' => $paymentId,
            ]);

            return $invoice;
        });
    }

    /**
     * Cancel an invoice.
     *
     * @param Invoices $invoice
     * @param int $userId
     * @param string $reason
     * @return Invoices
     * @throws Exception
     */
    public function cancel(Invoices $invoice, int $userId, string $reason = ''): Invoices
    {
        if (in_array($invoice->status, [Invoices::STATUS_PAID, Invoices::STATUS_CANCELLED])) {
            throw new Exception('Cannot cancel this invoice.');
        }

        return $this->transaction(function () use ($invoice, $userId, $reason) {
            // Reverse inventory if invoice was sent
            if ($invoice->status === Invoices::STATUS_SENT || $invoice->status === Invoices::STATUS_PARTIAL) {
                foreach ($invoice->items as $item) {
                    if ($item->item_id) {
                        $this->inventoryService->incrementStock(
                            $item->item,
                            $item->quantity,
                            'invoice_cancellation',
                            $invoice->id,
                            "Cancelled Invoice #{$invoice->invoice_number}"
                        );
                    }
                }

                // Void the journal entry
                $journalEntry = $invoice->journalEntries()->first();
                if ($journalEntry) {
                    $this->journalEntryService->void($journalEntry, $userId, $reason);
                }
            }

            $invoice->status = Invoices::STATUS_CANCELLED;
            $invoice->save();

            $this->logAction('invoice_cancelled', [
                'invoice_id' => $invoice->id,
                'cancelled_by' => $userId,
                'reason' => $reason,
            ]);

            return $invoice;
        });
    }

    /**
     * Calculate totals for an invoice.
     *
     * @param Invoices $invoice
     * @param array $items
     * @return void
     */
    protected function calculateTotals(Invoices $invoice, array $items): void
    {
        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * $item['rate'];
            $subtotal += $lineTotal;

            if (isset($item['tax_rate']) && $item['tax_rate'] > 0) {
                $taxTotal += $lineTotal * ($item['tax_rate'] / 100);
            }

            if (isset($item['discount']) && $item['discount'] > 0) {
                $discountTotal += $item['discount'];
            }
        }

        $invoice->subtotal = $subtotal;
        $invoice->tax = $taxTotal;
        $invoice->discount = $discountTotal;
        $invoice->total = $subtotal + $taxTotal - $discountTotal;
    }

    /**
     * Create invoice items.
     *
     * @param Invoices $invoice
     * @param array $items
     * @return void
     */
    protected function createInvoiceItems(Invoices $invoice, array $items): void
    {
        foreach ($items as $itemData) {
            $invoice->items()->create([
                'item_id' => $itemData['item_id'] ?? null,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'rate' => $itemData['rate'],
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'discount' => $itemData['discount'] ?? 0,
                'total' => $itemData['quantity'] * $itemData['rate'],
            ]);
        }
    }

    /**
     * Create journal entry for an invoice.
     *
     * @param Invoices $invoice
     * @param Team $team
     * @param int $userId
     * @return JournalEntry
     */
    protected function createInvoiceJournalEntry(Invoices $invoice, Team $team, int $userId): JournalEntry
    {
        // Get accounts
        $accountsReceivable = $this->chartOfAccountsService->getAccountsReceivable($team);
        $salesRevenue = $this->chartOfAccountsService->getSalesRevenue($team);
        $salesTaxPayable = $this->chartOfAccountsService->getDefaultAccount($team, 'sales_tax_payable');
        $inventory = $this->chartOfAccountsService->getInventory($team);
        $cogs = $this->chartOfAccountsService->getCostOfGoodsSold($team);

        if (!$accountsReceivable || !$salesRevenue) {
            throw new Exception('Required accounts not found. Please set up chart of accounts.');
        }

        $lines = [];

        // Debit Accounts Receivable
        $lines[] = [
            'ledger_account_id' => $accountsReceivable->id,
            'type' => 'debit',
            'amount' => $invoice->total,
            'description' => "Invoice #{$invoice->invoice_number} - {$invoice->customer->name}",
        ];

        // Credit Sales Revenue
        $lines[] = [
            'ledger_account_id' => $salesRevenue->id,
            'type' => 'credit',
            'amount' => $invoice->subtotal,
            'description' => "Invoice #{$invoice->invoice_number}",
        ];

        // Credit Sales Tax Payable (if applicable)
        if ($invoice->tax > 0 && $salesTaxPayable) {
            $lines[] = [
                'ledger_account_id' => $salesTaxPayable->id,
                'type' => 'credit',
                'amount' => $invoice->tax,
                'description' => "Sales Tax - Invoice #{$invoice->invoice_number}",
            ];
        }

        // If tracking inventory, add COGS entry
        if ($inventory && $cogs) {
            $costOfGoods = $this->calculateCostOfGoods($invoice);
            if ($costOfGoods > 0) {
                // Debit COGS
                $lines[] = [
                    'ledger_account_id' => $cogs->id,
                    'type' => 'debit',
                    'amount' => $costOfGoods,
                    'description' => "COGS - Invoice #{$invoice->invoice_number}",
                ];

                // Credit Inventory
                $lines[] = [
                    'ledger_account_id' => $inventory->id,
                    'type' => 'credit',
                    'amount' => $costOfGoods,
                    'description' => "Inventory - Invoice #{$invoice->invoice_number}",
                ];
            }
        }

        return $this->journalEntryService->createAndPost($team, [
            'entry_date' => $invoice->invoice_date,
            'description' => "Invoice #{$invoice->invoice_number}",
            'reference_type' => get_class($invoice),
            'reference_id' => $invoice->id,
            'user_id' => $userId,
            'lines' => $lines,
        ], $userId);
    }

    /**
     * Calculate cost of goods sold for an invoice.
     *
     * @param Invoices $invoice
     * @return float
     */
    protected function calculateCostOfGoods(Invoices $invoice): float
    {
        $total = 0;

        foreach ($invoice->items as $item) {
            if ($item->item && $item->item->cost_price) {
                $total += $item->quantity * $item->item->cost_price;
            }
        }

        return $total;
    }

    /**
     * Get invoices for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getCustomerInvoices(Customer $customer): Collection
    {
        return Invoices::where('customer_id', $customer->id)
            ->orderBy('invoice_date', 'desc')
            ->get();
    }

    /**
     * Get outstanding invoices for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getOutstandingInvoices(Customer $customer): Collection
    {
        return Invoices::where('customer_id', $customer->id)
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->get();
    }
}
