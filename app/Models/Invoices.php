<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoices extends Model
{
    use HasFactory;

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

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class)->where('team_id', Filament::getTenant()->id);
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
        return $this->hasOne(SalesOrder::class)->where('team_id', Filament::getTenant()->id);
    }

    public function sales_order(): HasOne
    {
        return $this->hasOne(SalesOrder::class)->where('team_id', Filament::getTenant()->id);
    }
}
