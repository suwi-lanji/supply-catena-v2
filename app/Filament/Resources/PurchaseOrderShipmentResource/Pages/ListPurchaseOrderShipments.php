<?php

namespace App\Filament\Resources\PurchaseOrderShipmentResource\Pages;

use App\Filament\Resources\PurchaseOrderShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrderShipments extends ListRecords
{
    protected static string $resource = PurchaseOrderShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
