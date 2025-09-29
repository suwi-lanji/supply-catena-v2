<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Team;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function creating(Team $team): void {}

    public function created(Team $team): void
    {
        $warehouse = Warehouse::create(['team_id' => $team->id, 'name' => $team->name, 'phone' => $team->phone, 'is_primary' => true]);
        foreach (Item::where('team_id', $team->id)->get() as $item) {
            if (! $item->wharehouse_id) {
                $item->update(['warehouse_id' => $warehouse->id]);
                DB::table('warehouse_items')->insert(['warehouse_id' => $warehouse->id, 'team_id' => $team->id, 'item_id' => $item->id, 'quantity' => $item->stock_on_hand]);
            }
        }
    }

    public function updating(Team $team): void {}

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {

        if ($team->has_warehouses) {
            if ($team->warehouses()->count() < 1) {
                $warehouse = Warehouse::create(['team_id' => $team->id, 'name' => $team->name, 'phone' => $team->phone, 'is_primary' => true]);
                foreach (Item::where('team_id', $team->id)->get() as $item) {
                    if (! $item->wharehouse_id) {
                        $item->update(['warehouse_id' => $warehouse->id]);
                        DB::table('warehouse_items')->insert(['warehouse_id' => $warehouse->id, 'team_id' => $team->id, 'item_id' => $item->id, 'quantity' => $item->stock_on_hand]);
                    }
                }
            }
        }
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Team $team): void
    {
        //
    }
}
