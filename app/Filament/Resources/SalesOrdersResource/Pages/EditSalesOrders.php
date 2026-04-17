<?php

namespace App\Filament\Resources\SalesOrdersResource\Pages;

use App\Filament\Resources\SalesOrdersResource;
use App\Models\SalesOrder;
use App\Services\Sales\SalesOrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSalesOrders extends EditRecord
{
    protected static string $resource = SalesOrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirm')
                ->label('Confirm Order')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['draft', 'open']))
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(SalesOrderService::class);

                    try {
                        $service->confirm($this->record);

                        Notification::make()
                            ->title('Order Confirmed')
                            ->body('The sales order has been confirmed.')
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
                ->visible(fn () => !in_array($this->record->status, ['shipped', 'delivered', 'cancelled']))
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(SalesOrderService::class);

                    try {
                        $service->cancel($this->record);

                        Notification::make()
                            ->title('Order Cancelled')
                            ->body('The sales order has been cancelled.')
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

    protected function handleRecordUpdate($record, array $data): SalesOrder
    {
        $service = app(SalesOrderService::class);

        return $service->update($record, $data);
    }
}
