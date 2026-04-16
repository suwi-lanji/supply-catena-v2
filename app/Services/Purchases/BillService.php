<?php

namespace App\Services\Purchases;

use App\Models\Bill;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Team;
use App\Models\JournalEntry;
use App\Services\BaseService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\ChartOfAccountsService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class BillService extends BaseService
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
     * Create a new bill.
     *
     * @param Team $team
     * @param array $data
     * @return Bill
     * @throws Exception
     */
    public function create(Team $team, array $data): Bill
    {
        return $this->transaction(function () use ($team, $data) {
            $vendor = Vendor::findOrFail($data['vendor_id']);
            if ($vendor->team_id !== $team->id) {
                throw new Exception('Vendor does not belong to this team.');
            }

            // Calculate totals from items
            $calculations = $this->calculateTotals($data['items'] ?? [], $data['discount'] ?? 0, $data['adjustment'] ?? 0);

            $bill = new Bill();
            $bill->team_id = $team->id;
            $bill->vendor_id = $data['vendor_id'];
            $bill->bill_number = $data['bill_number'] ?? $this->generateBillNumber($team);
            $bill->order_number = $data['order_number'] ?? null;
            $bill->bill_date = $data['bill_date'] ?? now();
            $bill->due_date = $data['due_date'] ?? now()->addDays(30);
            $bill->payment_terms = $data['payment_terms'] ?? null;
            $bill->subject = $data['subject'] ?? null;
            $bill->items = $data['items'] ?? [];
            $bill->sub_total = $calculations['sub_total'];
            $bill->discount = $calculations['discount'];
            $bill->adjustment = $calculations['adjustment'];
            $bill->total = $calculations['total'];
            $bill->balance_due = $calculations['total'];
            $bill->notes = $data['notes'] ?? null;
            $bill->status = Bill::STATUS_OPEN;
            $bill->save();

            $this->logAction('bill_created', [
                'bill_id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'vendor_id' => $vendor->id,
                'total' => $bill->total,
            ]);

            return $bill;
        });
    }

    /**
     * Update a bill.
     *
     * @param Bill $bill
     * @param array $data
     * @return Bill
     * @throws Exception
     */
    public function update(Bill $bill, array $data): Bill
    {
        if (!in_array($bill->status, [Bill::STATUS_OPEN, 'open'])) {
            throw new Exception('Only open bills can be updated.');
        }

        return $this->transaction(function () use ($bill, $data) {
            // Calculate totals from items
            $calculations = $this->calculateTotals($data['items'] ?? $bill->items, $data['discount'] ?? $bill->discount, $data['adjustment'] ?? $bill->adjustment);

            $fillableFields = [
                'vendor_id', 'bill_number', 'order_number', 'bill_date', 'due_date',
                'payment_terms', 'subject', 'items', 'sub_total', 'discount',
                'adjustment', 'total', 'notes'
            ];

            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $bill->$field = $data[$field];
                }
            }

            // Update calculated fields
            $bill->sub_total = $calculations['sub_total'];
            $bill->discount = $calculations['discount'];
            $bill->adjustment = $calculations['adjustment'];
            $bill->total = $calculations['total'];
            $bill->balance_due = $calculations['total'];

            $bill->save();

            $this->logAction('bill_updated', [
                'bill_id' => $bill->id,
                'bill_number' => $bill->bill_number,
            ]);

            return $bill;
        });
    }

    /**
     * Approve and post a bill.
     *
     * @param Bill $bill
     * @param int $userId
     * @return Bill
     * @throws Exception
     */
    public function approve(Bill $bill, int $userId): Bill
    {
        if (!in_array($bill->status, [Bill::STATUS_OPEN, 'open'])) {
            throw new Exception('Only open bills can be approved.');
        }

        return $this->transaction(function () use ($bill, $userId) {
            $bill->status = Bill::STATUS_APPROVED;
            $bill->approved_by = $userId;
            $bill->approved_at = now();
            $bill->save();

            // Try to create journal entry and increment inventory
            try {
                $team = $bill->team;
                $existingAccounts = \App\Models\LedgerAccount::where('team_id', $team->id)->count();
                if ($existingAccounts === 0) {
                    $this->chartOfAccountsService->initializeDefaultAccounts($team);
                }

                // Create journal entry
                $this->createBillJournalEntry($bill, $team, $userId);

                // Increment inventory for each item
                $this->incrementInventoryForBill($bill);
            } catch (Exception $e) {
                $this->logError('Failed to create journal entry for bill', [
                    'bill_id' => $bill->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->logAction('bill_approved', [
                'bill_id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'approved_by' => $userId,
            ]);

            return $bill;
        });
    }

    /**
     * Apply a payment to a bill.
     *
     * @param Bill $bill
     * @param float $amount
     * @param int $paymentId
     * @return Bill
     */
    public function applyPayment(Bill $bill, float $amount, int $paymentId): Bill
    {
        return $this->transaction(function () use ($bill, $amount, $paymentId) {
            $bill->balance_due = max(0, $bill->balance_due - $amount);

            if ($bill->balance_due <= 0) {
                $bill->status = Bill::STATUS_PAID;
            } elseif ($amount > 0) {
                $bill->status = Bill::STATUS_PARTIAL;
            }

            $bill->save();

            $this->logAction('bill_payment_applied', [
                'bill_id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'amount' => $amount,
                'payment_id' => $paymentId,
            ]);

            return $bill;
        });
    }

    /**
     * Cancel a bill.
     *
     * @param Bill $bill
     * @param int $userId
     * @param string $reason
     * @return Bill
     * @throws Exception
     */
    public function cancel(Bill $bill, int $userId, string $reason = ''): Bill
    {
        if (in_array($bill->status, [Bill::STATUS_PAID, Bill::STATUS_CANCELLED])) {
            throw new Exception('Cannot cancel this bill.');
        }

        return $this->transaction(function () use ($bill, $userId, $reason) {
            // Reverse inventory if bill was approved
            if (in_array($bill->status, [Bill::STATUS_APPROVED, Bill::STATUS_PARTIAL])) {
                try {
                    $this->decrementInventoryForBill($bill);

                    // Void the journal entry
                    $journalEntry = $bill->journalEntries()->first();
                    if ($journalEntry) {
                        $this->journalEntryService->void($journalEntry, $userId, $reason);
                    }
                } catch (Exception $e) {
                    $this->logError('Failed to reverse inventory for cancelled bill', [
                        'bill_id' => $bill->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $bill->status = Bill::STATUS_CANCELLED;
            $bill->save();

            $this->logAction('bill_cancelled', [
                'bill_id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'cancelled_by' => $userId,
                'reason' => $reason,
            ]);

            return $bill;
        });
    }

    /**
     * Delete an open bill.
     *
     * @param Bill $bill
     * @return bool
     * @throws Exception
     */
    public function delete(Bill $bill): bool
    {
        if (!in_array($bill->status, [Bill::STATUS_OPEN, 'open'])) {
            throw new Exception('Only open bills can be deleted.');
        }

        $billNumber = $bill->bill_number;
        $bill->delete();

        $this->logAction('bill_deleted', [
            'bill_number' => $billNumber,
        ]);

        return true;
    }

    /**
     * Calculate totals for a bill.
     *
     * @param array $items
     * @param float $discountPercent
     * @param float $adjustment
     * @return array
     */
    protected function calculateTotals(array $items, float $discountPercent = 0, float $adjustment = 0): array
    {
        $subTotal = 0;

        foreach ($items as $item) {
            $lineTotal = floatval($item['quantity'] ?? 0) * floatval($item['rate'] ?? 0);
            $subTotal += floatval($item['amount'] ?? $lineTotal);
        }

        $total = $subTotal;
        
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
     * Generate a unique bill number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateBillNumber(Team $team): string
    {
        $prefix = 'BL-';
        $count = Bill::where('team_id', $team->id)->count() + 1;
        return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create journal entry for a bill.
     *
     * @param Bill $bill
     * @param Team $team
     * @param int $userId
     * @return JournalEntry|null
     */
    protected function createBillJournalEntry(Bill $bill, Team $team, int $userId): ?JournalEntry
    {
        $accountsPayable = $this->chartOfAccountsService->getAccountsPayable($team);
        $inventory = $this->chartOfAccountsService->getInventory($team);
        $salesTaxPayable = $this->chartOfAccountsService->getDefaultAccount($team, 'sales_tax_payable');

        if (!$accountsPayable || !$inventory) {
            return null;
        }

        $lines = [];

        // Debit Inventory (or expense account)
        $lines[] = [
            'ledger_account_id' => $inventory->id,
            'type' => 'debit',
            'amount' => $bill->sub_total,
            'description' => "Bill #{$bill->bill_number}",
        ];

        // Debit Tax (if applicable)
        if ($salesTaxPayable) {
            $taxAmount = $this->calculateTaxAmount($bill);
            if ($taxAmount > 0) {
                $lines[] = [
                    'ledger_account_id' => $salesTaxPayable->id,
                    'type' => 'debit',
                    'amount' => $taxAmount,
                    'description' => "Tax - Bill #{$bill->bill_number}",
                ];
            }
        }

        // Credit Accounts Payable
        $lines[] = [
            'ledger_account_id' => $accountsPayable->id,
            'type' => 'credit',
            'amount' => $bill->total,
            'description' => "Bill #{$bill->bill_number}",
        ];

        return $this->journalEntryService->createAndPost($team, [
            'entry_date' => $bill->bill_date,
            'description' => "Bill #{$bill->bill_number}",
            'reference_type' => get_class($bill),
            'reference_id' => $bill->id,
            'user_id' => $userId,
            'lines' => $lines,
        ], $userId);
    }

    /**
     * Calculate tax amount for a bill.
     *
     * @param Bill $bill
     * @return float
     */
    protected function calculateTaxAmount(Bill $bill): float
    {
        $taxTotal = 0;
        foreach ($bill->items ?? [] as $item) {
            if (isset($item['tax']) && floatval($item['tax']) > 0) {
                $lineTotal = floatval($item['quantity'] ?? 0) * floatval($item['rate'] ?? 0);
                $taxTotal += (floatval($item['tax']) / 100) * $lineTotal;
            }
        }
        return round($taxTotal, 2);
    }

    /**
     * Increment inventory for bill items.
     *
     * @param Bill $bill
     * @return void
     */
    protected function incrementInventoryForBill(Bill $bill): void
    {
        foreach ($bill->items ?? [] as $item) {
            if (isset($item['item']) && $item['item']) {
                $inventoryItem = Item::find($item['item']);
                if ($inventoryItem && $inventoryItem->track_inventory_for_this_item) {
                    $this->inventoryService->incrementStock(
                        $inventoryItem,
                        floatval($item['quantity'] ?? 0),
                        'bill',
                        $bill->id,
                        "Bill #{$bill->bill_number}"
                    );
                }
            }
        }
    }

    /**
     * Decrement inventory for cancelled bill items.
     *
     * @param Bill $bill
     * @return void
     */
    protected function decrementInventoryForBill(Bill $bill): void
    {
        foreach ($bill->items ?? [] as $item) {
            if (isset($item['item']) && $item['item']) {
                $inventoryItem = Item::find($item['item']);
                if ($inventoryItem && $inventoryItem->track_inventory_for_this_item) {
                    $this->inventoryService->decrementStock(
                        $inventoryItem,
                        floatval($item['quantity'] ?? 0),
                        'bill_cancellation',
                        $bill->id,
                        "Cancelled Bill #{$bill->bill_number}"
                    );
                }
            }
        }
    }

    /**
     * Get bills for a vendor.
     *
     * @param Vendor $vendor
     * @return Collection
     */
    public function getVendorBills(Vendor $vendor): Collection
    {
        return Bill::where('vendor_id', $vendor->id)
            ->orderBy('bill_date', 'desc')
            ->get();
    }

    /**
     * Get outstanding bills for a vendor.
     *
     * @param Vendor $vendor
     * @return Collection
     */
    public function getOutstandingBills(Vendor $vendor): Collection
    {
        return Bill::where('vendor_id', $vendor->id)
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->get();
    }
}
