<?php

namespace App\Filament\Resources\SalesReceiptResource\Pages;

use App\Filament\Resources\SalesReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use App\Mail\SalesReceiptMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment;

class ViewSalesReceipt extends ViewRecord
{
    protected static string $resource = SalesReceiptResource::class;
    protected static string $view = 'filament.resources.sales-receipt.view-sales-receipt';
    protected function getCustomer() {
        return Customer::where('id', $this->getRecord()->customer_id)->get();
    }
    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
            ->action(function($record) {
                Mail::to($this->getCustomer()->first()->email)->send(new SalesReceiptMail($record=$this->getRecord(), $stream=Pdf::loadView('pdf-sales-receipt', ['record' => $record])->setOptions([
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'DOMPDF_ENABLE_HTML5PARSER' => true,
                    'chroot' => public_path(),
                    'fontDir' => storage_path('fonts/'),
                    'isRemoteEnabled' => true,
                    'css' => file_get_contents(public_path('css/purchase-order.css'))
                ])->output(), $filename=$this->getRecord()->invoice_number.'.pdf'));
                Notification::make()
                ->title('Sales Receipt Sent')
                ->success()
                ->send();
            }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-sales-receipt', ['record' => $record])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                        ])->stream();
                    }, $record->sales_receipt_number.'.pdf');
                }),
        ];
    }
}
