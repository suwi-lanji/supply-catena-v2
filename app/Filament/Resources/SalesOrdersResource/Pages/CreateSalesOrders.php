<?php

namespace App\Filament\Resources\SalesOrdersResource\Pages;

use App\Filament\Resources\SalesOrdersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesOrders extends CreateRecord
{
    protected static string $resource = SalesOrdersResource::class;
}
