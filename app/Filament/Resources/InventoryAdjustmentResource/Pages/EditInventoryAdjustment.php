<?php

namespace App\Filament\Resources\InventoryAdjustmentResource\Pages;

use App\Filament\Resources\InventoryAdjustmentResource;
use App\Services\Inventory\InventoryAdjustmentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInventoryAdjustment extends EditRecord
{
    protected static string $resource = InventoryAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->action(function () {
                    $service = app(InventoryAdjustmentService::class);

                    try {
                        $service->delete($this->record);

                        Notification::make()
                            ->title('Adjustment Deleted')
                            ->body('The inventory adjustment has been deleted and stock has been reversed.')
                            ->warning()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function beforeFill(): void
    {
        Notification::make()
            ->title('Read Only')
            ->body('Inventory adjustments cannot be edited after creation. Delete this adjustment and create a new one if needed.')
            ->warning()
            ->send();
    }
}
