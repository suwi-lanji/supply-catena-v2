<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountBudget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'fiscal_year_id',
        'ledger_account_id',
        'budgeted_amount',
        'used_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
    ];

    /**
     * Get the team that owns this budget.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the fiscal year for this budget.
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get the ledger account for this budget.
     */
    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    /**
     * Get the remaining budget.
     *
     * @return float
     */
    public function getRemainingAttribute(): float
    {
        return $this->budgeted_amount - $this->used_amount;
    }

    /**
     * Get the percentage used.
     *
     * @return float
     */
    public function getPercentageUsedAttribute(): float
    {
        if ($this->budgeted_amount <= 0) {
            return 0;
        }

        return ($this->used_amount / $this->budgeted_amount) * 100;
    }

    /**
     * Add to the used amount.
     *
     * @param float $amount
     * @return void
     */
    public function addUsed(float $amount): void
    {
        $this->used_amount += $amount;
        $this->save();
    }

    /**
     * Subtract from the used amount.
     *
     * @param float $amount
     * @return void
     */
    public function subtractUsed(float $amount): void
    {
        $this->used_amount = max(0, $this->used_amount - $amount);
        $this->save();
    }
}
