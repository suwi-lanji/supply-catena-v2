<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\Purchases\PurchaseOrderService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(PurchaseOrderService::class);
        $team = Filament::getTenant();

        return $service->create($team, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
