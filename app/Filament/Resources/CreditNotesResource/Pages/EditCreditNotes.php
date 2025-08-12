<?php

namespace App\Filament\Resources\CreditNotesResource\Pages;

use App\Filament\Resources\CreditNotesResource;
use Filament\Resources\Pages\EditRecord;

class EditCreditNotes extends EditRecord
{
    protected static string $resource = CreditNotesResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
