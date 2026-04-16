<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoices extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'terms_and_conditions' => 'array',
        ];
    }

    protected $guarded = [];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->where('team_id', Filament::getTenant()?->id);
    }

    public function payment_term(): HasOne
    {
        return $this->hasOne(PaymentTerm::class)->where('team_id', Filament::getTenant()->id);
    }

    public function sales_person(): HasOne
    {
        return $this->hasOne(SalesPerson::class)->where('team_id', Filament::getTenant()->id);
    }

    public function order_number(): HasOne
    {
        return $this->hasOne(SalesOrder::class, 'order_number')->where('team_id', Filament::getTenant()->id);
    }

    public function sales_order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_number')->where('team_id', Filament::getTenant()->id);
    }

    /**
     * Get the invoice items.
     */
    public function items()
    {
        return $this->hasMany(ItemsSold::class, 'invoice_id');
    }

    /**
     * Get the journal entries for this invoice.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }
}
