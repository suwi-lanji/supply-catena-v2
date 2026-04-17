<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LedgerAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Account types following standard accounting classification
     */
    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY = 'equity';
    const TYPE_REVENUE = 'revenue';
    const TYPE_EXPENSE = 'expense';

    /**
     * Asset sub-types
     */
    const SUB_TYPE_CURRENT_ASSET = 'current_asset';
    const SUB_TYPE_FIXED_ASSET = 'fixed_asset';
    const SUB_TYPE_INVENTORY = 'inventory';
    const SUB_TYPE_BANK = 'bank';
    const SUB_TYPE_ACCOUNTS_RECEIVABLE = 'accounts_receivable';
    const SUB_TYPE_CASH = 'cash';

    /**
     * Liability sub-types
     */
    const SUB_TYPE_CURRENT_LIABILITY = 'current_liability';
    const SUB_TYPE_LONG_TERM_LIABILITY = 'long_term_liability';
    const SUB_TYPE_ACCOUNTS_PAYABLE = 'accounts_payable';

    /**
     * Equity sub-types
     */
    const SUB_TYPE_CAPITAL = 'capital';
    const SUB_TYPE_RETAINED_EARNINGS = 'retained_earnings';
    const SUB_TYPE_DRAWINGS = 'drawings';

    /**
     * Revenue sub-types
     */
    const SUB_TYPE_SALES = 'sales';
    const SUB_TYPE_OTHER_INCOME = 'other_income';
    const SUB_TYPE_DISCOUNT_RECEIVED = 'discount_received';

    /**
     * Expense sub-types
     */
    const SUB_TYPE_COST_OF_GOODS_SOLD = 'cost_of_goods_sold';
    const SUB_TYPE_OPERATING_EXPENSE = 'operating_expense';
    const SUB_TYPE_DISCOUNT_ALLOWED = 'discount_allowed';
    const SUB_TYPE_OTHER_EXPENSE = 'other_expense';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'code',
        'name',
        'type',
        'sub_type',
        'parent_id',
        'description',
        'is_active',
        'is_system',
        'opening_balance',
        'current_balance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    /**
     * Get the team that owns the account.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the parent account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(LedgerAccount::class, 'parent_id');
    }

    /**
     * Get the journal entry lines for this account.
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'ledger_account_id');
    }

    /**
     * Get the transactions for this account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class, 'ledger_account_id');
    }

    /**
     * Check if the account is a debit account.
     * Debit accounts: Assets and Expenses increase with debit
     *
     * @return bool
     */
    public function isDebitAccount(): bool
    {
        return in_array($this->type, [self::TYPE_ASSET, self::TYPE_EXPENSE]);
    }

    /**
     * Check if the account is a credit account.
     * Credit accounts: Liabilities, Equity, and Revenue increase with credit
     *
     * @return bool
     */
    public function isCreditAccount(): bool
    {
        return in_array($this->type, [self::TYPE_LIABILITY, self::TYPE_EQUITY, self::TYPE_REVENUE]);
    }

    /**
     * Get the normal balance side for this account.
     *
     * @return string 'debit' or 'credit'
     */
    public function getNormalBalance(): string
    {
        return $this->isDebitAccount() ? 'debit' : 'credit';
    }

    /**
     * Calculate the current balance based on all transactions.
     *
     * @return float
     */
    public function calculateBalance(): float
    {
        $debits = $this->transactions()->where('type', 'debit')->sum('amount');
        $credits = $this->transactions()->where('type', 'credit')->sum('amount');

        if ($this->isDebitAccount()) {
            return $this->opening_balance + $debits - $credits;
        }

        return $this->opening_balance + $credits - $debits;
    }

    /**
     * Update the current balance.
     *
     * @return void
     */
    public function updateBalance(): void
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
    }

    /**
     * Scope to get accounts by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all account types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_ASSET,
            self::TYPE_LIABILITY,
            self::TYPE_EQUITY,
            self::TYPE_REVENUE,
            self::TYPE_EXPENSE,
        ];
    }

    /**
     * Get all sub-types for a given type.
     *
     * @param string $type
     * @return array
     */
    public static function getSubTypesForType(string $type): array
    {
        return match ($type) {
            self::TYPE_ASSET => [
                self::SUB_TYPE_CURRENT_ASSET,
                self::SUB_TYPE_FIXED_ASSET,
                self::SUB_TYPE_INVENTORY,
                self::SUB_TYPE_BANK,
                self::SUB_TYPE_ACCOUNTS_RECEIVABLE,
                self::SUB_TYPE_CASH,
            ],
            self::TYPE_LIABILITY => [
                self::SUB_TYPE_CURRENT_LIABILITY,
                self::SUB_TYPE_LONG_TERM_LIABILITY,
                self::SUB_TYPE_ACCOUNTS_PAYABLE,
            ],
            self::TYPE_EQUITY => [
                self::SUB_TYPE_CAPITAL,
                self::SUB_TYPE_RETAINED_EARNINGS,
                self::SUB_TYPE_DRAWINGS,
            ],
            self::TYPE_REVENUE => [
                self::SUB_TYPE_SALES,
                self::SUB_TYPE_OTHER_INCOME,
                self::SUB_TYPE_DISCOUNT_RECEIVED,
            ],
            self::TYPE_EXPENSE => [
                self::SUB_TYPE_COST_OF_GOODS_SOLD,
                self::SUB_TYPE_OPERATING_EXPENSE,
                self::SUB_TYPE_DISCOUNT_ALLOWED,
                self::SUB_TYPE_OTHER_EXPENSE,
            ],
            default => [],
        };
    }
}
