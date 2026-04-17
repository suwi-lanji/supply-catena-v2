<?php

namespace App\Observers;

use App\Models\Packages;
use App\Models\SalesOrder;

class PackagesObserver
{
    /**
     * Handle the Packages "created" event.
     */
    public function created(Packages $packages): void
    {
        SalesOrder::where('id', $packages->order_number)->update(['packaged' => true]);
    }

    /**
     * Handle the Packages "updated" event.
     */
    public function updated(Packages $packages): void
    {
        SalesOrder::where('id', $packages->order_number)->update(['packaged' => true]);
    }

    /**
     * Handle the Packages "deleted" event.
     */
    public function deleted(Packages $packages): void
    {
        SalesOrder::where('id', $packages->order_number)->update(['packaged' => false]);
    }

    /**
     * Handle the Packages "restored" event.
     */
    public function restored(Packages $packages): void
    {
        SalesOrder::where('id', $packages->order_number)->update(['packaged' => true]);
    }

    /**
     * Handle the Packages "force deleted" event.
     */
    public function forceDeleted(Packages $packages): void
    {
        SalesOrder::where('id', $packages->order_number)->update(['packaged' => false]);
    }
}
