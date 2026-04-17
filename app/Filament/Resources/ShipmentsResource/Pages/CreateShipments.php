<?php

namespace App\Filament\Resources\ShipmentsResource\Pages;

use App\Filament\Resources\ShipmentsResource;
use App\Models\Shipments;
use App\Services\Sales\ShipmentService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShipments extends CreateRecord
{
    protected static string $resource = ShipmentsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(ShipmentService::class);
        $team = Filament::getTenant();

        return $service->create($team, $data);
    }
}
