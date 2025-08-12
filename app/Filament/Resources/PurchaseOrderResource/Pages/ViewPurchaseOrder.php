<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Mail\PurchaseOrderMail;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceives;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-order-resource.pages.view-purchase-order';

    private function getTotal($total, $adjustment, $discount): int
    {
        return ($total - ($total * $discount / 100)) + $adjustment;
    }

    protected function getHeaderActions(): array
    {
        if (! $this->getRecord()->received) {
            Notification::make()
                ->title('Mark Purchase As Received')
                ->body('Purchase order not received click on receive action.')
                ->warning()
                ->send();
        }

        return [
            Action::make('edit')
                ->color('default')
                ->url(route('filament.dashboard.resources.purchase-orders.edit', ['record' => $this->getRecord(), 'tenant' => Filament::getTenant()])),
            Action::make('delete')
                ->color('default')
                ->requiresConfirmation()
                ->action(fn () => $this->getRecord()->delete()),
            Actions\Action::make('email')->color('default')
                ->action(function ($record) {
                    Mail::to(Vendor::where('id', $this->getRecord()->vendor_id)->pluck('email')->first())->send(new PurchaseOrderMail($record = $this->getRecord(), $stream = Pdf::loadView('pdf', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                        'isPhpEnabled' => true,
                        'isHtml5ParserEnabled' => true,
                        'DOMPDF_ENABLE_HTML5PARSER' => true,
                        'chroot' => public_path(),
                        'fontDir' => storage_path('fonts/'),
                        'isRemoteEnabled' => true,
                    ])->output(), $filename = $this->getRecord()->purchase_order_number.'.pdf'));
                    Notification::make()
                        ->title('Purchase Order Sent')
                        ->success()
                        ->send();
                }),
            Action::make('convert_to_bill')
                ->color('sucess')
                ->link()
                ->url(route('filament.dashboard.resources.bills.create', ['tenant' => Filament::getTenant(), 'purchase_order_id' => $this->getRecord()->id]))
                ->label('Convert to Bill')
                ->visible(fn ($record) => ! $this->getRecord()->billed),
            CreateAction::make('receives')
                ->color('default')
                ->label('Receives')
                ->model(PurchaseReceives::class)
                ->form([
                    Forms\Components\Fieldset::make('')
                        ->schema([
                            Forms\Components\Hidden::make('team_id')
                                ->default(Filament::getTenant()->id),
                            Forms\Components\Hidden::make('vendor_id')
                                ->default($this->getRecord()->vendor_id),
                            Forms\Components\Select::make('purchase_order_number')
                                ->options(PurchaseOrder::where('id', $this->getRecord()->id)->pluck('purchase_order_number', 'id'))
                                ->required(),
                        ]),
                    Forms\Components\Fieldset::make('Purchase Receive Information')
                        ->schema([
                            Forms\Components\TextInput::make('purchase_receive_number')
                                ->default(function () {
                                    return 'PR-0000'.PurchaseReceives::where('team_id', Filament::getTenant()->id)->count() + 1;
                                })
                                ->required(),
                            Forms\Components\DatePicker::make('received_date')
                                ->required(),
                            TableRepeater::make('items')
                                ->afterStateHydrated(function (Forms\Components\Repeater $component, array $state, $get, $set) {
                                    $items = [];
                                    foreach ($this->getRecord()->items as $item) {
                                        $i = ['item' => $item['item'], 'ordered' => $item['quantity'], 'received' => 0, 'in_transit' => 0, 'quantity_to_receive' => $item['quantity']];
                                        array_push($items, $i);
                                    }

                                    $set('items', $items);
                                })
                                ->schema([
                                    Forms\Components\Select::make('item')
                                        ->options(Item::where('team_id', Filament::getTenant()->id)
                                            ->select('id', DB::raw('COALESCE(part_number, name) as part_number_or_name'))
                                            ->get()
                                            ->pluck('part_number_or_name', 'id')
                                        )
                                        ->preload()
                                        ->searchable(),
                                    Forms\Components\TextInput::make('ordered')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('received')
                                        ->numeric()
                                        ->default(0)
                                        ->readonly(),
                                    Forms\Components\TextInput::make('in_transit')
                                        ->numeric()
                                        ->default(0)
                                        ->readonly(),
                                    Forms\Components\TextInput::make('quantity_to_receive')
                                        ->numeric()
                                        ->required(),
                                ]),
                            Forms\Components\Textarea::make('notes'),
                        ]),
                ]),

            Actions\Action::make('backorder_report')
                ->label('Backorder Report')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        $backorderedItems = $record->calculateBackorderedQuantities();
                        echo Pdf::loadView('pdf-purchases-backorder', ['backorderedItems' => $backorderedItems])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css')),
                        ])->stream();
                    }, 'Purchases-Back-Order-'.$record->purchase_order_number.'.pdf');
                }),
            Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css')),
                        ])->stream();
                    }, $record->purchase_order_number.'.pdf');
                }),
        ];
    }
}
