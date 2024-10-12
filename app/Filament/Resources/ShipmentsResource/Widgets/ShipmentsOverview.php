<?php

namespace App\Filament\Resources\ShipmentsResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Shipments;
use Filament\Facades\Filament;
class ShipmentsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 2;
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Total Shipments', Shipments::where('team_id', Filament::getTenant()->id)->count()),
            Stat::make('Delivered Shipments', Shipments::where('team_id', Filament::getTenant()->id)->where('delivered', true)->count())
        ];
    }
}
