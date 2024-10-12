<?php

namespace App\Filament\Resources\SalesOrdersResource\Pages;

use App\Filament\Resources\SalesOrdersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesOrders extends EditRecord
{
    protected static string $resource = SalesOrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
