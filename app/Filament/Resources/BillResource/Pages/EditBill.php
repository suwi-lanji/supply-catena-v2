<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\Bill;
use App\Services\Purchases\BillService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Bill')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [Bill::STATUS_OPEN, 'open']))
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(BillService::class);
                    $userId = auth()->id();
                    
                    try {
                        $service->approve($this->record, $userId);
                        
                        Notification::make()
                            ->title('Bill Approved')
                            ->body('The bill has been approved and inventory has been updated.')
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
                ->label('Cancel Bill')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => !in_array($this->record->status, [Bill::STATUS_PAID, Bill::STATUS_CANCELLED]))
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Cancellation Reason')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(BillService::class);
                    $userId = auth()->id();
                    
                    try {
                        $service->cancel($this->record, $userId, $data['reason']);
                        
                        Notification::make()
                            ->title('Bill Cancelled')
                            ->body('The bill has been cancelled.')
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
                ->visible(fn () => in_array($this->record->status, [Bill::STATUS_OPEN, 'open'])),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Bill
    {
        $service = app(BillService::class);
        
        return $service->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
