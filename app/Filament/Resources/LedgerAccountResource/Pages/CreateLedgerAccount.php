<?php

namespace App\Filament\Resources\LedgerAccountResource\Pages;

use App\Filament\Resources\LedgerAccountResource;
use App\Services\Accounting\ChartOfAccountsService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateLedgerAccount extends CreateRecord
{
    protected static string $resource = LedgerAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(ChartOfAccountsService::class);
        return $service->createAccount(Filament::getTenant(), $data);
    }
}
