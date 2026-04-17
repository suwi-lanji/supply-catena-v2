<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\VendorCredit;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewBillResource extends ViewRecord
{
    protected static string $resource = BillResource::class;

    protected static string $view = 'filament.resources.bills.pages.view-bill';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')->color('success')
                ->color('default'),
            Actions\Action::make('apply_credits')->color('default')
                ->visible(fn () => VendorCredit::where('vendor_id', $this->getRecord()->vendor_id)->exists())
                ->form([
                    Forms\Components\Select::make('vendor_credit')
                        ->options(VendorCredit::where('vendor_id', $this->getRecord()->vendor_id)->where('amount_due', '>', 0)->pluck('credit_note_number', 'id'))
                        ->preload()
                        ->searchable()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($get, $set) {
                            $credits = VendorCredit::where('id', $get('vendor_credit'))->pluck('amount_due')->first();
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
                    $billService = app(\App\Services\Purchases\BillService::class);
                    
                    $credit = VendorCredit::where('id', $data['vendor_credit'])->first();
                    if ($credit) {
                        $new_amount_due = floatval($credit['amount_due']) - floatval($data['amount']);
                        $credit->update(['amount_due' => $new_amount_due]);
                        
                        // Use service to apply payment
                        $billService->applyPayment($this->getRecord(), floatval($data['amount']), $credit->id);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Credits Applied')
                            ->body('Vendor credits have been applied to the bill.')
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
                        echo Pdf::loadView('pdf-bill', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css')),
                        ])->stream();
                    }, $record->bill_number.'.pdf');
                }),
            Actions\Action::make('record_payment')->color('default')
                ->url(route('filament.dashboard.resources.payments-mades.create', ['tenant' => Filament::getTenant(), 'bill_id' => $this->getRecord()->id]))
                ->color('default'),
            Actions\ActionGroup::make([
                Actions\Action::make('void'),
                Actions\Action::make('create_vendor_credits')
                    ->url(route('filament.dashboard.resources.vendor-credits.create', ['tenant' => Filament::getTenant(), 'bill_id' => $this->getRecord()->id])),
                Actions\Action::make('undo_receive'),
            ]),
        ];
    }
}
