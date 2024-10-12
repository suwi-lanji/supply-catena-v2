<?php

namespace App\Filament\Resources\SalesReturnsResource\Pages;

use App\Filament\Resources\SalesReturnsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesReturns extends EditRecord
{
    protected static string $resource = SalesReturnsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
