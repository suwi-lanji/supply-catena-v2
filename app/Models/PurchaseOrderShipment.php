<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrderShipment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function purchase_order(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class)->where('team_id', Filament::getTenant()->id);
    }

    public function delivery_method(): HasOne
    {
        return $this->hasOne(DeliveryMethod::class)->where('team_id', Filament::getTenant()->id);
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class)->where('team_id', Filament::getTenant()->id);
    }
}
