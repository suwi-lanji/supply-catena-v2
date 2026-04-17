<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use App\Services\Accounting\JournalEntryService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $lines = $data['lines'] ?? [];
        unset($data['lines']);

        $service = app(JournalEntryService::class);

        return $service->create(Filament::getTenant(), [
            'entry_date' => $data['entry_date'],
            'description' => $data['description'] ?? null,
            'user_id' => auth()->id(),
            'lines' => $lines,
        ]);
    }
}
