<?php

namespace App\Filament\Resources\PurchaseReceivesResource\Pages;

use App\Filament\Resources\PurchaseReceivesResource;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseReceives extends ListRecords
{
    protected static string $resource = PurchaseReceivesResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
