<?php

namespace App\Filament\Resources\InvoicesResource\Pages;

use App\Filament\Resources\InvoicesResource;
use App\Models\Invoices;
use App\Services\Sales\InvoiceService;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoices extends EditRecord
{
    protected static string $resource = InvoicesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send')
                ->label('Send Invoice')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [Invoices::STATUS_DRAFT, 'open']))
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(InvoiceService::class);
                    $userId = auth()->id();
                    
                    try {
                        $service->send($this->record, $userId);
                        
                        Notification::make()
                            ->title('Invoice Sent')
                            ->body('The invoice has been sent and inventory has been updated.')
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
                ->label('Cancel Invoice')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => !in_array($this->record->status, [Invoices::STATUS_PAID, Invoices::STATUS_CANCELLED]))
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Cancellation Reason')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(InvoiceService::class);
                    $userId = auth()->id();
                    
                    try {
                        $service->cancel($this->record, $userId, $data['reason']);
                        
                        Notification::make()
                            ->title('Invoice Cancelled')
                            ->body('The invoice has been cancelled.')
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
                ->visible(fn () => in_array($this->record->status, [Invoices::STATUS_DRAFT, 'open'])),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Invoices
    {
        $service = app(InvoiceService::class);
        
        return $service->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
