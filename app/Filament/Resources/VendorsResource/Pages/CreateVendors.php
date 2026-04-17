<?php

namespace App\Filament\Resources\VendorsResource\Pages;

use App\Filament\Resources\VendorsResource;
use App\Services\Purchases\VendorService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateVendors extends CreateRecord
{
    protected static string $resource = VendorsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(VendorService::class);
        $team = Filament::getTenant();

        return $service->create($team, $data);
    }
}
