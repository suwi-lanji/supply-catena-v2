<?php

namespace App\Services\Sales;

use App\Models\PaymentsReceived;
use App\Models\Invoices;
use App\Models\Customer;
use App\Models\Team;
use App\Models\JournalEntry;
use App\Services\BaseService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\ChartOfAccountsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class PaymentReceivedService extends BaseService
{
    protected JournalEntryService $journalEntryService;
    protected ChartOfAccountsService $chartOfAccountsService;
    protected InvoiceService $invoiceService;

    public function __construct(
        JournalEntryService $journalEntryService,
        ChartOfAccountsService $chartOfAccountsService,
        InvoiceService $invoiceService
    ) {
        $this->journalEntryService = $journalEntryService;
        $this->chartOfAccountsService = $chartOfAccountsService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Record a payment received from a customer.
     *
     * @param Team $team
     * @param array $data
     * @return PaymentsReceived
     * @throws Exception
     */
    public function create(Team $team, array $data): PaymentsReceived
    {
        return $this->transaction(function () use ($team, $data) {
            $customer = Customer::findOrFail($data['customer_id']);
            if ($customer->team_id !== $team->id) {
                throw new Exception('Customer does not belong to this team.');
            }

            $payment = new PaymentsReceived();
            $payment->team_id = $team->id;
            $payment->customer_id = $data['customer_id'];
            $payment->payment_number = $data['payment_number'] ?? $this->generatePaymentNumber($team);
            $payment->payment_date = $data['payment_date'] ?? now();
            $payment->amount_received = $data['amount_received'] ?? 0;
            $payment->bank_charges = $data['bank_charges'] ?? 0;
            $payment->payment_mode = $data['payment_mode'] ?? null;
            $payment->paid_through = $data['paid_through'] ?? null;
            $payment->reference_number = $data['reference_number'] ?? null;
            $payment->notes = $data['notes'] ?? null;
            $payment->items = $data['items'] ?? [];
            $payment->status = PaymentsReceived::STATUS_RECEIVED;
            $payment->save();

            // Apply to invoices if specified
            if (isset($data['items']) && is_array($data['items'])) {
                $this->applyToInvoices($payment, $data['items']);
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

            $this->logAction('payment_received_created', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'customer_id' => $customer->id,
                'amount' => $payment->amount_received,
            ]);

            return $payment;
        });
    }

    /**
     * Apply payment to specific invoices.
     *
     * @param PaymentsReceived $payment
     * @param array $invoiceAllocations
     * @return void
     */
    public function applyToInvoices(PaymentsReceived $payment, array $invoiceAllocations): void
    {
        foreach ($invoiceAllocations as $allocation) {
            if (!isset($allocation['invoice_id']) || !isset($allocation['payment'])) {
                continue;
            }

            $invoice = Invoices::find($allocation['invoice_id']);

            if ($invoice && $invoice->customer_id === $payment->customer_id) {
                $amount = min(floatval($allocation['payment']), $invoice->balance_due);

                if ($amount <= 0) {
                    continue;
                }

                // Record the allocation
                DB::table('payment_invoice_allocations')->insert([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update invoice balance
                $this->invoiceService->applyPayment($invoice, $amount, $payment->id);
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
        $count = PaymentsReceived::where('team_id', $team->id)->count() + 1;
        return (string) $count;
    }

    /**
     * Create journal entry for a payment.
     *
     * @param PaymentsReceived $payment
     * @param Team $team
     * @param int|null $userId
     * @return JournalEntry|null
     */
    protected function createPaymentJournalEntry(PaymentsReceived $payment, Team $team, ?int $userId): ?JournalEntry
    {
        // Get accounts
        $cashAccount = $this->chartOfAccountsService->getCash($team) ??
            $this->chartOfAccountsService->getBank($team);
        $accountsReceivable = $this->chartOfAccountsService->getAccountsReceivable($team);

        if (!$cashAccount || !$accountsReceivable) {
            return null;
        }

        $customerName = $payment->customer ? ($payment->customer->company_display_name ?? $payment->customer->company_name ?? 'Customer') : 'Customer';

        return $this->journalEntryService->createAndPost($team, [
            'entry_date' => $payment->payment_date,
            'description' => "Payment received from {$customerName} - Ref: {$payment->reference_number}",
            'reference_type' => get_class($payment),
            'reference_id' => $payment->id,
            'user_id' => $userId,
            'lines' => [
                [
                    'ledger_account_id' => $cashAccount->id,
                    'type' => 'debit',
                    'amount' => $payment->amount_received,
                    'description' => "Payment from {$customerName}",
                ],
                [
                    'ledger_account_id' => $accountsReceivable->id,
                    'type' => 'credit',
                    'amount' => $payment->amount_received,
                    'description' => "Payment from {$customerName}",
                ],
            ],
        ], $userId);
    }

    /**
     * Void a payment.
     *
     * @param PaymentsReceived $payment
     * @param int $userId
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function void(PaymentsReceived $payment, int $userId, string $reason): bool
    {
        if ($payment->status === PaymentsReceived::STATUS_VOIDED) {
            throw new Exception('Payment is already voided.');
        }

        return $this->transaction(function () use ($payment, $userId, $reason) {
            // Reverse invoice allocations
            $allocations = DB::table('payment_invoice_allocations')
                ->where('payment_id', $payment->id)
                ->get();

            foreach ($allocations as $allocation) {
                $invoice = Invoices::find($allocation->invoice_id);
                if ($invoice) {
                    $invoice->balance_due += $allocation->amount;
                    $invoice->status = $invoice->balance_due >= $invoice->total
                        ? Invoices::STATUS_SENT
                        : Invoices::STATUS_PARTIAL;
                    $invoice->save();
                }
            }

            // Delete allocations
            DB::table('payment_invoice_allocations')
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

            $payment->status = PaymentsReceived::STATUS_VOIDED;
            $payment->save();

            $this->logAction('payment_received_voided', [
                'payment_id' => $payment->id,
                'voided_by' => $userId,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Get payments for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getCustomerPayments(Customer $customer): Collection
    {
        return PaymentsReceived::where('customer_id', $customer->id)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Get unallocated payments for a customer.
     *
     * @param Customer $customer
     * @return Collection
     */
    public function getUnallocatedPayments(Customer $customer): Collection
    {
        return PaymentsReceived::where('customer_id', $customer->id)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_invoice_allocations')
                    ->whereColumn('payment_id', 'payments_receiveds.id');
            })
            ->orderBy('payment_date')
            ->get();
    }
}
