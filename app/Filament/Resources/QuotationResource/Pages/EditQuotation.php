<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use App\Services\Sales\QuotationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('convert_to_sales_order')
                ->label('Convert to Sales Order')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'converted')
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(QuotationService::class);

                    try {
                        $salesOrder = $service->convertToSalesOrder($this->record);

                        Notification::make()
                            ->title('Converted Successfully')
                            ->body('The quotation has been converted to a sales order.')
                            ->success()
                            ->send();

                        $this->redirect(\App\Filament\Resources\SalesOrdersResource::getUrl('view', ['record' => $salesOrder]));
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
                ->visible(fn () => $this->record->status !== 'converted'),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Quotation
    {
        $service = app(QuotationService::class);

        return $service->update($record, $data);
    }
}
