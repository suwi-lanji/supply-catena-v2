<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_POSTED = 'posted';
    const STATUS_VOIDED = 'voided';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (JournalEntry $entry) {
            if (empty($entry->entry_number)) {
                $entry->entry_number = $entry->generateEntryNumber();
            }
        });
    }

    /**
     * Generate a unique entry number.
     *
     * @return string
     */
    protected function generateEntryNumber(): string
    {
        $prefix = 'JE';
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(6));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get the team that owns the journal entry.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who created this entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who posted this entry.
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get the user who voided this entry.
     */
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Get the reference model (Invoice, Bill, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the journal entry lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    /**
     * Get the transactions created from this entry.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class, 'journal_entry_id');
    }

    /**
     * Check if the entry is balanced (debits = credits).
     *
     * @return bool
     */
    public function isBalanced(): bool
    {
        $debits = $this->lines()->where('type', 'debit')->sum('amount');
        $credits = $this->lines()->where('type', 'credit')->sum('amount');

        return bccomp($debits, $credits, 2) === 0;
    }

    /**
     * Get total debits.
     *
     * @return float
     */
    public function getTotalDebits(): float
    {
        return (float) $this->lines()->where('type', 'debit')->sum('amount');
    }

    /**
     * Get total credits.
     *
     * @return float
     */
    public function getTotalCredits(): float
    {
        return (float) $this->lines()->where('type', 'credit')->sum('amount');
    }

    /**
     * Check if the entry can be posted.
     *
     * @return bool
     */
    public function canBePosted(): bool
    {
        return $this->status === self::STATUS_DRAFT
            && $this->isBalanced()
            && $this->lines()->count() > 0;
    }

    /**
     * Check if the entry can be voided.
     *
     * @return bool
     */
    public function canBeVoided(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    /**
     * Post the journal entry.
     *
     * @param int $userId
     * @return bool
     */
    public function post(int $userId): bool
    {
        if (!$this->canBePosted()) {
            return false;
        }

        return DB::transaction(function () use ($userId) {
            $this->status = self::STATUS_POSTED;
            $this->posted_by = $userId;
            $this->posted_at = now();
            $this->save();

            // Create account transactions for each line
            foreach ($this->lines as $line) {
                AccountTransaction::create([
                    'team_id' => $this->team_id,
                    'ledger_account_id' => $line->ledger_account_id,
                    'journal_entry_id' => $this->id,
                    'journal_entry_line_id' => $line->id,
                    'transaction_date' => $this->entry_date,
                    'type' => $line->type,
                    'amount' => $line->amount,
                    'balance_after' => 0, // Will be calculated
                    'reference_type' => $this->reference_type,
                    'reference_id' => $this->reference_id,
                    'description' => $line->description ?? $this->description,
                ]);

                // Update account balance
                $line->ledgerAccount->updateBalance();
            }

            return true;
        });
    }

    /**
     * Void the journal entry.
     *
     * @param int $userId
     * @param string $reason
     * @return bool
     */
    public function void(int $userId, string $reason): bool
    {
        if (!$this->canBeVoided()) {
            return false;
        }

        return DB::transaction(function () use ($userId, $reason) {
            $this->status = self::STATUS_VOIDED;
            $this->voided_by = $userId;
            $this->voided_at = now();
            $this->void_reason = $reason;
            $this->save();

            // Create reversing transactions
            foreach ($this->lines as $line) {
                $reversalType = $line->type === 'debit' ? 'credit' : 'debit';

                AccountTransaction::create([
                    'team_id' => $this->team_id,
                    'ledger_account_id' => $line->ledger_account_id,
                    'journal_entry_id' => $this->id,
                    'journal_entry_line_id' => $line->id,
                    'transaction_date' => now(),
                    'type' => $reversalType,
                    'amount' => $line->amount,
                    'balance_after' => 0,
                    'reference_type' => $this->reference_type,
                    'reference_id' => $this->reference_id,
                    'description' => "Void: {$this->entry_number} - {$reason}",
                ]);

                // Update account balance
                $line->ledgerAccount->updateBalance();
            }

            return true;
        });
    }

    /**
     * Scope to get entries by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get posted entries.
     */
    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    /**
     * Scope to get entries for a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }
}
