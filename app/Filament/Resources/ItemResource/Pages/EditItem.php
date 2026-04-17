<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Services\Inventory\ItemService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): \App\Models\Item
    {
        $service = app(ItemService::class);

        return $service->update($record, $data);
    }
}
