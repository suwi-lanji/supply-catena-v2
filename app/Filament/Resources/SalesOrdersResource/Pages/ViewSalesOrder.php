<?php

namespace App\Filament\Resources\SalesOrdersResource\Pages;

use App\Filament\Resources\SalesOrdersResource;
use App\Mail\SalesOrderMail;
use App\Models\CreditNotes;
use App\Models\Customer;
use App\Models\Invoices;
use App\Models\Item;
use App\Models\Packages;
use App\Models\SalesOrder;
use App\Models\SalesReturns;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrdersResource::class;

    protected static string $view = 'filament.resources.sales-orders.pages.view-sales-order-1';

    private function backOrderItems(): array
    {
        $items = [];
        foreach ($this->getRecord()->items as $item) {
            $i = Item::where('id', $item['item'])->where('stock_on_hand', '<', $item['quantity']);
            if ($i->exists()) {
                $item['rate'] = $i->get()->first()['cost_price'];
                array_push($items, $item);
            }
        }

        return $items;
    }

    protected function getCustomer()
    {
        return Customer::where('id', $this->getRecord()->customer_id)->get();
    }

    protected function getItemName($id)
    {
        return Item::where('id', $id)->pluck('name');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')->color('default'),
            Actions\Action::make('email')->color('default')
                ->action(function ($record) {
                    Mail::to($this->getCustomer()->first()->email)->send(new SalesOrderMail($record = $this->getRecord(), $stream = Pdf::loadView('pdf-sales-order', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                        'isPhpEnabled' => true,
                        'isHtml5ParserEnabled' => true,
                        'DOMPDF_ENABLE_HTML5PARSER' => true,
                        'chroot' => public_path(),
                        'fontDir' => storage_path('fonts/'),
                        'isRemoteEnabled' => true,
                        'css' => file_get_contents(public_path('css/purchase-order.css')),
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
                        echo Pdf::loadView('pdf-sales-order', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css')),
                        ])->stream();
                    }, $record->sales_order_number.'.pdf');
                }),
            Actions\Action::make('backorder_report')
                ->label('Backorder Report')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        $backorderedItems = $record->calculateBackorderedQuantities();
                        echo Pdf::loadView('pdf-sales-backorder', ['backorderedItems' => $backorderedItems])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css')),
                        ])->stream();
                    }, 'Sales-Back-Order-'.$record->sales_order_number.'.pdf');
                }),
            Actions\Action::make('convert_to_invoice')->label('Convert to Invoice')->color('default')
                ->visible(fn () => ! Invoices::where('order_number', $this->getRecord()->id)->exists())
                ->url(route('filament.dashboard.resources.invoices.create', ['tenant' => Filament::getTenant(), 'sales_order_id' => $this->getRecord()->id])),

            Actions\ActionGroup::make([
                Actions\Action::make('package')
                    ->form([
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\TextInput::make('package_slip')
                                    ->default('PKG-0000'.Packages::where('team_id', Filament::getTenant()->id)->count() + 1),
                                Forms\Components\DatePicker::make('date')
                                    ->native(false)->default(now())
                                    ->required(),
                            ]),
                        TableRepeater::make('items')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($get, $set) {
                                $set('items', SalesOrder::where('id', $this->getRecord()->id)->pluck('items')[0]);
                            })
                            ->hintActions([
                                Action::make('add_quantity_to_all')
                                    ->form([
                                        Forms\Components\TextInput::make('quantity'),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $new_items = [];

                                        foreach ($items as $item) {
                                            $item['quantity'] = $data['quantity'];
                                            $total = floatval($item['quantity']) * floatval($item['rate']);
                                            if (floatval($item['tax']) > 0) {
                                                $total -= (floatval($item['tax']) / 100 * $total);
                                            }
                                            $item['amount'] = $total;
                                            array_push($new_items, $item);
                                        }

                                        $set('items', $new_items);
                                    }),
                                Action::make('add_rate_to_all')
                                    ->form([
                                        Forms\Components\TextInput::make('rate'),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $new_items = [];
                                        foreach ($items as $item) {

                                            $item['rate'] = $data['rate'];
                                            $total = floatval($item['quantity']) * floatval($item['rate']);
                                            if (floatval($item['tax']) > 0) {
                                                $total -= (floatval($item['tax']) / 100 * $total);
                                            }
                                            $item['amount'] = $total;
                                            array_push($new_items, $item);
                                        }

                                        $set('items', $new_items);
                                    }),
                                Action::make('add_tax_to_all')
                                    ->form([
                                        Forms\Components\TextInput::make('tax')
                                            ->label('Tax (%)'),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $new_items = [];
                                        foreach ($items as $item) {

                                            $item['tax'] = $data['tax'];
                                            $total = floatval($item['quantity']) * floatval($item['rate']);
                                            if (floatval($item['tax']) > 0) {
                                                $total -= (floatval($item['tax']) / 100 * $total);
                                            }
                                            $item['amount'] = $total;
                                            array_push($new_items, $item);
                                        }

                                        $set('items', $new_items);
                                    }),
                            ])
                            ->schema([
                                Forms\Components\Select::make('item')
                                    ->options(Item::where('team_id', Filament::getTenant()->id)
                                        ->select('id', DB::raw('COALESCE(part_number, name) as part_number_or_name'))
                                        ->get()
                                        ->pluck('part_number_or_name', 'id')
                                    )
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\TextInput::make('quantity')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->numeric(),
                                Forms\Components\TextInput::make('rate')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->numeric(),
                                Forms\Components\TextInput::make('tax')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->numeric(),
                                Forms\Components\Select::make('source_warehouse')
                                    ->label('Warehouse')
                                    ->visible(fn ($get): bool => $get('item') != null)
                                    ->live()
                                    ->afterStateUpdated(function ($get, $set) {
                                        $new_rate = floatval($get('rate')) + floatval(DB::table('warehouse_items')->where('warehouse_id', '=', floatval($get('source_warehouse')))->where('item_id', '=', floatval($get('item')))->pluck('price_adjustment')->first());
                                        $set('rate', $new_rate);
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->options(fn ($get) => Warehouse::whereIn('id', DB::table('warehouse_items')->where('item_id', '=', $get('item'))->pluck('warehouse_id'))->pluck('name', 'id')),
                                Forms\Components\TextInput::make('amount'),
                            ])
                            ->colStyles([
                                'item' => 'width:170px',
                                'default' => 'margin-bottom: 10px',
                            ])
                            ->reorderable()
                            ->cloneable()
                            ->collapsible()
                            ->defaultItems(1)
                            ->columnSpan('full')
                            ->addable(true)
                            ->addActionLabel('Add Items'),
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\Textarea::make('internal_notes')->columns(1),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $data['team_id'] = $this->getRecord()->team_id;
                        $data['sales_order_number'] = $this->getRecord()->id;
                        $created = Packages::create($data);
                        if ($created) {
                            $this->getRecord()->update(['packaged' => true]);
                        }

                        return $created;
                    }),
                Actions\Action::make('shippment')
                    ->action(function (Model $record) {
                        $package_ids = Packages::where('sales_order_number', $record->id)->pluck('id')->first();
                        if ($package_ids) {
                            return redirect()->route('filament.dashboard.resources.shipments.create', ['tenant' => Filament::getTenant(), 'package_id' => $package_ids, 'customer_id' => $this->getRecord()->customer_id]);
                        }
                    })
                    ->disabled(! $this->getRecord()->packaged),
            ])
                ->label('Create')
                ->button()
                ->icon('heroicon-m-chevron-down')
                ->iconPosition(IconPosition::After)
                ->color('default'),
            Actions\ActionGroup::make([
                Actions\Action::make('create_sales_returns')
                    ->form([
                        Forms\Components\TextInput::make('sales_returns_number')
                            ->default('SR-0000'.SalesReturns::where('team_id', Filament::getTenant()->id)->count() + 1),
                        Forms\Components\DatePicker::make('date')
                            ->native(false)->default(now())
                            ->required(),
                        Forms\Components\Textarea::make('reason'),
                        Forms\Components\Toggle::make('credit_only_goods'),
                        TableRepeater::make('items')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($get, $set) {
                                $set('items', SalesOrder::where('id', $this->getRecord()->id)->pluck('items')[0]);
                            })
                            ->hintActions([
                                Action::make('add_quantity_to_all')
                                    ->form([
                                        Forms\Components\TextInput::make('quantity'),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $new_items = [];

                                        foreach ($items as $item) {
                                            $item['quantity'] = $data['quantity'];
                                            $total = floatval($item['quantity']) * floatval($item['rate']);
                                            if (floatval($item['tax']) > 0) {
                                                $total -= (floatval($item['tax']) / 100 * $total);
                                            }
                                            $item['amount'] = $total;
                                            array_push($new_items, $item);
                                        }

                                        $set('items', $new_items);
                                    }),
                                Action::make('add_rate_to_all')
                                    ->form([
                                        Forms\Components\TextInput::make('rate'),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $new_items = [];
                                        foreach ($items as $item) {

                                            $item['rate'] = $data['rate'];
                                            $total = floatval($item['quantity']) * floatval($item['rate']);
                                            if (floatval($item['tax']) > 0) {
                                                $total -= (floatval($item['tax']) / 100 * $total);
                                            }
                                            $item['amount'] = $total;
                                            array_push($new_items, $item);
                                        }

                                        $set('items', $new_items);
                                    }),
                                Action::make('add_tax_to_all')
                                    ->form([
                                        Forms\Components\TextInput::make('tax')
                                            ->label('Tax (%)'),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $new_items = [];
                                        foreach ($items as $item) {

                                            $item['tax'] = $data['tax'];
                                            $total = floatval($item['quantity']) * floatval($item['rate']);
                                            if (floatval($item['tax']) > 0) {
                                                $total -= (floatval($item['tax']) / 100 * $total);
                                            }
                                            $item['amount'] = $total;
                                            array_push($new_items, $item);
                                        }

                                        $set('items', $new_items);
                                    }),
                            ])
                            ->schema([
                                Forms\Components\Select::make('item')
                                    ->options(Item::where('team_id', Filament::getTenant()->id)
                                        ->select('id', DB::raw('COALESCE(part_number, name) as part_number_or_name'))
                                        ->get()
                                        ->pluck('part_number_or_name', 'id')
                                    )
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\Select::make('account')
                                    ->options(['Advanced Tax', 'Employee Advance']),
                                Forms\Components\TextInput::make('quantity')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->numeric(),
                                Forms\Components\TextInput::make('rate')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->numeric(),
                                Forms\Components\TextInput::make('tax')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->numeric(),
                                Forms\Components\Select::make('source_warehouse')
                                    ->label('Warehouse')
                                    ->visible(fn ($get): bool => $get('item') != null)
                                    ->live()
                                    ->afterStateUpdated(function ($get, $set) {
                                        $new_rate = floatval($get('rate')) + floatval(DB::table('warehouse_items')->where('warehouse_id', '=', floatval($get('source_warehouse')))->where('item_id', '=', floatval($get('item')))->pluck('price_adjustment')->first());
                                        $set('rate', $new_rate);
                                        $total = floatval($get('quantity')) * floatval($get('rate'));
                                        if (floatval($get('tax')) > 0) {

                                            $total -= (floatval($get('discount')) / 100 * $total);
                                        }

                                        $set('amount', $total);
                                    })
                                    ->options(fn ($get) => Warehouse::whereIn('id', DB::table('warehouse_items')->where('item_id', '=', $get('item'))->pluck('warehouse_id'))->pluck('name', 'id')),
                                Forms\Components\TextInput::make('amount'),
                            ])
                            ->colStyles([
                                'item' => 'width:170px',
                                'default' => 'margin-bottom: 10px',
                            ])
                            ->reorderable()
                            ->cloneable()
                            ->collapsible()
                            ->defaultItems(1)
                            ->columnSpan('full')
                            ->addable(true)
                            ->addActionLabel('Add Items'),
                    ])
                    ->action(function ($data, $record) {
                        $data['team_id'] = Filament::getTenant()->id;
                        $data['sales_order_number'] = $this->getRecord()->id;
                        $created = SalesReturns::create($data);

                        if ($created) {
                            $updated = $this->getRecord()->update(['returned' => true]);
                            $total = 0;
                            $vat = 0;
                            foreach ($data['items'] as $item) {
                                $total += $item['amount'];
                            }
                            $credit_note = CreditNotes::create(['customer_id' => $record->customer_id, 'credit_note_number' => 'DN-0000'.CreditNotes::where('team_id', Filament::getTenant()->id)->count() + 1, 'reference_number' => 'RN-0000'.CreditNotes::where('team_id', Filament::getTenant()->id)->count() + 1, 'credit_note_date' => $data['date'], 'items' => $data['items'], 'sub_total' => $total, 'discount' => 0, 'adjustment' => 0, 'total' => $total, 'amount_due' => $total, 'team_id' => Filament::getTenant()->id]);

                            return $updated;
                        }

                        return $created;
                    }),
                Actions\Action::make('void')
                    ->visible(fn ($record): bool => ! $record->status == 'void')
                    ->action(function ($record) {
                        $record->update(['status' => 'void']);
                    }),
                Actions\Action::make('unvoid')
                    ->visible(fn ($record): bool => $record->status == 'void')
                    ->action(function ($record) {
                        $record->update(['status' => 'open']);
                    }),
                Actions\Action::make('backorder')
                    ->url(route('filament.dashboard.resources.purchase-orders.create', ['tenant' => Filament::getTenant(), 'items' => $this->backOrderItems()])),
            ])
                ->color('default'),
        ];
    }
}
