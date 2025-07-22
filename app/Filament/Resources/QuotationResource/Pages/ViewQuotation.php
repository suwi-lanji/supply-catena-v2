<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Facades\Filament;
use App\Models\Item;
use App\Models\Invoices;
use App\Models\CreditNotes;
use Filament\Support\Enums\IconPosition;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Forms;
use App\Models\Packages;
use App\Models\PaymentsReceived;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\Shipments;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\SalesReturns;
use App\Mail\QuotationMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\SalesAccount;
class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;
    protected static string $view = 'filament.resources.quotations.view-quotation';

    protected function getCustomer() {
        return Customer::where('id', $this->getRecord()->customer_id)->get();
    }

    protected function getItemName($id) {
        return Item::where('id', $id)->pluck('name');
    }
    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
            ->form([
                Forms\Components\Select::make('global_template')
                ->label('Use Platform Template')
                ->options(DB::table('global_templates')->where('document_type', 'quotation')->get()->pluck('name', 'id')),
                Forms\Components\Select::make('custom_template')
                ->label('Use Custom Template')
                ->options(DB::table('templates')->where('document_type', 'quotation')->get()->pluck('template_name', 'id'))
            ])
            ->action(function($record, $data) {

                if($data['global_template'] != null) {
                    $template = DB::table('global_templates')->find($data['global_template'])->template;
                    session()->put('pdf_template_html', $template);
                    session()->put('document_id', $record->id);
                    session()->put('tenant_id', Filament::getTenant()->id);
                    session()->put('document_type', 'quotation');
                    session()->put('template_type', 'global');
                    return redirect()->route('generate-pdf');
                } else {
                    $template = DB::table('templates')->find($data['custom_template'])->template;
                    session()->put('pdf_template_html', $template);
                    session()->put('document_id', $record->id);
                    session()->put('tenant_id', Filament::getTenant()->id);
                    session()->put('document_type', 'quotation');
                    session()->put('template_type', 'custom');
                    return redirect()->route('generate-pdf');
                }
                Notification::make()
                ->title('Quotation Sent')
                ->success()
                ->send();
            }),
            Actions\Action::make('generate')
            ->color('success')
            ->action(function(Model $record) {
                $salesOrder = SalesOrder::create(['customer_id' => $record->customer_id, 'sales_order_number' => 'SO-000'.SalesOrder::where('team_id', Filament::getTenant()->id)->count() + 1, 'reference_number' => 'RN-000'.SalesOrder::where('team_id', Filament::getTenant()->id)->count() + 1, 'sales_order_date' => $record->quotation_date, 'expected_shippment_date' => $record->expected_shippment_date, 'payment_term_id' => $record->payment_term_id, 'delivery_method_id' => $record->delivery_method_id, 'sales_person_id' => $record->sales_person_id, 'items' => $record->items, 'customer_notes' => $record->customer_notes, 'terms_and_conditions' => $record->terms_and_conditions, 'discount' => $record->discount, 'adjustment' => $record->adjustment, 'sub_total' => $record->sub_total, 'total' => $record->total, 'status' => $record->status, 'team_id' => Filament::getTenant()->id, 'packaged' => true]);
                $invoice = Invoices::create(['customer_id' => $record->customer_id, 'invoice_number' => 'INV-000'.Invoices::where('team_id', Filament::getTenant()->id)->count()+1, 'order_number' => $salesOrder->id, 'invoice_date' => $record->quotation_date, 'payment_terms_id' => $record->payment_term_id, 'due_date' => $record->expected_shippment_date !=null ? $record->expected_shippment_date : $record->quotation_date, 'sales_person_id' => $record->sales_person_id, 'items' => $record->items, 'customer_notes' => $record->customer_notes, 'terms_and_conditions' => $record->terms_and_conditions, 'discount' => $record->discount, 'adjustment' => $record->adjustment, 'sub_total' => $record->sub_total, 'total' => $record->total, 'status' => $record->status, 'team_id' => Filament::getTenant()->id]);
                $package = Packages::create(['team_id' => Filament::getTenant()->id, 'sales_order_number' => $salesOrder->id, 'package_slip' => 'PKG-000'.Packages::where('team_id', Filament::getTenant()->id)->count() + 1, 'date' => $record->expected_shippment_date !=null ? $record->expected_shippment_date : $record->quotation_date, 'items' => $record->items]);
                $ps = array();
                array_push($ps, $package->id);
                $shipment = Shipments::create([
                    'team_id' => Filament::getTenant()->id, // Current tenant's ID
                    'customer_id' => $record->customer_id, // Customer ID from the current record
                    'packages' => $ps, // Packages
                    // Generate a unique shipment order number
                    'shipment_order_number' => 'SHP-000' . (Shipments::where('team_id', Filament::getTenant()->id)->count() + 1),
                    'shipment_date' => $record->expected_shippment_date !=null ? $record->expected_shippment_date : $record->quotation_date,
                    'delivery_method_id' => $record->delivery_method_id, // Delivery method ID
                    'delivered' => false // Shipment is not delivered yet
                ]);
            }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-quotation', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                                                                                                                                  
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css'))
                        ])->stream();
                    }, $record->quotation_number.'.pdf');
                })
        ];
    }
}
