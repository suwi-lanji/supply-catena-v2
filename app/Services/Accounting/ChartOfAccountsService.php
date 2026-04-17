<?php

namespace App\Services\Accounting;

use App\Models\LedgerAccount;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ChartOfAccountsService extends BaseService
{
    /**
     * Create a new ledger account.
     *
     * @param Team $team
     * @param array $data
     * @return LedgerAccount
     * @throws Exception
     */
    public function createAccount(Team $team, array $data): LedgerAccount
    {
        $this->validateAccountData($data, $team);

        $account = LedgerAccount::create([
            'team_id' => $team->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'sub_type' => $data['sub_type'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'opening_balance' => $data['opening_balance'] ?? 0,
            'current_balance' => $data['opening_balance'] ?? 0,
            'is_active' => true,
            'is_system' => $data['is_system'] ?? false,
        ]);

        $this->logAction('ledger_account_created', [
            'account_id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
        ]);

        return $account;
    }

    /**
     * Update a ledger account.
     *
     * @param LedgerAccount $account
     * @param array $data
     * @return LedgerAccount
     * @throws Exception
     */
    public function updateAccount(LedgerAccount $account, array $data): LedgerAccount
    {
        if ($account->is_system && isset($data['code'])) {
            throw new Exception('Cannot modify system account code.');
        }

        $account->update($data);

        $this->logAction('ledger_account_updated', [
            'account_id' => $account->id,
            'code' => $account->code,
        ]);

        return $account;
    }

    /**
     * Delete a ledger account.
     *
     * @param LedgerAccount $account
     * @return bool
     * @throws Exception
     */
    public function deleteAccount(LedgerAccount $account): bool
    {
        if ($account->is_system) {
            throw new Exception('Cannot delete system account.');
        }

        if ($account->transactions()->exists()) {
            throw new Exception('Cannot delete account with transactions.');
        }

        $account->delete();

        $this->logAction('ledger_account_deleted', [
            'account_id' => $account->id,
            'code' => $account->code,
        ]);

        return true;
    }

    /**
     * Validate account data.
     *
     * @param array $data
     * @throws Exception
     */
    protected function validateAccountData(array $data, ?Team $team = null): void
    {
        if (empty($data['code'])) {
            throw new Exception('Account code is required.');
        }

        if (empty($data['name'])) {
            throw new Exception('Account name is required.');
        }

        if (!in_array($data['type'], LedgerAccount::getTypes())) {
            throw new Exception('Invalid account type.');
        }

        // Check for duplicate code within the team
        if ($team) {
            $exists = LedgerAccount::where('team_id', $team->id)
                ->where('code', $data['code'])
                ->exists();

            if ($exists) {
                throw new Exception('Account code already exists.');
            }
        }
    }

    /**
     * Initialize default chart of accounts for a team.
     *
     * @param Team $team
     * @return Collection
     */
    public function initializeDefaultAccounts(Team $team): Collection
    {
        $accounts = collect();

        DB::transaction(function () use ($team, &$accounts) {
            $defaultAccounts = $this->getDefaultAccountsConfig();

            foreach ($defaultAccounts as $accountData) {
                $account = $this->createAccount($team, array_merge($accountData, ['is_system' => true]));
                $accounts->push($account);
            }
        });

        return $accounts;
    }

    /**
     * Get default accounts configuration.
     *
     * @return array
     */
    protected function getDefaultAccountsConfig(): array
    {
        return [
            // Assets
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'sub_type' => 'cash'],
            ['code' => '1100', 'name' => 'Bank Accounts', 'type' => 'asset', 'sub_type' => 'bank'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'sub_type' => 'accounts_receivable'],
            ['code' => '1300', 'name' => 'Inventory', 'type' => 'asset', 'sub_type' => 'inventory'],
            ['code' => '1500', 'name' => 'Fixed Assets', 'type' => 'asset', 'sub_type' => 'fixed_asset'],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'sub_type' => 'accounts_payable'],
            ['code' => '2100', 'name' => 'Sales Tax Payable', 'type' => 'liability', 'sub_type' => 'current_liability'],
            ['code' => '2200', 'name' => 'Accrued Expenses', 'type' => 'liability', 'sub_type' => 'current_liability'],
            ['code' => '2500', 'name' => 'Long-term Liabilities', 'type' => 'liability', 'sub_type' => 'long_term_liability'],

            // Equity
            ['code' => '3000', 'name' => 'Owner\'s Capital', 'type' => 'equity', 'sub_type' => 'capital'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'sub_type' => 'retained_earnings'],
            ['code' => '3200', 'name' => 'Owner\'s Drawings', 'type' => 'equity', 'sub_type' => 'drawings'],

            // Revenue
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4100', 'name' => 'Sales Returns and Allowances', 'type' => 'revenue', 'sub_type' => 'sales'],
            ['code' => '4200', 'name' => 'Sales Discounts', 'type' => 'revenue', 'sub_type' => 'discount_received'],
            ['code' => '4900', 'name' => 'Other Income', 'type' => 'revenue', 'sub_type' => 'other_income'],

            // Expenses
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'sub_type' => 'cost_of_goods_sold'],
            ['code' => '6000', 'name' => 'Salaries and Wages', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6100', 'name' => 'Rent Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6200', 'name' => 'Utilities Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6300', 'name' => 'Office Supplies', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6400', 'name' => 'Depreciation Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6500', 'name' => 'Bad Debt Expense', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6600', 'name' => 'Bank Charges', 'type' => 'expense', 'sub_type' => 'operating_expense'],
            ['code' => '6700', 'name' => 'Discount Allowed', 'type' => 'expense', 'sub_type' => 'discount_allowed'],
        ];
    }

    /**
     * Get accounts by type.
     *
     * @param Team $team
     * @param string $type
     * @return Collection
     */
    public function getAccountsByType(Team $team, string $type): Collection
    {
        return LedgerAccount::where('team_id', $team->id)
            ->where('type', $type)
            ->active()
            ->orderBy('code')
            ->get();
    }

    /**
     * Get the default account for a specific purpose.
     *
     * @param Team $team
     * @param string $subType
     * @return LedgerAccount|null
     */
    public function getDefaultAccount(Team $team, string $subType): ?LedgerAccount
    {
        return LedgerAccount::where('team_id', $team->id)
            ->where('sub_type', $subType)
            ->active()
            ->first();
    }

    /**
     * Get accounts receivable account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getAccountsReceivable(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_ACCOUNTS_RECEIVABLE);
    }

    /**
     * Get accounts payable account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getAccountsPayable(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_ACCOUNTS_PAYABLE);
    }

    /**
     * Get sales revenue account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getSalesRevenue(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_SALES);
    }

    /**
     * Get inventory account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getInventory(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_INVENTORY);
    }

    /**
     * Get cost of goods sold account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getCostOfGoodsSold(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_COST_OF_GOODS_SOLD);
    }

    /**
     * Get cash account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getCash(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_CASH);
    }

    /**
     * Get bank account.
     *
     * @param Team $team
     * @return LedgerAccount|null
     */
    public function getBank(Team $team): ?LedgerAccount
    {
        return $this->getDefaultAccount($team, LedgerAccount::SUB_TYPE_BANK);
    }

    /**
     * Get the chart of accounts as a tree structure.
     *
     * @param Team $team
     * @return Collection
     */
    public function getChartOfAccountsTree(Team $team): Collection
    {
        $accounts = LedgerAccount::where('team_id', $team->id)
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return $accounts;
    }
}
