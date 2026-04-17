<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\Purchases\PurchaseOrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_received')
                ->label('Mark as Received')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => !$this->record->received && $this->record->status !== 'cancelled')
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(PurchaseOrderService::class);

                    try {
                        $service->markAsReceived($this->record);

                        Notification::make()
                            ->title('Marked as Received')
                            ->body('The purchase order has been marked as received.')
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
            Actions\Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => !in_array($this->record->status, ['received', 'billed', 'cancelled']))
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(PurchaseOrderService::class);

                    try {
                        $service->cancel($this->record);

                        Notification::make()
                            ->title('Order Cancelled')
                            ->body('The purchase order has been cancelled.')
                            ->warning()
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
            Actions\DeleteAction::make()
                ->visible(fn () => in_array($this->record->status, ['draft', 'cancelled'])),
        ];
    }

    protected function handleRecordUpdate($record, array $data): PurchaseOrder
    {
        $service = app(PurchaseOrderService::class);

        return $service->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
