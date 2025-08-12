<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceives;
use App\Models\Team;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class PurchaseReceivesObserver
{
    /**
     * Handle the PurchaseReceives "created" event.
     */
    public function created(PurchaseReceives $purchaseReceives): void
    {
        $team = Team::find($purchaseReceives->team_id);
        if ($purchaseReceives->exists) {
            $order = PurchaseOrder::where('id', $purchaseReceives->purchase_order_number)->update(['received' => true]);
            foreach ($purchaseReceives->items as $item) {
                Item::where('id', $item['item'])->increment('stock_on_hand', $item['quantity_to_receive']);
                $item = Item::where('id', $item['item'])->first();
                if (DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->exists()) {
                    DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->increment('quantity', $item['quantity_to_receive']);
                }
            }
        }
    }

    /**
     * Handle the PurchaseReceives "updated" event.
     */
    public function updated(PurchaseReceives $purchaseReceives): void
    {
        $team = Team::find($purchaseReceives->team_id);
        foreach ($purchaseReceives->items as $item) {
            Item::where('id', $item['item'])->increment('stock_on_hand', $item['quantity_to_receive']);
            $item = Item::where('id', $item['item'])->first();
            if (DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->exists()) {
                DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->increment('quantity', $item['quantity_to_receive']);
            }
        }
    }

    /**
     * Handle the PurchaseReceives "deleted" event.
     */
    public function deleted(PurchaseReceives $purchaseReceives): void
    {
        $team = Team::find($purchaseReceives->team_id);
        $order = PurchaseOrder::where('id', $purchaseReceives
            ->purchase_order_number)->update(['received' => false]);
        foreach ($purchaseReceives->items as $item) {
            Item::where('id', $item['item'])->decrement('stock_on_hand', $item['quantity_to_receive']);
            $item = Item::where('id', $item['item'])->first();
            if (DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->exists()) {
                DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->decrement('quantity', $item['quantity_to_receive']);
            }
        }
    }

    /**
     * Handle the PurchaseReceives "restored" event.
     */
    public function restored(PurchaseReceives $purchaseReceives): void
    {
        $team = Team::find($purchaseReceives->team_id);
        $order = PurchaseOrder::where('id', $purchaseReceives
            ->purchase_order_number)->update(['received' => true]);
        foreach ($purchaseReceives->items as $item) {
            Item::where('id', $item['item'])->increment('stock_on_hand', $item['quantity_to_receive']);
            $item = Item::where('id', $item['item'])->first();
            if (DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->exists()) {
                DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->increment('quantity', $item['quantity_to_receive']);
            }
        }
    }

    /**
     * Handle the PurchaseReceives "force deleted" event.
     */
    public function forceDeleted(PurchaseReceives $purchaseReceives): void
    {
        $team = Team::find($purchaseReceives->team_id);
        $order = PurchaseOrder::where('id', $purchaseReceives
            ->purchase_order_number)->update(['received' => false]);
        foreach ($purchaseReceives->items as $item) {
            Item::where('id', $item['item'])->decrement('stock_on_hand', $item['quantity_to_receive']);
            $item = Item::where('id', $item['item'])->first();
            if (DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->exists()) {
                DB::table('warehouse_items')->where('warehouse_id', Warehouse::where('team_id', $team->id)->where('is_primary', true)->pluck('id')->first())->where('item_id', $item['item'])->decrement('quantity', $item['quantity_to_receive']);
            }
        }
    }
}
