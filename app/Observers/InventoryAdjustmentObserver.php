<?php

namespace App\Observers;

use App\Models\InventoryAdjustment;
use App\Models\Item;
use App\Models\Team;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentObserver
{
    /**
     * Handle the InventoryAdjustment "created" event.
     */
    public function created(InventoryAdjustment $inventoryAdjustment): void
    {
        $team = Team::find($inventoryAdjustment->team_id);
        if ($inventoryAdjustment->exists) {
            $jsonData = $inventoryAdjustment->items;
            $adjustment = $inventoryAdjustment->mode_of_adjustment;
            \Log::info('Items data: ', ['items' => $jsonData]);
            $tenant = Team::find($inventoryAdjustment->team_id);
            foreach ($jsonData as $item) {
                if ($adjustment == 0) {
                    Item::where('id', $item['name'])->update(['stock_on_hand' => $item['new_quantity_available']]);

                    $amount = floatval($item['new_quantity_available']) - floatval($item['quantity_available']);
                    $warehouse_id = Item::where('id', $item['name'])->pluck('warehouse_id')->first();
                    if ($team->has_warehouses) {
                        if (! $warehouse_id) {
                            $stmt = DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('is_primary', true)->pluck('id')->first())->where('item_id', $item['name'])->increment('quantity', $amount);
                        } else {
                            $stmt = DB::table('warehouse_items')->where('warehouse_id', $warehouse_id)->where('item_id', $item['name'])->increment('quantity', $amount);
                        }
                    }

                } else {
                    $i = Item::where('id', $item['name'])->get()->first();
                    $i->update(['cost_price' => $i->cost_price + ($i->cost_price * ($item['value_adjusted'] / $item['new_value']))]);
                }
            }
        }
    }

    /**
     * Handle the InventoryAdjustment "updated" event.
     */
    public function updated(InventoryAdjustment $inventoryAdjustment): void
    {
        $team = Team::find($inventoryAdjustment->team_id);
        if ($inventoryAdjustment->exists) {
            $jsonData = $inventoryAdjustment->items;
            $adjustment = $inventoryAdjustment->mode_of_adjustment;
            foreach ($jsonData as $item) {
                if ($adjustment == 0) {
                    Item::where('id', $item['name'])->update(['stock_on_hand' => $item['new_quantity_available']]);

                    $amount = floatval($item['new_quantity_available']) - floatval($item['quantity_available']);
                    $warehouse_id = Item::where('id', $item['name'])->pluck('warehouse_id')->first();
                    if ($team->has_warehouses) {
                        if (! $warehouse_id) {
                            $stmt = DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('is_primary', true)->pluck('id')->first())->where('item_id', $item['name'])->increment('quantity', $amount);
                        } else {
                            $stmt = DB::table('warehouse_items')->where('warehouse_id', $warehouse_id)->where('item_id', $item['name'])->increment('quantity', $amount);
                        }
                    }

                } else {
                    $i = Item::where('id', $item['name'])->get()->first();
                    $i->update(['cost_price' => $i->cost_price + ($i->cost_price * ($item['value_adjusted'] / $item['new_value']))]);
                }
            }
        }
    }

    /**
     * Handle the InventoryAdjustment "deleted" event.
     */
    public function deleted(InventoryAdjustment $inventoryAdjustment): void
    {
        $team = Team::find($inventoryAdjustment->team_id);
        if ($inventoryAdjustment->exists) {
            $jsonData = $inventoryAdjustment->items;
            $adjustment = $inventoryAdjustment->mode_of_adjustment;
            foreach ($jsonData as $item) {
                Item::where('id', $item['name'])->update(['stock_on_hand' => $item['quantity_available']]);
            }
        }
    }

    /**
     * Handle the InventoryAdjustment "restored" event.
     */
    public function restored(InventoryAdjustment $inventoryAdjustment): void
    {
        $team = Team::find($inventoryAdjustment->team_id);
        if ($inventoryAdjustment->exists) {
            $jsonData = $inventoryAdjustment->items;
            $adjustment = $inventoryAdjustment->mode_of_adjustment;
            foreach ($jsonData as $item) {
                if ($adjustment == 0) {
                    Item::where('id', $item['name'])->update(['stock_on_hand' => $item['new_quantity_available']]);
                } else {
                    $i = Item::where('id', $item['name'])->get()->first();
                    $i->update(['cost_price' => $i->cost_price + ($i->cost_price * ($item['value_adjusted'] / $item['new_value']))]);
                }
            }
        }
    }

    /**
     * Handle the InventoryAdjustment "force deleted" event.
     */
    public function forceDeleted(InventoryAdjustment $inventoryAdjustment): void
    {
        $team = Team::find($inventoryAdjustment->team_id);
        if ($inventoryAdjustment->exists) {
            $jsonData = $inventoryAdjustment->items;
            $adjustment = $inventoryAdjustment->mode_of_adjustment;
            foreach ($jsonData as $item) {
                Item::where('id', $item['name'])->update(['stock_on_hand' => $item['quantity_available']]);
            }
        }
    }
}
