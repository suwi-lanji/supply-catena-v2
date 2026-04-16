<?php

namespace App\Filament\Resources\PaymentsMadeResource\Pages;

use App\Filament\Resources\PaymentsMadeResource;
use App\Models\PaymentsMade;
use App\Services\Purchases\PaymentMadeService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPaymentsMade extends EditRecord
{
    protected static string $resource = PaymentsMadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('void')
                ->label('Void Payment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status !== PaymentsMade::STATUS_VOIDED)
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Void Reason')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(PaymentMadeService::class);
                    $userId = auth()->id();
                    
                    try {
                        $service->void($this->record, $userId, $data['reason']);
                        
                        Notification::make()
                            ->title('Payment Voided')
                            ->body('The payment has been voided and bill allocations have been reversed.')
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
