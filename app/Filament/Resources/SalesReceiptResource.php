<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReceiptResource\Pages;
use App\Models\Item;
use App\Models\PaymentTerm;
use App\Models\SalesAccount;
use App\Models\SalesReceipt;
use App\Models\Warehouse;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReceiptResource extends Resource
{
    protected static ?string $model = SalesReceipt::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'company_display_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('sales_receipt_number')
                            ->default('SR-0000'.SalesReceipt::where('team_id', Filament::getTenant()->id)->count() + 1),
                        Forms\Components\DatePicker::make('receipt_date')
                            ->required(),
                        Forms\Components\Select::make('payment_term_id')
                            ->label('Payment Terms')
                            ->required()
                            ->options(PaymentTerm::where('team_id', Filament::getTenant()->id)->pluck('name', 'id')),
                    ])->columns(1),

                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('sales_person_id')
                            ->relationship('sales_person', 'name')
                            ->preload()
                            ->searchable(),
                    ]),
                TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->live(onBlur: true)
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('sales_order_id')) {
                            $set('items', SalesOrder::where('id', $request->input('sales_order_id'))->pluck('items')->toArray()[0]);
                        }
                    })
                    ->hintActions([
                        Action::make('account')
                            ->form([
                                Forms\Components\Select::make('account')
                                    ->options(SalesAccount::where('team_id', Filament::getTenant()->id)->pluck('name')),
                            ])
                            ->action(function ($data, $get, $set) {
                                $items = $get('items');
                                $new_items = [];

                                foreach ($items as $item) {
                                    $item['account'] = $data['account'];

                                    array_push($new_items, $item);
                                }

                                $set('items', $new_items);
                            }),
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
                        Action::make('calculate_total')
                            ->action(function ($get, $set) {
                                $items = $get('items');
                                $total = 0;
                                $vat = 0;
                                foreach ($items as $item) {
                                    $total += $item['amount'];
                                    $vat += $item['tax'];
                                }

                                $set('sub_total', $total);
                                $total = floatval($get('sub_total')) + (floatval($vat) / 100 * floatval($get('sub_total')));
                                $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                                $total += floatval($get('adjustment'));
                                $set('total', $total);
                            }),
                    ])
                    ->schema([
                        Forms\Components\Select::make('item')
                            ->options(Item::where('team_id', Filament::getTenant()->id)
                            ->select('id', DB::raw('COALESCE(part_number, name) as part_number_or_name'))
                            ->get()
                            ->pluck('part_number_or_name', 'id')
                            )
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $item = Item::find($get('item'));
                                $set('rate', $item->selling_price);

                            })
                            ->preload()
                            ->searchable(),

                        Forms\Components\TextInput::make('quantity')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('rate')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('discount')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get('quantity')) * floatval($get('rate'));
                                if (floatval($get('discount')) > 0) {
                                    $total -= (floatval($get('discount')) / 100 * $total);
                                }

                                $set('amount', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('tax')
                            ->numeric(),
                        Forms\Components\Select::make('source_warehouse')
                            ->label('Warehouse')
                            ->visible(fn ($get): bool => $get('item') != null)
                            ->live()
                            ->afterStateUpdated(function ($get, $set) {
                                $new_rate = floatval($get('rate')) + floatval(DB::table('warehouse_items')->where('warehouse_id', '=', floatval($get('source_warehouse')))->where('item_id', '=', floatval($get('item')))->pluck('price_adjustment')->first());
                                $set('rate', $new_rate);
                            })
                            ->options(fn ($get) => Warehouse::whereIn('id', DB::table('warehouse_items')->where('item_id', '=', $get('item'))->pluck('warehouse_id'))->pluck('name', 'id')),
                        Forms\Components\TextInput::make('amount'),
                        // ->numeric(),

                    ])
                    ->colStyles([
                        'item' => 'width: 200px;',
                        'account' => 'width: 200px;',
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
                        Forms\Components\TextInput::make('sub_total')
                            ->live(onBlur: true)
                            ->numeric(),
                        Forms\Components\TextInput::make('discount')
                            ->default(0)
                            ->afterStateUpdated(function ($get, $set) {
                                $vat = 0;
                                foreach ($get('items') as $item) {
                                    $vat += $item['tax'];
                                }
                                $total = floatval($get('sub_total')) + (floatval($vat) / 100 * floatval($get('sub_total')));

                                $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                                $total += floatval($get('adjustment'));
                                $set('total', $total);
                            })
                            ->live(onBlur: true)
                            ->numeric(),
                        Forms\Components\TextInput::make('adjustment')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $vat = 0;
                                foreach ($get('items') as $item) {
                                    $vat += $item['tax'];
                                }
                                $total = floatval($get('sub_total')) + (floatval($vat) / 100 * floatval($get('sub_total')));

                                $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                                $total += floatval($get('adjustment'));
                                $set('total', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('shipping_charges')
                            ->default(0)
                            ->numeric(),
                        Forms\Components\TextInput::make('total')
                            ->numeric(),
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Textarea::make('customer_notes'),
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\Repeater::make('terms_and_conditions')
                                    ->schema([
                                        Forms\Components\Textarea::make('terms_and_conditions'),
                                    ]),
                            ]),
                        Forms\Components\Select::make('payment_mode')
                            ->options(['Check', 'Cash', 'Bank Transfer', 'Credit Card', 'Bank Remittance'])
                            ->required(),
                        Forms\Components\Select::make('deposited_to')
                            ->options(['Undeposited Funds', 'Petty Cash', 'Other'])
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->default('RN-0000'.SalesReceipt::where('team_id', Filament::getTenant()->id)->count() + 1),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sales_receipt_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receipt_date')
                    ->date(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReceipts::route('/'),
            'create' => Pages\CreateSalesReceipt::route('/create'),
            'view' => Pages\ViewSalesReceipt::route('/{record}'),
            'edit' => Pages\EditSalesReceipt::route('/{record}/edit'),
        ];
    }
}
