<?php

namespace App\Filament\Resources\SalesOrdersResource\Pages;

use App\Filament\Resources\SalesOrdersResource;
use App\Models\SalesOrder;
use App\Services\Sales\SalesOrderService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSalesOrders extends CreateRecord
{
    protected static string $resource = SalesOrdersResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(SalesOrderService::class);
        $team = Filament::getTenant();

        return $service->create($team, $data);
    }
}
