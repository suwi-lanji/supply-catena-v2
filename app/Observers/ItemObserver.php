<?php

namespace App\Observers;

use Filament\Facades\Filament;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Team;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        $team = Team::find($item->team_id);

        if ($team && $team->has_warehouses) {
            DB::transaction(function () use ($item, $team) {
                $warehouseId = $item->warehouse_id;

                // If no warehouse is associated, find the primary warehouse
                if (!$warehouseId) {
                    $warehouse = Warehouse::where('is_primary', true)
                        ->where('team_id', $team->id)
                        ->first();

                    if ($warehouse) {
                        $warehouseId = $warehouse->id;
                        $item->update(['warehouse_id' => $warehouseId]);
                    }
                }

                // Check if warehouse_items record already exists to avoid duplicates
                $exists = DB::table('warehouse_items')
                    ->where('warehouse_id', $warehouseId)
                    ->where('item_id', $item->id)
                    ->exists();

                if (!$exists) {
                    // Insert warehouse item record only if it doesn't already exist
                    DB::table('warehouse_items')->insert([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $item->id,
                        'team_id' => $item->team_id,
                        'quantity' => $item->stock_on_hand ?? 0,
                    ]);
                }
            });
        }
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        $team = Team::find($item->team_id);

        if ($team && $team->has_warehouses) {
            DB::transaction(function () use ($item, $team) {
                $tenantId = $team->id;
                $warehouseId = $item->warehouse_id ?? Warehouse::where('team_id', $item->team_id)->where('is_primary', true)->value('id');

                // Check if warehouse_items record exists before inserting/updating
                $exists = DB::table('warehouse_items')
                    ->where('warehouse_id', $warehouseId)
                    ->where('team_id', $tenantId)
                    ->where('item_id', $item->id)
                    ->exists();

                if ($exists) {
                    // Update the existing warehouse item
                    DB::table('warehouse_items')
                        ->where('warehouse_id', $warehouseId)
                        ->where('team_id', $tenantId)
                        ->where('item_id', $item->id)
                        ->update(['quantity' => $item->stock_on_hand ?? 0]);
                } else {
                    // Insert the warehouse item only if it doesn't exist
                    DB::table('warehouse_items')->insert([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $item->id,
                        'team_id' => $item->team_id,
                        'quantity' => $item->stock_on_hand ?? 0,
                    ]);
                }
            });
        }
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        $team = Team::find($item->team_id);

        if ($team && $team->has_warehouses) {
            // Optional: Delete warehouse items if necessary
            DB::table('warehouse_items')
                ->where('item_id', $item->id)
                ->delete();
        }
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        // Logic for restoring items can be implemented here
        // For instance, re-inserting into warehouse_items table if needed
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        // Logic for permanently deleting items can be implemented here
        // For instance, making sure the warehouse_items record is permanently removed
    }
}
