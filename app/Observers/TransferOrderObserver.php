<?php

namespace App\Observers;

use App\Models\TransferOrder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class TransferOrderObserver
{
    /**
     * Handle the TransferOrder "created" event.
     */
    public function created(TransferOrder $transferOrder): void
    {
        if ($transferOrder->delivered) {
            $total_costs = 0;
            foreach ($transferOrder->costs as $cost) {
                $total_costs += $cost;
            }
            $total_costs = $total_costs / count($transferOrder->items);
            foreach ($transferOrder->items as $item) {

                DB::table('warehouse_items')->where('warehouse_id', $transferOrder->source_warehouse_id)->where('item_id', $item['item_name'])->decrement('quantity', $item['transfer_quantity']);
                if (DB::table('warehouse_items')->where('warehouse_id', $transferOrder->destination_warehouse_id)->where('item_id', $item['item_name'])->exists()) {
                    DB::table('warehouse_items')->where('warehouse_id', $transferOrder->destination_warehouse_id)->where('item_id', $item['item_name'])->increment('quantity', $item['transfer_quantity']);
                    DB::table('warehouse_items')->where('warehouse_id', $transferOrder->destination_warehouse_id)->where('item_id', $item['item_name'])->update(['price_adjustment' => $total_costs, 'cost_adjustment' => $total_costs]);
                } else {
                    DB::table('warehouse_items')->insert([
                        'warehouse_id' => $transferOrder->destination_warehouse_id,
                        'item_id' => $item['item_name'],
                        'quantity' => $item['transfer_quantity'],
                        'price_adjustment' => $total_costs,
                        'cost_adjustment' => $total_costs,
                        'team_id' => Filament::getTenant()->id,
                    ]);
                }

            }
        }
    }

    /**
     * Handle the TransferOrder "updated" event.
     */
    public function updated(TransferOrder $transferOrder): void
    {
        if ($transferOrder->delivered) {
            $total_costs = 0;
            foreach ($transferOrder->costs as $cost) {
                $total_costs += $cost;
            }
            foreach ($transferOrder->items as $item) {
                DB::table('warehouse_items')->where('warehouse_id', $transferOrder->source_warehouse_id)->where('item_id', $item['item_name'])->decrement('quantity', $item['transfer_quantity']);
                if (DB::table('warehouse_items')->where('warehouse_id', $transferOrder->destination_warehouse_id)->where('item_id', $item['item_name'])->exists()) {
                    DB::table('warehouse_items')->where('warehouse_id', $transferOrder->destination_warehouse_id)->where('item_id', $item['item_name'])->increment('quantity', $item['transfer_quantity']);
                    DB::table('warehouse_items')->where('warehouse_id', $transferOrder->destination_warehouse_id)->where('item_id', $item['item_name'])->update(['price_adjustment' => $total_costs, 'cost_adjustment' => $total_costs]);
                } else {
                    DB::table('warehouse_items')->insert([
                        'warehouse_id' => $transferOrder->destination_warehouse_id,
                        'item_id' => $item['item_name'],
                        'quantity' => $item['transfer_quantity'],
                        'price_adjustment' => $total_costs,
                        'cost_adjustment' => $total_costs,
                    ]);
                }

            }
        }
    }

    /**
     * Handle the TransferOrder "deleted" event.
     */
    public function deleted(TransferOrder $transferOrder): void {}

    /**
     * Handle the TransferOrder "restored" event.
     */
    public function restored(TransferOrder $transferOrder): void {}

    /**
     * Handle the TransferOrder "force deleted" event.
     */
    public function forceDeleted(TransferOrder $transferOrder): void {}
}
