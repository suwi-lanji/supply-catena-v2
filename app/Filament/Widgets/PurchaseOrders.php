<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Columns\IconColumn;
use App\Models\PurchaseOrder;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Facades\Filament;
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
