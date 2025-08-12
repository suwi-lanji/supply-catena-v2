<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\PurchaseOrder;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class InventorySummary extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getCards(): array
    {
        return [
            Card::make('Quantity in hand', Item::where('team_id', Filament::getTenant()->id)->where('team_id', Filament::getTenant()->id)->sum('stock_on_hand')),
            Card::make('Quantity to be received', function () {
                $total = 0;
                $vat = 0;
                $items = PurchaseOrder::where('received', false)->where('team_id', Filament::getTenant()->id)->pluck('items');
                if (count($items) > 0) {
                    foreach ($items[0] as $item) {
                        $total += $item['quantity'];
                    }
                }

                return $total;
            }),

        ];
    }
}
