<?php

namespace App\Filament\Resources\CreditNotesResource\Pages;

use App\Filament\Resources\CreditNotesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditNotes extends ListRecords
{
    protected static string $resource = CreditNotesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
