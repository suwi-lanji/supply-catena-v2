<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_OPEN = 'open';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'bill_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    protected $guarded = [];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class)->where('team_id', Filament::getTenant()?->id);
    }

    /**
     * Get the bill items.
     */
    public function items()
    {
        return $this->hasMany(ItemsPurchased::class, 'bill_id');
    }

    /**
     * Get the journal entries for this bill.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }
}
