<?php

namespace App\Filament\Resources\PaymentsReceivedResource\Pages;

use App\Filament\Resources\PaymentsReceivedResource;
use App\Models\PaymentsReceived;
use App\Services\Sales\PaymentReceivedService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePaymentsReceived extends CreateRecord
{
    protected static string $resource = PaymentsReceivedResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(PaymentReceivedService::class);
        $team = Filament::getTenant();
        
        return $service->create($team, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
