<?php

namespace App\Filament\Resources\PaymentsMadeResource\Pages;

use App\Filament\Resources\PaymentsMadeResource;
use App\Mail\PaymentMail;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class ViewPaymentsMade extends ViewRecord
{
    protected static string $resource = PaymentsMadeResource::class;

    protected static string $view = 'filament.resources.payments-mades.pages.view-payments-made';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
                ->action(function ($record) {
                    Mail::to(Vendor::where('id', $this->record->vendor_id)->pluck('email')->first())->send(new PaymentMail($record = $this->getRecord(), $stream = Pdf::loadView('pdf-payments-made', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                        'isPhpEnabled' => true,
                        'isHtml5ParserEnabled' => true,
                        'DOMPDF_ENABLE_HTML5PARSER' => true,
                        'chroot' => public_path(),
                        'fontDir' => storage_path('fonts/'),
                        'isRemoteEnabled' => true,
                    ])->output(), $filename = $this->getRecord()->invoice_number.'.pdf'));
                    Notification::make()
                        ->title('Sales Order Sent')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-payment-made', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                        ])->stream();
                    }, $record->payment_number.'.pdf');
                }),
        ];
    }
}
