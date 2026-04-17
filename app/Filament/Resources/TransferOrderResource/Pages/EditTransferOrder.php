<?php

namespace App\Filament\Resources\TransferOrderResource\Pages;

use App\Filament\Resources\TransferOrderResource;
use App\Models\TransferOrder;
use App\Services\Inventory\TransferOrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTransferOrder extends EditRecord
{
    protected static string $resource = TransferOrderResource::class;

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
                    $service = app(TransferOrderService::class);

                    try {
                        $service->markAsDelivered($this->record);

                        Notification::make()
                            ->title('Transfer Completed')
                            ->body('The transfer order has been marked as delivered and inventory has been moved.')
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
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->delivered),
        ];
    }

    protected function handleRecordUpdate($record, array $data): TransferOrder
    {
        $service = app(TransferOrderService::class);

        return $service->update($record, $data);
    }
}
