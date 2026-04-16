<?php

namespace App\Filament\Resources\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\InventoryAdjustmentResource;
use App\Models\InventoryAdjustment;
use App\Models\Item;
use App\Services\Inventory\InventoryService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInventoryAdjustment extends CreateRecord
{
    protected static string $resource = InventoryAdjustmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()->id;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $adjustment = InventoryAdjustment::create([
            'team_id' => Filament::getTenant()->id,
            'mode_of_adjustment' => $data['mode_of_adjustment'],
            'reference_number' => $data['reference_number'],
            'date' => $data['date'],
            'account' => $data['account'] ?? null,
            'reason' => $data['reason'] ?? null,
            'description' => $data['description'] ?? null,
            'items' => $data['items'] ?? [],
        ]);

        // Process inventory adjustments using the service
        $service = app(InventoryService::class);
        $this->processAdjustments($service, $adjustment);

        Notification::make()
            ->title('Inventory Adjusted')
            ->body('The inventory has been adjusted successfully.')
            ->success()
            ->send();

        return $adjustment;
    }

    /**
     * Process inventory adjustments.
     */
    protected function processAdjustments(InventoryService $service, InventoryAdjustment $adjustment): void
    {
        $items = $adjustment->items ?? [];

        foreach ($items as $itemData) {
            if (!isset($itemData['name'])) {
                continue;
            }

            $item = Item::find($itemData['name']);
            if (!$item) {
                continue;
            }

            // Mode 0 = Quantity adjustment
            if ($adjustment->mode_of_adjustment == 0) {
                $newQuantity = floatval($itemData['new_quantity_available'] ?? 0);
                
                if ($newQuantity != $item->stock_on_hand) {
                    $service->setStock(
                        $item,
                        $newQuantity,
                        $adjustment->reason ?? 'Inventory adjustment',
                        null // Use default warehouse
                    );
                }
            }
            // Mode 1 = Value adjustment (handled differently if needed)
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
