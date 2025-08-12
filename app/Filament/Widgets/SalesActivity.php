<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SalesActivity extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Sales Activity';

    protected function getCards(): array
    {
        return [
            Card::make('Quantity to be packed', SalesOrder::where('packaged', false)->where('team_id', Filament::getTenant()->id)->count()),
            Card::make('Quantity to be shipped', SalesOrder::where('packaged', true)->where('team_id', Filament::getTenant()->id)->where('shipped', false)->count()),
            Card::make('Quantity to be delivered', SalesOrder::where('packaged', true)->where('team_id', Filament::getTenant()->id)->where('shipped', true)->where('delivered', false)->count()),
            Card::make('Quantity to be invoiced', SalesOrder::where('invoiced', false)->where('team_id', Filament::getTenant()->id)->count()),
        ];
    }
}
