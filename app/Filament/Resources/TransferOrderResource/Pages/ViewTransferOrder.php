<?php

namespace App\Filament\Resources\TransferOrderResource\Pages;

use App\Filament\Resources\TransferOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Facades\Filament;
use App\Models\Item;
use App\Models\Invoices;
use Filament\Support\Enums\IconPosition;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Forms;
use App\Models\Packages;
use App\Models\SalesOrder;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Illuminate\Database\Eloquent\Model;
use App\Mail\ShipmentMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment;

class ViewTransferOrder extends ViewRecord
{
    protected static string $resource = TransferOrderResource::class;
    protected static string $view = 'filament.resources.transfer-orders.view-transfer-order';
    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make('edit')->color('default'),
            /*Actions\Action::make('email')->color('default')
            ->action(function($record) {
                Mail::to($this->getCustomer()->first()->email)->send(new ShipmentMail($record=$this->getRecord(), $stream=Pdf::loadView('pdf-shipment', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'DOMPDF_ENABLE_HTML5PARSER' => true,
                    'chroot' => public_path(),
                    'fontDir' => storage_path('fonts/'),
                    'isRemoteEnabled' => true,
                    'css' => file_get_contents(public_path('css/purchase-order.css'))
                ])->output(), $filename=$this->getRecord()->invoice_number.'.pdf'));
                Notification::make()
                ->title('Sales Order Sent')
                ->success()
                ->send();
            }),*/
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-transfer-order', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                        ])->stream();
                    }, $record->transfer_order_number.'.pdf');
                }),
            
        ];
    }
}
