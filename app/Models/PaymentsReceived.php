<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentsReceived extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_RECEIVED = 'received';
    public const STATUS_VOIDED = 'voided';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->where('team_id', Filament::getTenant()?->id);
    }

    /**
     * Get the journal entries for this payment.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }
}
