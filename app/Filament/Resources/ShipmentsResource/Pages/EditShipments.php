<?php

namespace App\Filament\Resources\ShipmentsResource\Pages;

use App\Filament\Resources\ShipmentsResource;
use Filament\Resources\Pages\EditRecord;

class EditShipments extends EditRecord
{
    protected static string $resource = ShipmentsResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
