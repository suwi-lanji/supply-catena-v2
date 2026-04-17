<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;

    const TYPE_DEBIT = 'debit';
    const TYPE_CREDIT = 'credit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'journal_entry_id',
        'ledger_account_id',
        'type',
        'amount',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the journal entry this line belongs to.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Get the ledger account for this line.
     */
    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }

    /**
     * Check if this is a debit line.
     *
     * @return bool
     */
    public function isDebit(): bool
    {
        return $this->type === self::TYPE_DEBIT;
    }

    /**
     * Check if this is a credit line.
     *
     * @return bool
     */
    public function isCredit(): bool
    {
        return $this->type === self::TYPE_CREDIT;
    }

    /**
     * Get the signed amount based on type.
     * Positive for debit, negative for credit.
     *
     * @return float
     */
    public function getSignedAmount(): float
    {
        return $this->type === self::TYPE_DEBIT ? $this->amount : -$this->amount;
    }

    /**
     * Create a debit line.
     *
     * @param int $accountId
     * @param float $amount
     * @param string|null $description
     * @return array
     */
    public static function makeDebit(int $accountId, float $amount, ?string $description = null): array
    {
        return [
            'ledger_account_id' => $accountId,
            'type' => self::TYPE_DEBIT,
            'amount' => $amount,
            'description' => $description,
        ];
    }

    /**
     * Create a credit line.
     *
     * @param int $accountId
     * @param float $amount
     * @param string|null $description
     * @return array
     */
    public static function makeCredit(int $accountId, float $amount, ?string $description = null): array
    {
        return [
            'ledger_account_id' => $accountId,
            'type' => self::TYPE_CREDIT,
            'amount' => $amount,
            'description' => $description,
        ];
    }
}
