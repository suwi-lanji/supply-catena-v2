<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn () => $this->record->status !== \App\Models\JournalEntry::STATUS_DRAFT),
        ];
    }

    protected function authorizeAccess(): void
    {
        if ($this->record->status !== \App\Models\JournalEntry::STATUS_DRAFT) {
            abort(403, 'Only draft journal entries can be edited.');
        }
    }
}
