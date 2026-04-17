<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\DeliveryMethod;
use App\Models\Item;
use App\Models\PaymentTerm;
use App\Models\PurchaseOrder;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Purchases';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
                            ->relationship('vendor', 'vendor_display_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('Delivery Information')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_street')
                            ->required(),
                        Forms\Components\TextInput::make('delivery_city')
                            ->required(),
                        Forms\Components\TextInput::make('delivery_province')
                            ->required(),
                        Forms\Components\TextInput::make('delivery_country')
                            ->required(),
                        Forms\Components\TextInput::make('delivery_phone')
                            ->tel()
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('purchase_order_number')
                            ->default('PO-0000'.PurchaseOrder::where('team_id', Filament::getTenant()->id)->count() + 1),
                        Forms\Components\TextInput::make('reference_number')
                            ->default('RN-0000'.PurchaseOrder::where('team_id', Filament::getTenant()->id)->count() + 1),
                        Forms\Components\DatePicker::make('purchase_order_date')
                            ->native(false)->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('expected_delivery_date'),
                        Forms\Components\Select::make('payment_terms')
                            ->options(PaymentTerm::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->required()
                            ->suffixAction(
                                Actions\Action::make('create_payment_term')
                                    ->icon('heroicon-o-plus')
                                    ->form([
                                        Forms\Components\TextInput::make('name')->required(),
                                        Forms\Components\Fieldset::make('Payment Term Details')
                                            ->schema([
                                                Forms\Components\TextInput::make('account_type'),
                                                Forms\Components\TextInput::make('bank'),
                                                Forms\Components\TextInput::make('account_name'),
                                                Forms\Components\TextInput::make('account_number'),
                                                Forms\Components\TextInput::make('branch'),
                                                Forms\Components\TextInput::make('swift_code'),
                                                Forms\Components\TextInput::make('branch_number'),

                                            ]),
                                    ])
                                    ->action(function ($data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = PaymentTerm::create($data);

                                        return $created;
                                    })
                            ),
                        Forms\Components\Select::make('shipment_preference')
                            ->required()
                            ->options(DeliveryMethod::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->suffixAction(
                                Actions\Action::make('create_delivery_method')
                                    ->icon('heroicon-o-plus')
                                    ->form([
                                        Forms\Components\TextInput::make('name')->required(),
                                    ])
                                    ->action(function ($data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = DeliveryMethod::create($data);

                                        return $created;
                                    })
                            ),
                    ]),
                TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('items', [])) {
                            $set('items', $request->input('items', []));
                        }
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric(),
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
                Forms\Components\TextInput::make('customer_notes'),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Repeater::make('terms_and_conditions')
                            ->schema([
                                Forms\Components\Textarea::make('terms_and_conditions'),
                            ]),
                    ]),
                Forms\Components\TextInput::make('discount')
                    ->default(0)
                    ->afterStateUpdated(function ($get, $set) {
                        $vat = 0;
                        foreach ($get('items') ?? [] as $item) {
                            $vat += floatval($item['tax'] ?? 0);
                        }
                        $subtotal = floatval($get('sub_total') ?? 0);
                        $total = $subtotal + ($vat / 100 * $subtotal);
                        $discount = floatval($get('discount') ?? 0);
                        $total = $total - ($discount / 100 * $total);
                        $total += floatval($get('adjustment') ?? 0);
                        $set('total', $total);
                    })
                    ->live(onBlur: true)
                    ->numeric(),
                Forms\Components\TextInput::make('shipment_charges')
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('adjustment')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $vat = 0;
                        foreach ($get('items') ?? [] as $item) {
                            $vat += floatval($item['tax'] ?? 0);
                        }
                        $subtotal = floatval($get('sub_total') ?? 0);
                        $total = $subtotal + ($vat / 100 * $subtotal);
                        $discount = floatval($get('discount') ?? 0);
                        $total = $total - ($discount / 100 * $total);
                        $total += floatval($get('adjustment') ?? 0);
                        $set('total', $total);
                    })
                    ->numeric(),
                Forms\Components\TextInput::make('sub_total')
                    ->default(0)
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_order_number')
                    ->label('Purchase Order#')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference #')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\CheckboxColumn::make('received')->disabled(true),
                Tables\Columns\CheckboxColumn::make('billed')->disabled(true),
                Tables\Columns\TextColumn::make('total')
                    ->numeric(),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable(),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
