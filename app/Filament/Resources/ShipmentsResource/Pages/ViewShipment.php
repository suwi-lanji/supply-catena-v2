<?php

namespace App\Filament\Resources\ShipmentsResource\Pages;

use App\Filament\Resources\ShipmentsResource;
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
use App\Models\Shipments;
class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentsResource::class;
    protected static string $view = 'filament.resources.shipments.pages.view-shipment';
    protected function getCustomer() {
        return Customer::where('id', $this->getRecord()->customer_id)->get();
    }
    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
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
            }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-shipment', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                        ])->stream();
                    }, $record->shipment_order_number.'.pdf');
                }),
            Actions\Action::make('Update Shipment Status')
            ->color('default')
            ->icon('heroicon-o-cog-8-tooth')
            ->form([
                Forms\Components\Select::make('status')
                ->options([
                    'Shipped' => 'Shipped','In Transit' => 'In Transit','Out For Delivery' => 'Out For Delivery','Failed Delivery Attempt' => 'Failed Delivery Attempt','Customs Clearance' => 'Customs Clearance','Ready For Pickup' => 'Ready For Pickup','Delayed' => 'Delayed','Delivered' => 'Delivered','Delivered To PO' => 'Delivered to PO','White Glove Delivery' => 'White Glove Delivery','Delivered From Pickup point' => 'Delivered From Pickup point'
                ]),
                Forms\Components\Textarea::make('notes')
            ])
            ->action(function($data, $record) {
                $updated = Shipments::where("id", $record->id)->update($data);
                $shipment = Shipments::find($record->id);
                foreach($shipment->packages as $pid) {
                    $package = Packages::find($pid);
                    $package->update(["shipped" => true]);
                    SalesOrder::find($package->sales_order_number)->update(["shipped" =>true]);
                    if($shipment->delivered || $shipment->status=="Delivered") {
                        SalesOrder::find($package->sales_order_number)->update(["delivered" =>true]);
                        if(!$shipment->delivered) {
                            $shipment->update(["delivered" =>true]);
                        }
                        foreach(Packages::find($pid)->items as $item) {
                            Item::find($item["item"])->decrement("stock_on_hand", $item["quantity"]);
                        }    
                    }
                     
                }
            }),
        ];
    }
}
