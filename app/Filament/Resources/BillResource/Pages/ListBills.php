<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Closure;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return null;
    }
}
