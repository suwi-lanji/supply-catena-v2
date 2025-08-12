<?php

namespace App\Observers;

use App\Models\Packages;
use App\Models\SalesOrder;
use App\Models\Shipments;

class ShipmentsObserver
{
    /**
     * Handle the Shipments "created" event.
     */
    public function created(Shipments $shipments): void
    {
        foreach ($shipments->packages as $pid) {
            $package = Packages::find($pid);
            $package->update(['shipped' => true]);
            SalesOrder::find($package->sales_order_number)->update(['shipped' => true]);
            if ($shipments->delivered || $shipments->status == 'Delivered') {
                SalesOrder::find($package->sales_order_number)->update(['delivered' => true]);
                if (! $shipments->delivered) {
                    $shipments->update(['delivered' => true]);
                }
            }

        }
    }

    /**
     * Handle the Shipments "updated" event.
     */
    public function updated(Shipments $shipments): void
    {
        foreach ($shipments->packages as $pid) {
            $package = Packages::find($pid);
            $package->update(['shipped' => true]);
            SalesOrder::find($package->sales_order_number)->update(['shipped' => true]);
            if ($shipments->delivered || $shipments->status == 'Delivered') {
                SalesOrder::find($package->sales_order_number)->update(['delivered' => true]);
                if (! $shipments->delivered) {
                    $shipments->update(['delivered' => true]);
                }
            }

        }

    }

    /**
     * Handle the Shipments "deleted" event.
     */
    public function deleted(Shipments $shipments): void
    {
        if ($shipments->delivered || $shipments->status == 'Delivered') {
            if (! $shipments->delivered) {
                $shipments->update(['delivered' => true]);
            }
            foreach ($shipments->packages as $pid) {
                Packages::find($pid)->update(['shipped' => true]);
                SalesOrder::find(Packages::find($pid)->pluck('sales_order_number'))->update(['shipped' => true]);
            }
        }
    }

    /**
     * Handle the Shipments "restored" event.
     */
    public function restored(Shipments $shipments): void
    {
        if ($shipments->delivered || $shipments->status == 'Delivered') {
            if (! $shipments->delivered) {
                $shipments->update(['delivered' => true]);
            }
        }
    }

    /**
     * Handle the Shipments "force deleted" event.
     */
    public function forceDeleted(Shipments $shipments): void
    {
        if ($shipments->delivered || $shipments->status == 'Delivered') {
            if (! $shipments->delivered) {
                $shipments->update(['delivered' => true]);
            }
        }
    }
}
