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
            $payment->payment_date = $data['payment_date'] ?? now();
            $payment->amount = $data['amount'];
            $payment->payment_method = $data['payment_method'];
            $payment->reference = $data['reference'] ?? null;
            $payment->notes = $data['notes'] ?? null;
            $payment->status = PaymentsReceived::STATUS_RECEIVED;
            $payment->save();

            // Apply to invoices if specified
            if (isset($data['invoices']) && is_array($data['invoices'])) {
                $this->applyToInvoices($payment, $data['invoices']);
            }

            // Create journal entry
            if ($data['create_journal_entry'] ?? true) {
                $this->createPaymentJournalEntry($payment, $team, $data['user_id'] ?? null);
            }

            $this->logAction('payment_received_created', [
                'payment_id' => $payment->id,
                'customer_id' => $customer->id,
                'amount' => $payment->amount,
            ]);

            return $payment;
        });
    }

    /**
     * Apply payment to specific invoices.
     *
     * @param PaymentsReceived $payment
     * @param array $invoiceAllocations [[invoice_id => amount], ...]
     * @return void
     */
    public function applyToInvoices(PaymentsReceived $payment, array $invoiceAllocations): void
    {
        foreach ($invoiceAllocations as $allocation) {
            $invoice = Invoices::find($allocation['invoice_id']);

            if ($invoice && $invoice->customer_id === $payment->customer_id) {
                $amount = min($allocation['amount'], $invoice->balance_due);

                // Record the allocation
                DB::table('payment_invoice_allocations')->insert([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'created_at' => now(),
                ]);

                // Update invoice balance
                $this->invoiceService->applyPayment($invoice, $amount, $payment->id);
            }
        }
    }

    /**
     * Create journal entry for a payment.
     *
     * @param PaymentsReceived $payment
     * @param Team $team
     * @param int|null $userId
     * @return JournalEntry
     */
    protected function createPaymentJournalEntry(PaymentsReceived $payment, Team $team, ?int $userId): JournalEntry
    {
        // Get accounts
        $cashAccount = $this->chartOfAccountsService->getCash($team) ??
            $this->chartOfAccountsService->getBank($team);
        $accountsReceivable = $this->chartOfAccountsService->getAccountsReceivable($team);

        if (!$cashAccount || !$accountsReceivable) {
            throw new Exception('Required accounts not found. Please set up chart of accounts.');
        }

        return $this->journalEntryService->createAndPost($team, [
            'entry_date' => $payment->payment_date,
            'description' => "Payment received from {$payment->customer->name} - Ref: {$payment->reference}",
            'reference_type' => get_class($payment),
            'reference_id' => $payment->id,
            'user_id' => $userId,
            'lines' => [
                [
                    'ledger_account_id' => $cashAccount->id,
                    'type' => 'debit',
                    'amount' => $payment->amount,
                    'description' => "Payment from {$payment->customer->name}",
                ],
                [
                    'ledger_account_id' => $accountsReceivable->id,
                    'type' => 'credit',
                    'amount' => $payment->amount,
                    'description' => "Payment from {$payment->customer->name}",
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
                $this->journalEntryService->void($journalEntry, $userId, $reason);
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
                    ->whereColumn('payment_id', 'payments_received.id');
            })
            ->orderBy('payment_date')
            ->get();
    }
}
