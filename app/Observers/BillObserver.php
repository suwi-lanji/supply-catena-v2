<?php

namespace App\Observers;

use App\Models\Bill;
use App\Models\PurchaseOrder;
class BillObserver
{
    /**
     * Handle the Bill "created" event.
     */
    public function created(Bill $bill): void
    {
        $bill->update(['balance_due' => $bill->total]);
        if($bill->balance_due == 0) {
            $bill->update(['status', 'paid']);
        }
        $order = PurchaseOrder::where('id', $bill->order_number)->update(['billed' => true]);
    }

    /**
     * Handle the Bill "updated" event.
     */
    public function updated(Bill $bill): void
    {
        if($bill->balance_due == 0) {
            $bill->update(['status', 'paid']);
        }
        $order = PurchaseOrder::where('id', $bill->order_number)->update(['billed' => true]);
    }

    /**
     * Handle the Bill "deleted" event.
     */
    public function deleted(Bill $bill): void
    {
        $order = PurchaseOrder::where('id', $bill->order_number)->update(['billed' => false]);
    }

    /**
     * Handle the Bill "restored" event.
     */
    public function restored(Bill $bill): void
    {
        $order = PurchaseOrder::where('id', $bill->order_number)->update(['billed' => true]);
    }
    /**
     * Handle the Bill "force deleted" event.
     */
    public function forceDeleted(Bill $bill): void
    {
        $order = PurchaseOrder::where('id', $bill->order_number)->update(['billed' => false]);
    }
}
