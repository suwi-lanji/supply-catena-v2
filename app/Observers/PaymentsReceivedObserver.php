<?php

namespace App\Observers;

use App\Models\PaymentsReceived;
use App\Models\Invoices;

class PaymentsReceivedObserver
{
    /**
     * Handle the PaymentsReceived "created" event.
     */
    public function created(PaymentsReceived $paymentsReceived): void
    {
        $jsonData = $paymentsReceived->items;
        foreach($jsonData as $item) {
            if(!Invoices::where('id', $item['invoice_id'])->exists()) {
                throw new \Exception("Something went wrong");
            }
            Invoices::where('id', $item['invoice_id'])->update(['balance_due' => floatval($item['amount_due']) - floatval($item['payment'])]);
            Invoices::where('balance_due', 0)->update(['status' => 'paid']);
        }
    }

    /**
     * Handle the PaymentsReceived "updated" event.
     */
    public function updated(PaymentsReceived $paymentsReceived): void
    {
        $jsonData = $paymentsReceived->items;
        foreach($jsonData as $item) {
            if(!Invoices::where('id', $item['invoice_id'])->exists()) {
                throw new \Exception("Something went wrong");
            }
            Invoices::where('id', $item['invoice_id'])->update(['balance_due' => floatval($item['amount_due']) - floatval($item['payment'])]);
            Invoices::where('balance_due', 0)->update(['status' => 'paid']);
        }
    }

    /**
     * Handle the PaymentsReceived "deleted" event.
     */
    public function deleted(PaymentsReceived $paymentsReceived): void
    {
        $jsonData = $paymentsReceived->items;
        foreach($jsonData as $item) {
            Invoices::where('id', $item['invoice_id'])->update(['balance_due' => $item['amount_due']]);
            Invoices::where('balance_due', '>', 0)->where('status', 'paid')->update(['status' => 'open']);
        }
    }

    /**
     * Handle the PaymentsReceived "restored" event.
     */
    public function restored(PaymentsReceived $paymentsReceived): void
    {
        $jsonData = $paymentsReceived->items;
        foreach($jsonData as $item) {
            Invoices::where('id', $item['invoice_id'])->update(['balance_due' => $item['amount_due'] - $item['payment']]);
        }
        Invoices::where('balance_due', 0)->update(['status' => 'paid']);
    }

    /**
     * Handle the PaymentsReceived "force deleted" event.
     */
    public function forceDeleted(PaymentsReceived $paymentsReceived): void
    {
        $jsonData = $paymentsReceived->items;
        foreach($jsonData as $item) {
            Invoices::where('id', $item['invoice_id'])->update(['balance_due' => $item['amount_due']]);
            Invoices::where('balance_due', '>', 0)->where('status', 'paid')->update(['status' => 'open']);
        }
    }
}
