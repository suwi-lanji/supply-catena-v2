<?php

namespace App\Filament\Resources\SalesReturnsResource\Pages;

use App\Filament\Resources\SalesReturnsResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesReturns extends ListRecords
{
    protected static string $resource = SalesReturnsResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
