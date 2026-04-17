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

            // Calculate totals from items
            $calculations = $this->calculateTotals($data['items'] ?? [], $data['discount'] ?? 0, $data['adjustment'] ?? 0);

            // Create the invoice
            $invoice = new Invoices();
            $invoice->team_id = $team->id;
            $invoice->customer_id = $data['customer_id'];
            $invoice->invoice_number = $data['invoice_number'] ?? $this->generateInvoiceNumber($team, $data['type'] ?? 'tax');
            $invoice->type = $data['type'] ?? 'tax';
            $invoice->invoice_date = $data['invoice_date'] ?? now();
            $invoice->due_date = $data['due_date'] ?? now()->addDays(30);
            $invoice->order_number = $data['order_number'] ?? null;
            $invoice->payment_terms_id = $data['payment_terms_id'] ?? null;
            $invoice->sales_person_id = $data['sales_person_id'] ?? null;
            $invoice->subject = $data['subject'] ?? null;
            $invoice->customer_notes = $data['customer_notes'] ?? null;
            $invoice->terms_and_conditions = $data['terms_and_conditions'] ?? null;
            $invoice->items = $data['items'] ?? [];
            $invoice->sub_total = $calculations['sub_total'];
            $invoice->discount = $calculations['discount'];
            $invoice->adjustment = $calculations['adjustment'];
            $invoice->total = $calculations['total'];
            $invoice->status = Invoices::STATUS_DRAFT;
            $invoice->save();

            $this->logAction('invoice_created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
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
        if (!in_array($invoice->status, [Invoices::STATUS_DRAFT, 'open'])) {
            throw new Exception('Only draft invoices can be updated.');
        }

        return $this->transaction(function () use ($invoice, $data) {
            // Calculate totals from items
            $calculations = $this->calculateTotals($data['items'] ?? $invoice->items, $data['discount'] ?? $invoice->discount, $data['adjustment'] ?? $invoice->adjustment);

            // Update basic fields
            $fillableFields = [
                'customer_id', 'invoice_number', 'type', 'invoice_date', 'due_date',
                'order_number', 'payment_terms_id', 'sales_person_id', 'subject',
                'customer_notes', 'terms_and_conditions', 'items', 'sub_total',
                'discount', 'adjustment', 'total'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $invoice->$field = $data[$field];
                }
            }

            // Update calculated fields
            $invoice->sub_total = $calculations['sub_total'];
            $invoice->discount = $calculations['discount'];
            $invoice->adjustment = $calculations['adjustment'];
            $invoice->total = $calculations['total'];

            $invoice->save();

            $this->logAction('invoice_updated', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
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
        if (!in_array($invoice->status, [Invoices::STATUS_DRAFT, 'open'])) {
            throw new Exception('Only draft invoices can be sent.');
        }

        return $this->transaction(function () use ($invoice, $userId) {
            $invoice->status = Invoices::STATUS_SENT;
            $invoice->balance_due = $invoice->total;
            $invoice->save();

            // Try to create journal entry and decrement inventory
            try {
                // Initialize chart of accounts if needed
                $team = $invoice->team;
                $existingAccounts = \App\Models\LedgerAccount::where('team_id', $team->id)->count();
                if ($existingAccounts === 0) {
                    $this->chartOfAccountsService->initializeDefaultAccounts($team);
                }

                // Create journal entry for the invoice
                $this->createInvoiceJournalEntry($invoice, $team, $userId);

                // Decrement inventory for each item
                $this->decrementInventoryForInvoice($invoice);
            } catch (Exception $e) {
                // Log but don't fail if accounting is not set up
                $this->logError('Failed to create journal entry for invoice', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->logAction('invoice_sent', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
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
                'invoice_number' => $invoice->invoice_number,
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
            if (in_array($invoice->status, [Invoices::STATUS_SENT, Invoices::STATUS_PARTIAL])) {
                try {
                    $this->incrementInventoryForInvoice($invoice);

                    // Void the journal entry
                    $journalEntry = $invoice->journalEntries()->first();
                    if ($journalEntry) {
                        $this->journalEntryService->void($journalEntry, $userId, $reason);
                    }
                } catch (Exception $e) {
                    $this->logError('Failed to reverse inventory for cancelled invoice', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $invoice->status = Invoices::STATUS_CANCELLED;
            $invoice->save();

            $this->logAction('invoice_cancelled', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'cancelled_by' => $userId,
                'reason' => $reason,
            ]);

            return $invoice;
        });
    }

    /**
     * Delete a draft invoice.
     *
     * @param Invoices $invoice
     * @return bool
     * @throws Exception
     */
    public function delete(Invoices $invoice): bool
    {
        if (!in_array($invoice->status, [Invoices::STATUS_DRAFT, 'open'])) {
            throw new Exception('Only draft invoices can be deleted.');
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        $this->logAction('invoice_deleted', [
            'invoice_number' => $invoiceNumber,
        ]);

        return true;
    }

    /**
     * Calculate totals for an invoice.
     *
     * @param array $items
     * @param float $discountPercent
     * @param float $adjustment
     * @return array
     */
    protected function calculateTotals(array $items, float $discountPercent = 0, float $adjustment = 0): array
    {
        $subTotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $lineTotal = floatval($item['quantity'] ?? 0) * floatval($item['rate'] ?? 0);
            $subTotal += floatval($item['amount'] ?? $lineTotal);

            if (isset($item['tax']) && floatval($item['tax']) > 0) {
                $taxTotal += (floatval($item['tax']) / 100) * $lineTotal;
            }
        }

        $total = $subTotal + $taxTotal;
        
        if ($discountPercent > 0) {
            $total = $total - ($discountPercent / 100 * $total);
        }
        
        $total += $adjustment;

        return [
            'sub_total' => round($subTotal, 2),
            'discount' => round($discountPercent, 2),
            'adjustment' => round($adjustment, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Generate a unique invoice number.
     *
     * @param Team $team
     * @param string $type
     * @return string
     */
    protected function generateInvoiceNumber(Team $team, string $type = 'tax'): string
    {
        if ($type === 'proforma') {
            $prefix = 'PI-' . now()->format('my');
            $lastInvoice = Invoices::where('team_id', $team->id)
                ->where('type', 'proforma')
                ->where('invoice_number', 'like', $prefix . '%')
                ->latest()
                ->first();
        } else {
            $prefix = 'INV-' . now()->format('my');
            $lastInvoice = Invoices::where('team_id', $team->id)
                ->where('type', 'tax')
                ->where('invoice_number', 'like', $prefix . '%')
                ->latest()
                ->first();
        }

        $lastNumber = $lastInvoice ? (int) substr($lastInvoice->invoice_number, strlen($prefix)) : 0;
        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create journal entry for an invoice.
     *
     * @param Invoices $invoice
     * @param Team $team
     * @param int $userId
     * @return JournalEntry|null
     */
    protected function createInvoiceJournalEntry(Invoices $invoice, Team $team, int $userId): ?JournalEntry
    {
        // Get accounts
        $accountsReceivable = $this->chartOfAccountsService->getAccountsReceivable($team);
        $salesRevenue = $this->chartOfAccountsService->getSalesRevenue($team);
        $salesTaxPayable = $this->chartOfAccountsService->getDefaultAccount($team, 'sales_tax_payable');

        if (!$accountsReceivable || !$salesRevenue) {
            return null;
        }

        $lines = [];

        // Debit Accounts Receivable
        $lines[] = [
            'ledger_account_id' => $accountsReceivable->id,
            'type' => 'debit',
            'amount' => $invoice->total,
            'description' => "Invoice #{$invoice->invoice_number}",
        ];

        // Credit Sales Revenue
        $lines[] = [
            'ledger_account_id' => $salesRevenue->id,
            'type' => 'credit',
            'amount' => $invoice->sub_total,
            'description' => "Invoice #{$invoice->invoice_number}",
        ];

        // Credit Sales Tax Payable (if applicable)
        if ($salesTaxPayable) {
            $taxAmount = $this->calculateTaxAmount($invoice);
            if ($taxAmount > 0) {
                $lines[] = [
                    'ledger_account_id' => $salesTaxPayable->id,
                    'type' => 'credit',
                    'amount' => $taxAmount,
                    'description' => "Sales Tax - Invoice #{$invoice->invoice_number}",
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
     * Calculate tax amount for an invoice.
     *
     * @param Invoices $invoice
     * @return float
     */
    protected function calculateTaxAmount(Invoices $invoice): float
    {
        $taxTotal = 0;
        foreach ($invoice->items ?? [] as $item) {
            if (isset($item['tax']) && floatval($item['tax']) > 0) {
                $lineTotal = floatval($item['quantity'] ?? 0) * floatval($item['rate'] ?? 0);
                $taxTotal += (floatval($item['tax']) / 100) * $lineTotal;
            }
        }
        return round($taxTotal, 2);
    }

    /**
     * Decrement inventory for invoice items.
     *
     * @param Invoices $invoice
     * @return void
     */
    protected function decrementInventoryForInvoice(Invoices $invoice): void
    {
        foreach ($invoice->items ?? [] as $item) {
            if (isset($item['item']) && $item['item']) {
                $inventoryItem = Item::find($item['item']);
                if ($inventoryItem && $inventoryItem->track_inventory_for_this_item) {
                    $this->inventoryService->decrementStock(
                        $inventoryItem,
                        floatval($item['quantity'] ?? 0),
                        'invoice',
                        $invoice->id,
                        "Invoice #{$invoice->invoice_number}"
                    );
                }
            }
        }
    }

    /**
     * Increment inventory for cancelled invoice items.
     *
     * @param Invoices $invoice
     * @return void
     */
    protected function incrementInventoryForInvoice(Invoices $invoice): void
    {
        foreach ($invoice->items ?? [] as $item) {
            if (isset($item['item']) && $item['item']) {
                $inventoryItem = Item::find($item['item']);
                if ($inventoryItem && $inventoryItem->track_inventory_for_this_item) {
                    $this->inventoryService->incrementStock(
                        $inventoryItem,
                        floatval($item['quantity'] ?? 0),
                        'invoice_cancellation',
                        $invoice->id,
                        "Cancelled Invoice #{$invoice->invoice_number}"
                    );
                }
            }
        }
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
