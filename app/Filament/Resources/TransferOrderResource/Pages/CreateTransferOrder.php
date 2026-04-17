<?php

namespace App\Filament\Resources\TransferOrderResource\Pages;

use App\Filament\Resources\TransferOrderResource;
use App\Models\TransferOrder;
use App\Services\Inventory\TransferOrderService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransferOrder extends CreateRecord
{
    protected static string $resource = TransferOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(TransferOrderService::class);
        $team = Filament::getTenant();

        return $service->create($team, $data);
    }
}
