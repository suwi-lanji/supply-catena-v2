<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use HasFactory;

    protected $table = 'item';

    protected $guarded = [];

    protected $appends = ['stock_on_hand', 'track_inventory', 'allow_negative_stock'];

    /**
     * Get the total stock on hand across all warehouses.
     */
    public function getStockOnHandAttribute(): float
    {
        // If opening_stock is set and no warehouse_items exist, use opening_stock
        $warehouseStock = DB::table('warehouse_items')
            ->where('item_id', $this->id)
            ->sum('quantity');

        return $warehouseStock > 0 ? $warehouseStock : ($this->opening_stock ?? 0);
    }

    /**
     * Check if inventory is tracked for this item.
     */
    public function getTrackInventoryAttribute(): bool
    {
        return (bool) $this->track_inventory_for_this_item;
    }

    /**
     * Get whether negative stock is allowed (default false).
     */
    public function getAllowNegativeStockAttribute(): bool
    {
        return false; // Default to not allowing negative stock
    }

    /**
     * Alias for selling_price.
     */
    public function getSalesPriceAttribute(): float
    {
        return $this->selling_price ?? 0;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sales_account(): HasOne
    {
        return $this->hasOne(SalesAccount::class)->where('team_id', Filament::getTenant()->id);
    }

    public function purchases_account(): hasOne
    {
        return $this->hasOne(PurchasesAccount::class)->where('team_id', Filament::getTenant()->id);
    }

    public function registrant()
    {
        return $this->belongsTo(User::class, 'regr_id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modr_id');
    }

    public function preferred_vendor(): hasOne
    {
        return $this->hasOne(Vendor::class)->where('team_id', Filament::getTenant()->id);
    }

    public function manufucturer(): hasOne
    {
        return $this->hasOne(Manufucturer::class)->where('team_id', Filament::getTenant()->id);
    }

    public function brand(): hasOne
    {
        return $this->hasOne(Brand::class)->where('team_id', Filament::getTenant()->id);
    }

    public function item_groups(): BelongsToMany
    {
        return $this->belongsToMany(ItemGroup::class, 'item_group_item')->where('team_id', Filament::getTenant()->id);
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class)->where('team_id', Filament::getTenant()->id);
    }

    /**
     * Get warehouse items relationship.
     */
    public function warehouseItems()
    {
        return $this->hasMany(WarehouseItem::class, 'item_id');
    }
}
