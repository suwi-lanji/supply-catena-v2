<?php
namespace App\Observers;

use App\Models\Invoices;
use App\Models\SalesOrder;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Models\Team;
use App\Notifications\DatabaseNotification;
class InvoicesObserver
{
    /**
     * Handle the Invoices "created" event.
     */
    public function created(Invoices $invoices): void
    {
        $this->updateBalanceAndStatus($invoices);
        $recipients = Team::find($invoices->team_id)->users;
        $team = Team::find($invoices->team_id);
        foreach($recipients as $recipient) {
            $recipient->notify(new DatabaseNotification("New invoice created", $invoices->invoice_number . " created successfully", route('filament.dashboard.resources.invoices.view', ["tenant" => $team->id, "record" => $invoices->id]), $team->id));
        }
        DB::transaction(function () use ($invoices) {
            SalesOrder::where('id', $invoices->order_number)
                ->update(['invoiced' => true, 'status' => 'closed']);

            $this->updateStockOnInvoiceItems($invoices, 'decrement');
        });
    }

    /**
     * Handle the Invoices "updated" event.
     */
    public function updated(Invoices $invoices): void
    {
        $this->updateBalanceAndStatus($invoices);

        DB::transaction(function () use ($invoices) {
            SalesOrder::where('id', $invoices->order_number)
                ->update(['invoiced' => true, 'status' => 'closed']);

            $this->updateStockOnInvoiceItems($invoices, 'decrement');
        });
    }

    /**
     * Handle the Invoices "deleted" event.
     */
    public function deleted(Invoices $invoices): void
    {
        DB::transaction(function () use ($invoices) {
            SalesOrder::where('id', $invoices->order_number)
                ->update(['invoiced' => false, 'status' => 'open']);

            $this->updateStockOnInvoiceItems($invoices, 'increment');
        });
    }

    /**
     * Handle the Invoices "restored" event.
     */
    public function restored(Invoices $invoices): void
    {
        DB::transaction(function () use ($invoices) {
            SalesOrder::where('id', $invoices->order_number)
                ->update(['invoiced' => true, 'status' => 'closed']);

            $this->updateStockOnInvoiceItems($invoices, 'decrement');
        });
    }

    /**
     * Handle the Invoices "force deleted" event.
     */
    public function forceDeleted(Invoices $invoices): void
    {
        DB::transaction(function () use ($invoices) {
            SalesOrder::where('id', $invoices->order_number)
                ->update(['invoiced' => false, 'status' => 'open']);

            $this->updateStockOnInvoiceItems($invoices, 'increment');
        });
    }

    /**
     * Update the balance due and status of the invoice without triggering events.
     */
    protected function updateBalanceAndStatus(Invoices $invoices): void
    {
        // Use withoutEvents to prevent triggering the update observer
        $invoices->withoutEvents(function () use ($invoices) {
            $invoices->update(['balance_due' => $invoices->total]);

            if ($invoices->balance_due == 0) {
                $invoices->update(['status' => 'paid']);
            }
        });
    }

    /**
     * Update the stock levels based on invoice items.
     */
    protected function updateStockOnInvoiceItems(Invoices $invoices, string $operation): void
    {
        foreach ($invoices->items as $item) {
            $itemModel = Item::find($item["item"]);

            if ($itemModel) {
                $itemModel->{$operation}("stock_on_hand", Arr::get($item, 'quantity', 0));
            }

            $warehouseId = Arr::get($item, 'source_warehouse', null);
            if ($warehouseId) {
                if (DB::table('warehouse_items')->where('warehouse_id', $warehouseId)->where('item_id', $item["item"])->exists()) {
                    DB::table('warehouse_items')->where('warehouse_id', $warehouseId)->where('item_id', $item["item"])->{$operation}('quantity', Arr::get($item, 'quantity', 0));
                }
            } else {
                if (DB::table('warehouses')->where('team_id', $invoices->team_id)->where('is_primary', true)->exists()) {
                    DB::table('warehouse_items')->where('team_id', $invoices->team_id)->where('item_id', $item["item"])->{$operation}('quantity', Arr::get($item, 'quantity', 0));
                }
            }
        }
    }
}
