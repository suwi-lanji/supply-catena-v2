<?php

namespace App\Filament\Resources\LedgerAccountResource\Pages;

use App\Filament\Resources\LedgerAccountResource;
use App\Services\Accounting\ChartOfAccountsService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLedgerAccount extends EditRecord
{
    protected static string $resource = LedgerAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->is_system || $this->record->transactions()->exists()),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(ChartOfAccountsService::class);
        return $service->updateAccount($record, $data);
    }
}
