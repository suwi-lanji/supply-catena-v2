<?php

namespace App\Observers;

use App\Models\ItemsPurchased;
use App\Models\PurchaseOrder;
use Illuminate\Support\Arr;

class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "created" event.
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            if (ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->exists()) {
                ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->update(['quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            } else {
                ItemsPurchased::create(['team_id' => $purchaseOrder->team_id, 'purchase_order_id' => $purchaseOrder->id, 'item_id' => $item['item'], 'quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            }
        }
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            if (ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->exists()) {
                ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->update(['quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            } else {
                ItemsPurchased::create(['team_id' => $purchaseOrder->team_id, 'purchase_order_id' => $purchaseOrder->id, 'item_id' => $item['item'], 'quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            }
        }
    }

    /**
     * Handle the PurchaseOrder "deleted" event.
     */
    public function deleted(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            if (ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->exists()) {
                ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->delete();
            }
        }
    }

    /**
     * Handle the PurchaseOrder "restored" event.
     */
    public function restored(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            if (ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->exists()) {
                ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->update(['quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            } else {
                ItemsPurchased::create(['team_id' => $purchaseOrder->team_id, 'purchase_order_id' => $purchaseOrder->id, 'item_id' => $item['item'], 'quantity' => Arr::get($item, 'quantity', 0), 'amount' => $item['amount']]);
            }
        }
    }

    /**
     * Handle the PurchaseOrder "force deleted" event.
     */
    public function forceDeleted(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $item) {
            if (ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->exists()) {
                ItemsPurchased::where('item_id', $item['item'])->where('purchase_order_id', $purchaseOrder->id)->delete();
            }
        }
    }
}
