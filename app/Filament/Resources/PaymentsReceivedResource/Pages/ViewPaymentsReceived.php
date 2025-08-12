<?php

namespace App\Filament\Resources\PaymentsReceivedResource\Pages;

use App\Filament\Resources\PaymentsReceivedResource;
use App\Mail\PaymentMail;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class ViewPaymentsReceived extends ViewRecord
{
    protected static string $resource = PaymentsReceivedResource::class;

    protected static string $view = 'filament.resources.payments-received.pages.view-payments-received';

    protected function getCustomer()
    {
        return Customer::where('id', $this->getRecord()->customer_id)->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
                ->action(function ($record) {
                    Mail::to($this->getCustomer()->first()->email)->send(new PaymentMail($record = $this->getRecord(), $stream = Pdf::loadView('pdf-payments-received', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
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
                        echo Pdf::loadView('pdf-payments-received', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
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
