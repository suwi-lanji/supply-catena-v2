<?php

namespace App\Services\Purchases;

use App\Models\Bill;
use App\Models\Vendor;
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

            $bill = new Bill();
            $bill->team_id = $team->id;
            $bill->vendor_id = $data['vendor_id'];
            $bill->bill_date = $data['bill_date'] ?? now();
            $bill->due_date = $data['due_date'] ?? now()->addDays(30);
            $bill->bill_number = $data['bill_number'] ?? $this->generateBillNumber($team);
            $bill->status = Bill::STATUS_OPEN;
            $bill->notes = $data['notes'] ?? null;

            // Calculate totals
            $this->calculateTotals($bill, $data['items']);
            $bill->balance_due = $bill->total;

            $bill->save();

            // Create bill items
            $this->createBillItems($bill, $data['items']);

            $this->logAction('bill_created', [
                'bill_id' => $bill->id,
                'vendor_id' => $vendor->id,
                'total' => $bill->total,
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
        if ($bill->status !== Bill::STATUS_OPEN) {
            throw new Exception('Only open bills can be approved.');
        }

        return $this->transaction(function () use ($bill, $userId) {
            $bill->status = Bill::STATUS_APPROVED;
            $bill->approved_by = $userId;
            $bill->approved_at = now();
            $bill->save();

            // Create journal entry
            $this->createBillJournalEntry($bill, $bill->team, $userId);

            // Increment inventory for each item
            foreach ($bill->items as $item) {
                if ($item->item_id) {
                    $inventoryItem = \App\Models\Item::find($item->item_id);
                    if ($inventoryItem) {
                        $this->inventoryService->incrementStock(
                            $inventoryItem,
                            $item->quantity,
                            'bill',
                            $bill->id,
                            "Bill #{$bill->bill_number}"
                        );
                    }
                }
            }

            $this->logAction('bill_approved', [
                'bill_id' => $bill->id,
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
            if ($bill->status === Bill::STATUS_APPROVED || $bill->status === Bill::STATUS_PARTIAL) {
                foreach ($bill->items as $item) {
                    if ($item->item_id) {
                        $inventoryItem = \App\Models\Item::find($item->item_id);
                        if ($inventoryItem) {
                            $this->inventoryService->decrementStock(
                                $inventoryItem,
                                $item->quantity,
                                'bill_cancellation',
                                $bill->id,
                                "Cancelled Bill #{$bill->bill_number}"
                            );
                        }
                    }
                }

                // Void the journal entry
                $journalEntry = $bill->journalEntries()->first();
                if ($journalEntry) {
                    $this->journalEntryService->void($journalEntry, $userId, $reason);
                }
            }

            $bill->status = Bill::STATUS_CANCELLED;
            $bill->save();

            $this->logAction('bill_cancelled', [
                'bill_id' => $bill->id,
                'cancelled_by' => $userId,
                'reason' => $reason,
            ]);

            return $bill;
        });
    }

    /**
     * Calculate totals for a bill.
     *
     * @param Bill $bill
     * @param array $items
     * @return void
     */
    protected function calculateTotals(Bill $bill, array $items): void
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * $item['rate'];
            $subtotal += $lineTotal;

            if (isset($item['tax_rate']) && $item['tax_rate'] > 0) {
                $taxTotal += $lineTotal * ($item['tax_rate'] / 100);
            }
        }

        $bill->subtotal = $subtotal;
        $bill->tax = $taxTotal;
        $bill->total = $subtotal + $taxTotal;
    }

    /**
     * Create bill items.
     *
     * @param Bill $bill
     * @param array $items
     * @return void
     */
    protected function createBillItems(Bill $bill, array $items): void
    {
        foreach ($items as $itemData) {
            $bill->items()->create([
                'item_id' => $itemData['item_id'] ?? null,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'rate' => $itemData['rate'],
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'total' => $itemData['quantity'] * $itemData['rate'],
            ]);
        }
    }

    /**
     * Generate a unique bill number.
     *
     * @param Team $team
     * @return string
     */
    protected function generateBillNumber(Team $team): string
    {
        $prefix = 'BILL';
        $count = Bill::where('team_id', $team->id)->count() + 1;
        return "{$prefix}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create journal entry for a bill.
     *
     * @param Bill $bill
     * @param Team $team
     * @param int $userId
     * @return JournalEntry
     */
    protected function createBillJournalEntry(Bill $bill, Team $team, int $userId): JournalEntry
    {
        // Get accounts
        $accountsPayable = $this->chartOfAccountsService->getAccountsPayable($team);
        $inventory = $this->chartOfAccountsService->getInventory($team);
        $salesTaxPayable = $this->chartOfAccountsService->getDefaultAccount($team, 'sales_tax_payable');

        if (!$accountsPayable || !$inventory) {
            throw new Exception('Required accounts not found. Please set up chart of accounts.');
        }

        $lines = [];

        // Debit Inventory (or expense account)
        $lines[] = [
            'ledger_account_id' => $inventory->id,
            'type' => 'debit',
            'amount' => $bill->subtotal,
            'description' => "Bill #{$bill->bill_number} - {$bill->vendor->name}",
        ];

        // Debit Tax (if applicable)
        if ($bill->tax > 0 && $salesTaxPayable) {
            $lines[] = [
                'ledger_account_id' => $salesTaxPayable->id,
                'type' => 'debit',
                'amount' => $bill->tax,
                'description' => "Tax - Bill #{$bill->bill_number}",
            ];
        }

        // Credit Accounts Payable
        $lines[] = [
            'ledger_account_id' => $accountsPayable->id,
            'type' => 'credit',
            'amount' => $bill->total,
            'description' => "Bill #{$bill->bill_number} - {$bill->vendor->name}",
        ];

        return $this->journalEntryService->createAndPost($team, [
            'entry_date' => $bill->bill_date,
            'description' => "Bill #{$bill->bill_number} from {$bill->vendor->name}",
            'reference_type' => get_class($bill),
            'reference_id' => $bill->id,
            'user_id' => $userId,
            'lines' => $lines,
        ], $userId);
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
