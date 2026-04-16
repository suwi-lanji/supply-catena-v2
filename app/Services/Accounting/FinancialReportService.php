<?php

namespace App\Services\Accounting;

use App\Models\Team;
use App\Models\LedgerAccount;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Models\Invoices;
use App\Models\Bill;
use App\Models\PaymentsReceived;
use App\Models\PaymentsMade;
use App\Models\Item;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportService extends BaseService
{
    /**
     * Generate a trial balance report.
     *
     * @param Team $team
     * @param Carbon|null $asOf
     * @return array
     */
    public function trialBalance(Team $team, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();

        $accounts = LedgerAccount::where('team_id', $team->id)
            ->active()
            ->orderBy('code')
            ->get();

        $trialBalance = [
            'as_of' => $asOf,
            'total_debits' => 0,
            'total_credits' => 0,
            'accounts' => [],
        ];

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, $asOf);

            if ($balance != 0) {
                $debit = 0;
                $credit = 0;

                if ($account->isDebitAccount()) {
                    if ($balance > 0) {
                        $debit = abs($balance);
                    } else {
                        $credit = abs($balance);
                    }
                } else {
                    if ($balance > 0) {
                        $credit = abs($balance);
                    } else {
                        $debit = abs($balance);
                    }
                }

                $trialBalance['accounts'][] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $debit,
                    'credit' => $credit,
                ];

                $trialBalance['total_debits'] += $debit;
                $trialBalance['total_credits'] += $credit;
            }
        }

        return $trialBalance;
    }

    /**
     * Generate a balance sheet.
     *
     * @param Team $team
     * @param Carbon|null $asOf
     * @return array
     */
    public function balanceSheet(Team $team, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();

        $assets = $this->getAccountsByType($team, LedgerAccount::TYPE_ASSET, $asOf);
        $liabilities = $this->getAccountsByType($team, LedgerAccount::TYPE_LIABILITY, $asOf);
        $equity = $this->getAccountsByType($team, LedgerAccount::TYPE_EQUITY, $asOf);

        return [
            'as_of' => $asOf,
            'assets' => $assets,
            'total_assets' => $assets->sum('balance'),
            'liabilities' => $liabilities,
            'total_liabilities' => $liabilities->sum('balance'),
            'equity' => $equity,
            'total_equity' => $equity->sum('balance'),
            'total_liabilities_and_equity' => $liabilities->sum('balance') + $equity->sum('balance'),
        ];
    }

    /**
     * Generate an income statement (Profit & Loss).
     *
     * @param Team $team
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function incomeStatement(Team $team, Carbon $startDate, Carbon $endDate): array
    {
        $revenue = $this->getAccountsByType($team, LedgerAccount::TYPE_REVENUE, $endDate, $startDate);
        $expenses = $this->getAccountsByType($team, LedgerAccount::TYPE_EXPENSE, $endDate, $startDate);

        $totalRevenue = $revenue->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
        ];
    }

    /**
     * Generate a general ledger report.
     *
     * @param Team $team
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $accountId
     * @return array
     */
    public function generalLedger(Team $team, Carbon $startDate, Carbon $endDate, ?int $accountId = null): array
    {
        $query = AccountTransaction::where('team_id', $team->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['ledgerAccount', 'journalEntry'])
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($accountId) {
            $query->where('ledger_account_id', $accountId);
        }

        $transactions = $query->get()->groupBy('ledger_account_id');

        $ledger = [];
        foreach ($transactions as $accountId => $accountTransactions) {
            $account = $accountTransactions->first()->ledgerAccount;
            $runningBalance = 0;

            $lines = [];
            foreach ($accountTransactions as $transaction) {
                if ($account->isDebitAccount()) {
                    $runningBalance += $transaction->type === 'debit' ? $transaction->amount : -$transaction->amount;
                } else {
                    $runningBalance += $transaction->type === 'credit' ? $transaction->amount : -$transaction->amount;
                }

                $lines[] = [
                    'date' => $transaction->transaction_date,
                    'entry_number' => $transaction->journalEntry->entry_number ?? 'N/A',
                    'description' => $transaction->description,
                    'debit' => $transaction->type === 'debit' ? $transaction->amount : 0,
                    'credit' => $transaction->type === 'credit' ? $transaction->amount : 0,
                    'balance' => $runningBalance,
                ];
            }

            $ledger[] = [
                'account' => $account,
                'opening_balance' => $this->getAccountBalance($account, $startDate->copy()->subDay()),
                'transactions' => $lines,
                'closing_balance' => $runningBalance,
            ];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'ledger' => $ledger,
        ];
    }

    /**
     * Generate an accounts receivable aging report.
     *
     * @param Team $team
     * @param Carbon|null $asOf
     * @return array
     */
    public function accountsReceivableAging(Team $team, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();

        $invoices = Invoices::where('team_id', $team->id)
            ->where('balance_due', '>', 0)
            ->whereIn('status', [Invoices::STATUS_SENT, Invoices::STATUS_PARTIAL])
            ->with('customer')
            ->get();

        $aging = [
            'current' => [],
            'days_1_30' => [],
            'days_31_60' => [],
            'days_61_90' => [],
            'over_90' => [],
        ];

        $totals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0,
        ];

        foreach ($invoices as $invoice) {
            $daysOverdue = $asOf->diffInDays($invoice->due_date, false);
            $amount = $invoice->balance_due;

            $bucket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => 'days_1_30',
                $daysOverdue <= 60 => 'days_31_60',
                $daysOverdue <= 90 => 'days_61_90',
                default => 'over_90',
            };

            $aging[$bucket][] = [
                'customer' => $invoice->customer->name,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'days_overdue' => max(0, $daysOverdue),
                'amount' => $amount,
            ];

            $totals[$bucket] += $amount;
        }

        return [
            'as_of' => $asOf,
            'aging' => $aging,
            'totals' => $totals,
            'grand_total' => array_sum($totals),
        ];
    }

    /**
     * Generate an accounts payable aging report.
     *
     * @param Team $team
     * @param Carbon|null $asOf
     * @return array
     */
    public function accountsPayableAging(Team $team, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();

        $bills = Bill::where('team_id', $team->id)
            ->where('balance_due', '>', 0)
            ->whereIn('status', [Bill::STATUS_APPROVED, Bill::STATUS_PARTIAL])
            ->with('vendor')
            ->get();

        $aging = [
            'current' => [],
            'days_1_30' => [],
            'days_31_60' => [],
            'days_61_90' => [],
            'over_90' => [],
        ];

        $totals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0,
        ];

        foreach ($bills as $bill) {
            $daysOverdue = $asOf->diffInDays($bill->due_date, false);
            $amount = $bill->balance_due;

            $bucket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => 'days_1_30',
                $daysOverdue <= 60 => 'days_31_60',
                $daysOverdue <= 90 => 'days_61_90',
                default => 'over_90',
            };

            $aging[$bucket][] = [
                'vendor' => $bill->vendor->name,
                'bill_number' => $bill->bill_number,
                'bill_date' => $bill->bill_date,
                'due_date' => $bill->due_date,
                'days_overdue' => max(0, $daysOverdue),
                'amount' => $amount,
            ];

            $totals[$bucket] += $amount;
        }

        return [
            'as_of' => $asOf,
            'aging' => $aging,
            'totals' => $totals,
            'grand_total' => array_sum($totals),
        ];
    }

    /**
     * Generate a sales summary report.
     *
     * @param Team $team
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function salesSummary(Team $team, Carbon $startDate, Carbon $endDate): array
    {
        $invoices = Invoices::where('team_id', $team->id)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with('customer')
            ->get();

        $payments = PaymentsReceived::where('team_id', $team->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_invoices' => $invoices->count(),
            'total_invoice_amount' => $invoices->sum('total'),
            'total_payments' => $payments->count(),
            'total_payments_amount' => $payments->sum('amount'),
            'invoices_by_status' => $invoices->groupBy('status')->map->count(),
            'top_customers' => $invoices->groupBy('customer_id')
                ->map(fn($group) => [
                    'customer' => $group->first()->customer->name ?? 'Unknown',
                    'total' => $group->sum('total'),
                ])
                ->sortByDesc('total')
                ->take(10)
                ->values(),
        ];
    }

    /**
     * Generate a purchases summary report.
     *
     * @param Team $team
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function purchasesSummary(Team $team, Carbon $startDate, Carbon $endDate): array
    {
        $bills = Bill::where('team_id', $team->id)
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->with('vendor')
            ->get();

        $payments = PaymentsMade::where('team_id', $team->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_bills' => $bills->count(),
            'total_bill_amount' => $bills->sum('total'),
            'total_payments' => $payments->count(),
            'total_payments_amount' => $payments->sum('amount'),
            'bills_by_status' => $bills->groupBy('status')->map->count(),
            'top_vendors' => $bills->groupBy('vendor_id')
                ->map(fn($group) => [
                    'vendor' => $group->first()->vendor->name ?? 'Unknown',
                    'total' => $group->sum('total'),
                ])
                ->sortByDesc('total')
                ->take(10)
                ->values(),
        ];
    }

    /**
     * Generate an inventory valuation report.
     *
     * @param Team $team
     * @return array
     */
    public function inventoryValuation(Team $team): array
    {
        $items = Item::where('team_id', $team->id)
            ->where('track_inventory', true)
            ->get();

        $valuation = [];
        $totalValue = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $value = $item->stock_on_hand * $item->cost_price;
            $totalValue += $value;
            $totalQuantity += $item->stock_on_hand;

            $valuation[] = [
                'item' => $item,
                'sku' => $item->sku,
                'name' => $item->name,
                'quantity' => $item->stock_on_hand,
                'unit_cost' => $item->cost_price,
                'total_value' => $value,
                'reorder_level' => $item->reorder_level,
                'is_low_stock' => $item->stock_on_hand <= $item->reorder_level,
            ];
        }

        return [
            'items' => $valuation,
            'total_items' => $items->count(),
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'low_stock_count' => collect($valuation)->where('is_low_stock', true)->count(),
        ];
    }

    /**
     * Get account balance at a specific date.
     *
     * @param LedgerAccount $account
     * @param Carbon $date
     * @return float
     */
    protected function getAccountBalance(LedgerAccount $account, Carbon $date): float
    {
        $transactions = AccountTransaction::where('ledger_account_id', $account->id)
            ->where('transaction_date', '<=', $date)
            ->get();

        $debits = $transactions->where('type', 'debit')->sum('amount');
        $credits = $transactions->where('type', 'credit')->sum('amount');

        if ($account->isDebitAccount()) {
            return $account->opening_balance + $debits - $credits;
        }

        return $account->opening_balance + $credits - $debits;
    }

    /**
     * Get accounts by type with balances for a date range.
     *
     * @param Team $team
     * @param string $type
     * @param Carbon $endDate
     * @param Carbon|null $startDate
     * @return Collection
     */
    protected function getAccountsByType(Team $team, string $type, Carbon $endDate, ?Carbon $startDate = null): Collection
    {
        $accounts = LedgerAccount::where('team_id', $team->id)
            ->where('type', $type)
            ->active()
            ->orderBy('code')
            ->get();

        return $accounts->map(function ($account) use ($endDate, $startDate) {
            if ($startDate) {
                // Calculate change over period
                $startBalance = $this->getAccountBalance($account, $startDate->copy()->subDay());
                $endBalance = $this->getAccountBalance($account, $endDate);
                $account->balance = $endBalance - $startBalance;
            } else {
                $account->balance = $this->getAccountBalance($account, $endDate);
            }
            return $account;
        })->filter(fn($account) => $account->balance != 0);
    }
}
