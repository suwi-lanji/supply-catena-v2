<?php

namespace App\Filament\Resources\ShipmentsResource\Pages;

use App\Filament\Resources\ShipmentsResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentsResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'delivered' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('delivered', true))
                ->icon('heroicon-m-paper-airplane'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ShipmentsResource\Widgets\ShipmentsOverview::class,
        ];
    }
}
