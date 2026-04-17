<?php

namespace App\Filament\Resources\PaymentsReceivedResource\Pages;

use App\Filament\Resources\PaymentsReceivedResource;
use App\Models\PaymentsReceived;
use App\Services\Sales\PaymentReceivedService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPaymentsReceived extends EditRecord
{
    protected static string $resource = PaymentsReceivedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('void')
                ->label('Void Payment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status !== PaymentsReceived::STATUS_VOIDED)
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Void Reason')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(PaymentReceivedService::class);
                    $userId = auth()->id();
                    
                    try {
                        $service->void($this->record, $userId, $data['reason']);
                        
                        Notification::make()
                            ->title('Payment Voided')
                            ->body('The payment has been voided and invoice allocations have been reversed.')
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
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
