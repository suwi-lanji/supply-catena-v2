<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Models\ItemsSold;
use App\Models\Item;
use Illuminate\Support\Arr;
class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "created" event.
     */
    public function created(SalesOrder $salesOrder): void
    {
        foreach($salesOrder->items as $item) {
            if(ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->exists()) {
                ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->update(['quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            } else {
                ItemsSold::create(['team_id' => $salesOrder->team_id, 'sales_order_id' => $salesOrder->id, 'item_id' => $item['item'], 'quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            }
        }
    }

    /**
     * Handle the SalesOrder "updated" event.
     */
    public function updated(SalesOrder $salesOrder): void
    {
        foreach($salesOrder->items as $item) {
            if(ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->exists()) {
                ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->update(['quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            } else {
                ItemsSold::create(['team_id' => $salesOrder->team_id, 'sales_order_id' => $salesOrder->id, 'item_id' => $item['item'], 'quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            }
        }
    }

    /**
     * Handle the SalesOrder "deleted" event.
     */
    public function deleted(SalesOrder $salesOrder): void
    {
        foreach($salesOrder->items as $item) {
            if(ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->exists()) {
                ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->delete();
            }
        }
    }

    /**
     * Handle the SalesOrder "restored" event.
     */
    public function restored(SalesOrder $salesOrder): void
    {
        foreach($salesOrder->items as $item) {
            if(ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->exists()) {
                ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->update(['quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            } else {
                ItemsSold::create(['team_id' => $salesOrder->team_id, 'sales_order_id' => $salesOrder->id, 'item_id' => $item['item'], 'quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            }
        }
    }

    /**
     * Handle the SalesOrder "force deleted" event.
     */
    public function forceDeleted(SalesOrder $salesOrder): void
    {
        foreach($salesOrder->items as $item) {
            if(ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->exists()) {
                ItemsSold::where('item_id',$item['item'])->where('sales_order_id', $salesOrder->id)->delete();
            }
        }
    }
}
