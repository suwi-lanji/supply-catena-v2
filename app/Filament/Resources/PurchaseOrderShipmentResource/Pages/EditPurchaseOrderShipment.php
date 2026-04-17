<?php

namespace App\Filament\Resources\PurchaseOrderShipmentResource\Pages;

use App\Filament\Resources\PurchaseOrderShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrderShipment extends EditRecord
{
    protected static string $resource = PurchaseOrderShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
