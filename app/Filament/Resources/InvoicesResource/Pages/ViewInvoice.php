<?php

namespace App\Filament\Resources\InvoicesResource\Pages;

use App\Filament\Resources\InvoicesResource;
use App\Mail\InvoiceMail;
use App\Models\CreditNotes;
use App\Models\Customer;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoicesResource::class;

    protected static string $view = 'filament.resources.invoices.pages.view-invoice-1';

    protected function getCustomer()
    {
        return Customer::where('id', $this->getRecord()->customer_id)->get();
    }

    protected function getItemName($id)
    {
        return Item::where('id', $id)->pluck('name')->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
                ->action(function ($record) {
                    Mail::to($this->getCustomer()->first()->email)->send(new InvoiceMail($record = $this->getRecord(), $stream = Pdf::loadView('pdf-invoice', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                        'isPhpEnabled' => true,
                        'isHtml5ParserEnabled' => true,
                        'DOMPDF_ENABLE_HTML5PARSER' => true,
                        'chroot' => public_path(),
                        'fontDir' => storage_path('fonts/'),
                        'isRemoteEnabled' => true,
                    ])->output(), $filename = $this->getRecord()->invoice_number.'.pdf'));
                    Notification::make()
                        ->title('Invoice Sent')
                        ->success();
                }),
            Actions\Action::make('record_payment')->label('Record Payment')->color('default')
                ->visible(fn () => $this->getRecord()->status != 'paid')
                ->url(route('filament.dashboard.resources.payments-receiveds.create', ['tenant' => Filament::getTenant(), 'invoice_id' => $this->getRecord()->id])),
            Actions\Action::make('apply_credits')->color('default')
                ->visible(fn () => CreditNotes::where('customer_id', $this->getRecord()->customer_id)->exists() && $this->getRecord()->status != 'paid')
                ->form([
                    Forms\Components\Select::make('customer_credit')
                        ->options(CreditNotes::where('customer_id', $this->getRecord()->customer_id)->where('amount_due', '>', 0)->pluck('credit_note_number', 'id'))
                        ->preload()
                        ->searchable()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($get, $set) {
                            $credits = CreditNotes::where('id', $get('customer_credit'))->pluck('amount_due')->first();
                            $set('available_credits', $credits);
                        }),
                    Forms\Components\TextInput::make('available_credits')
                        ->default(0)
                        ->disabled(true),
                    Forms\Components\TextInput::make('balance')
                        ->default($this->getRecord()->balance_due)
                        ->disabled(true),
                    Forms\Components\TextInput::make('amount')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($get, $set) {
                            if (floatval($get('amount')) > floatval($get('available_credits'))) {
                                $set('amount', $get('available_credits'));
                            }
                        })
                        ->numeric()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $invoiceService = app(\App\Services\Sales\InvoiceService::class);
                    
                    $credit = CreditNotes::where('customer_id', $this->getRecord()->customer_id)->get()->first();
                    if ($credit) {
                        $new_amount_due = floatval($credit['amount_due']) - floatval($data['amount']);
                        $credit->update(['amount_due' => $new_amount_due]);
                        
                        // Use service to apply payment
                        $invoiceService->applyPayment($this->getRecord(), floatval($data['amount']), $credit->id);
                        
                        Notification::make()
                            ->title('Credits Applied')
                            ->body('Credit notes have been applied to the invoice.')
                            ->success()
                            ->send();
                    }
                }),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-invoice', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                        ])->stream();
                    }, $record->invoice_number.'.pdf');
                }),
        ];
    }
}
