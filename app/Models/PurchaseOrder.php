<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'terms_and_conditions' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class)->where('team_id', Filament::getTenant()->id);
    }

    public function calculateBackorderedQuantities()
    {
        $backorderedItems = [];
        $packages = PurchaseReceives::where('purchase_order_number', $this->id)->get();
        foreach ($packages as $package) {
            foreach ($package->items as $index => $item) {

                // Retrieve ordered quantity from Package's item JSON field
                $orderedQuantity = $this->items[$index]['quantity'];
                $deliveredQuantity = $item['quantity_to_receive'];

                if ($orderedQuantity > $deliveredQuantity) {
                    array_push($backorderedItems, ['item' => $item['item'], 'purchase_order_id' => $this->id, 'purchase_receives_id' => $package->id, 'backorder_quantity' => $orderedQuantity - $deliveredQuantity]);
                }
            }
        }

        return $backorderedItems;
    }
}
