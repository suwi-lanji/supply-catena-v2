<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SalesOrders extends BaseWidget
{
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {

        return $table
            ->query(
                SalesOrder::where('team_id', Filament::getTenant()->id)
            )
            ->columns([
                TextColumn::make('sales_order_number'),
                TextColumn::make('reference_number'),
            ]);
    }
}
