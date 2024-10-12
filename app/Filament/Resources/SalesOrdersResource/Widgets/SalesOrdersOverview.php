<?php

namespace App\Filament\Resources\SalesOrdersResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SalesOrder;
use Filament\Facades\Filament;
class SalesOrdersOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Total Sales Orders', SalesOrder::where('team_id', Filament::getTenant()->id)->count()),
            Stat::make('Invoices Sales Orders', SalesOrder::where('team_id', Filament::getTenant()->id)->where('invoiced', true)->count())
        ];
    }
}
