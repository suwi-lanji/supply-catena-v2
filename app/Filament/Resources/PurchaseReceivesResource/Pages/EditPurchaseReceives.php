<?php

namespace App\Filament\Resources\PurchaseReceivesResource\Pages;

use App\Filament\Resources\PurchaseReceivesResource;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseReceives extends EditRecord
{
    protected static string $resource = PurchaseReceivesResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
