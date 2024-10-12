<?php
namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Item;
use App\Models\ItemsSold;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use InvalidArgumentException;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
class LatestSales extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    // Helper method to get the start of the period based on the provided time frame
    private function getStartOfPeriod(string $period): Carbon
    {
        switch ($period) {
            case 'today':
                return Carbon::now()->startOfDay();
            case 'this_week':
                return Carbon::now()->startOfWeek();
            case 'this_month':
                return Carbon::now()->startOfMonth();
            case 'this_year':
                return Carbon::now()->startOfYear();
            default:
                throw new InvalidArgumentException('Invalid period specified.');
        }
    }

    // Define the table and columns
    public function table(Table $table): Table
    {

        return $table
            ->emptyStateHeading('No sales recorded')
            ->query(ItemsSold::where('team_id', Filament::getTenant()->id))
            ->columns([
                TextColumn::make('item.name')
                    ->searchable()
                    ->label('Item Name'),
                TextColumn::make('quantity')
                    ->label('Total Sold')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
            ])
            ->filters([
                QueryBuilder::make()
                ->constraints([
                    DateConstraint::make('updated_at')
                ])

            ])
            ->defaultSort('quantity', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
            ]);
    }
}
