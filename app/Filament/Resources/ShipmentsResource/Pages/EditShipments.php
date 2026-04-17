<?php

namespace App\Filament\Resources\ShipmentsResource\Pages;

use App\Filament\Resources\ShipmentsResource;
use App\Models\Shipments;
use App\Services\Sales\ShipmentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditShipments extends EditRecord
{
    protected static string $resource = ShipmentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_delivered')
                ->label('Mark as Delivered')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => !$this->record->delivered)
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(ShipmentService::class);

                    try {
                        $service->markAsDelivered($this->record);

                        Notification::make()
                            ->title('Shipment Delivered')
                            ->body('The shipment has been marked as delivered.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Shipments
    {
        $service = app(ShipmentService::class);

        return $service->update($record, $data);
    }
}
