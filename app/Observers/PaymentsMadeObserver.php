<?php

namespace App\Observers;

use App\Models\Bill;
use App\Models\PaymentsMade;

class PaymentsMadeObserver
{
    /**
     * Handle the PaymentsMade "created" event.
     */
    public function created(PaymentsMade $paymentsMade): void
    {
        $jsonData = $paymentsMade->items;
        foreach ($jsonData as $item) {
            Bill::where('bill_number', $item['bill_number'])->update(['balance_due' => $item['amount_due'] - $item['payment']]);
            Bill::where('balance_due', 0)->update(['status' => 'paid']);
        }
    }

    /**
     * Handle the PaymentsMade "updated" event.
     */
    public function updated(PaymentsMade $paymentsMade): void
    {
        $jsonData = $paymentsMade->items;
        foreach ($jsonData as $item) {
            Bill::where('bill_number', $item['bill_number'])->update(['balance_due' => $item['amount_due'] - $item['payment']]);
            Bill::where('balance_due', 0)->update(['status' => 'paid']);
        }
    }

    /**
     * Handle the PaymentsMade "deleted" event.
     */
    public function deleted(PaymentsMade $paymentsMade): void
    {
        $jsonData = $paymentsMade->items;
        foreach ($jsonData as $item) {
            Bill::where('bill_number', $item['bill_number'])->update(['balance_due' => $item['amount_due']]);
            Bill::where('balance_due', '>', 0)->where('status', 'paid')->update(['status' => 'open']);
        }
    }

    /**
     * Handle the PaymentsMade "restored" event.
     */
    public function restored(PaymentsMade $paymentsMade): void
    {
        $jsonData = $paymentsMade->items;
        foreach ($jsonData as $item) {
            Bill::where('bill_number', $item['bill_number'])->update(['balance_due' => $item['amount_due'] - $item['payment']]);
        }
        Bill::where('balance_due', 0)->update(['status' => 'paid']);
    }

    /**
     * Handle the PaymentsMade "force deleted" event.
     */
    public function forceDeleted(PaymentsMade $paymentsMade): void
    {
        $jsonData = $paymentsMade->items;
        foreach ($jsonData as $item) {
            Bill::where('bill_number', $item['bill_number'])->update(['balance_due' => $item['amount_due']]);
            Bill::where('balance_due', '>', 0)->where('status', 'paid')->update(['status' => 'open']);
        }
    }
}
