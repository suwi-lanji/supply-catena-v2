<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Item;
use App\Models\ItemGroup;
use Filament\Facades\Filament;
class ProductDetails extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = 'Sales Activity';
    protected function getColumns(): int
    {
       return 3;
    }
    protected function getCards(): array
    {
        return [
            Card::make('Low Stock Items', function () {
                $items = Item::where('team_id', Filament::getTenant()->id)->get();
                $total = 0;
                        $vat = 0;
                foreach($items as $item) {
                    if(floatval($item['reorder_level']) >= floatval($item['stock_on_hand'])) {
                        $total += 1;
                    }
                }
                return $total;
            }),
            Card::make('All Item Groups', ItemGroup::where('team_id', Filament::getTenant()->id)->count()),
            Card::make('All Items', Item::where('team_id', Filament::getTenant()->id)->count())
        ];
    }
}
