<?php

namespace App\Services\Purchases;

use App\Models\PaymentsMade;
use App\Models\Bill;
use App\Models\Vendor;
use App\Models\Team;
use App\Models\JournalEntry;
use App\Services\BaseService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\ChartOfAccountsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class PaymentMadeService extends BaseService
{
    protected JournalEntryService $journalEntryService;
    protected ChartOfAccountsService $chartOfAccountsService;
    protected BillService $billService;

    public function __construct(
        JournalEntryService $journalEntryService,
        ChartOfAccountsService $chartOfAccountsService,
        BillService $billService
    ) {
        $this->journalEntryService = $journalEntryService;
        $this->chartOfAccountsService = $chartOfAccountsService;
        $this->billService = $billService;
    }

    /**
     * Record a payment made to a vendor.
     *
     * @param Team $team
     * @param array $data
     * @return PaymentsMade
     * @throws Exception
     */
    public function create(Team $team, array $data): PaymentsMade
    {
        return $this->transaction(function () use ($team, $data) {
            $vendor = Vendor::findOrFail($data['vendor_id']);
            if ($vendor->team_id !== $team->id) {
                throw new Exception('Vendor does not belong to this team.');
            }

            $payment = new PaymentsMade();
            $payment->team_id = $team->id;
            $payment->vendor_id = $data['vendor_id'];
            $payment->payment_number = $data['payment_number'] ?? $this->generatePaymentNumber($team);
            $payment->payment_date = $data['payment_date'] ?? now();
            $payment->amount = $data['amount'] ?? 0;
            $payment->bank_charges = $data['bank_charges'] ?? 0;
            $payment->payment_mode = $data['payment_mode'] ?? null;
            $payment->paid_through = $data['paid_through'] ?? null;
            $payment->reference_number = $data['reference_number'] ?? null;
            $payment->notes = $data['notes'] ?? null;
            $payment->items = $data['items'] ?? [];
            $payment->status = PaymentsMade::STATUS_PAID;
            $payment->save();

            // Apply to bills if specified
            if (isset($data['items']) && is_array($data['items'])) {
                $this->applyToBills($payment, $data['items']);
            }

            // Try to create journal entry
            try {
                $team = $payment->team;
                $existingAccounts = \App\Models\LedgerAccount::where('team_id', $team->id)->count();
                if ($existingAccounts === 0) {
                    $this->chartOfAccountsService->initializeDefaultAccounts($team);
                }
                $this->createPaymentJournalEntry($payment, $team, auth()->id());
            } catch (Exception $e) {
                $this->logError('Failed to create journal entry for payment', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->logAction('payment_made_created', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'vendor_id' => $vendor->id,
                'amount' => $payment->amount,
            ]);

            return $payment;
        });
    }

    /**
     * Apply payment to specific bills.
     *
     * @param PaymentsMade $payment
     * @param array $billAllocations
     * @return void
     */
    public function applyToBills(PaymentsMade $payment, array $billAllocations): void
    {
        foreach ($billAllocations as $allocation) {
            if (!isset($allocation['bill_id']) && !isset($allocation['invoice_id'])) {
                // Support both bill_id and invoice_id (as bills are sometimes called invoices in purchases)
                continue;
            }

            $billId = $allocation['bill_id'] ?? $allocation['invoice_id'] ?? null;
            $bill = Bill::find($billId);

            if ($bill && $bill->vendor_id === $payment->vendor_id) {
                $amount = min(floatval($allocation['payment'] ?? $allocation['amount'] ?? 0), $bill->balance_due);

                if ($amount <= 0) {
                    continue;
                }

                // Record the allocation
                DB::table('payment_bill_allocations')->insert([
                    'payment_id' => $payment->id,
                    'bill_id' => $bill->id,
                    'amount' => $amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update bill balance
                $this->billService->applyPayment($bill, $amount, $payment->id);
            }
        }
    }

    /**
     * Generate a unique payment number.
     *
     * @param Team $team
     * @return string
     */
    protected function generatePaymentNumber(Team $team): string
    {
        $count = PaymentsMade::where('team_id', $team->id)->count() + 1;
        return 'PM-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create journal entry for a payment.
     *
     * @param PaymentsMade $payment
     * @param Team $team
     * @param int|null $userId
     * @return JournalEntry|null
     */
    protected function createPaymentJournalEntry(PaymentsMade $payment, Team $team, ?int $userId): ?JournalEntry
    {
        // Get accounts
        $cashAccount = $this->chartOfAccountsService->getCash($team) ??
            $this->chartOfAccountsService->getBank($team);
        $accountsPayable = $this->chartOfAccountsService->getAccountsPayable($team);

        if (!$cashAccount || !$accountsPayable) {
            return null;
        }

        $vendorName = $payment->vendor ? ($payment->vendor->vendor_display_name ?? $payment->vendor->vendor_name ?? 'Vendor') : 'Vendor';

        return $this->journalEntryService->createAndPost($team, [
            'entry_date' => $payment->payment_date,
            'description' => "Payment made to {$vendorName} - Ref: {$payment->reference_number}",
            'reference_type' => get_class($payment),
            'reference_id' => $payment->id,
            'user_id' => $userId,
            'lines' => [
                [
                    'ledger_account_id' => $accountsPayable->id,
                    'type' => 'debit',
                    'amount' => $payment->amount,
                    'description' => "Payment to {$vendorName}",
                ],
                [
                    'ledger_account_id' => $cashAccount->id,
                    'type' => 'credit',
                    'amount' => $payment->amount,
                    'description' => "Payment to {$vendorName}",
                ],
            ],
        ], $userId);
    }

    /**
     * Void a payment.
     *
     * @param PaymentsMade $payment
     * @param int $userId
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function void(PaymentsMade $payment, int $userId, string $reason): bool
    {
        if ($payment->status === PaymentsMade::STATUS_VOIDED) {
            throw new Exception('Payment is already voided.');
        }

        return $this->transaction(function () use ($payment, $userId, $reason) {
            // Reverse bill allocations
            $allocations = DB::table('payment_bill_allocations')
                ->where('payment_id', $payment->id)
                ->get();

            foreach ($allocations as $allocation) {
                $bill = Bill::find($allocation->bill_id);
                if ($bill) {
                    $bill->balance_due += $allocation->amount;
                    $bill->status = $bill->balance_due >= $bill->total
                        ? Bill::STATUS_APPROVED
                        : Bill::STATUS_PARTIAL;
                    $bill->save();
                }
            }

            // Delete allocations
            DB::table('payment_bill_allocations')
                ->where('payment_id', $payment->id)
                ->delete();

            // Void journal entry
            $journalEntry = $payment->journalEntries()->first();
            if ($journalEntry) {
                try {
                    $this->journalEntryService->void($journalEntry, $userId, $reason);
                } catch (Exception $e) {
                    $this->logError('Failed to void journal entry', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $payment->status = PaymentsMade::STATUS_VOIDED;
            $payment->save();

            $this->logAction('payment_made_voided', [
                'payment_id' => $payment->id,
                'voided_by' => $userId,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Get payments for a vendor.
     *
     * @param Vendor $vendor
     * @return Collection
     */
    public function getVendorPayments(Vendor $vendor): Collection
    {
        return PaymentsMade::where('vendor_id', $vendor->id)
            ->orderBy('payment_date', 'desc')
            ->get();
    }
}
