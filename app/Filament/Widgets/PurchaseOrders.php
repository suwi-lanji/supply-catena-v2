<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PurchaseOrders extends BaseWidget
{
    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::where('team_id', Filament::getTenant()->id)
            )
            ->columns([
                TextColumn::make('purchase_order_number'),
                TextColumn::make('reference_number'),
            ]);
    }
}
