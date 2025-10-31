<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Item;
use App\Models\Quotation;
use App\Models\SalesAccount;
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

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

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
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('quotation_number')
                            ->default(fn (): string => 'QO-2304'.str_pad(Quotation::where('team_id', Filament::getTenant()->id)->count() + 1, 3, 0, STR_PAD_LEFT)),
                        Forms\Components\TextInput::make('reference_number')
                            ->default('RN-0000'.Quotation::where('team_id', Filament::getTenant()->id)->count() + 1),
                        Forms\Components\TextInput::make('report_number'),
                        Forms\Components\TextInput::make('stock_in'),
                        Forms\Components\DatePicker::make('quotation_date')
                            ->native(false)->default(now())
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if($state) {
                                    $date = \Carbon\Carbon::date($state);
                                    $set('expected_shippment_date', $date->copy()->addDays(30));
                                }
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('expected_shippment_date')
                            ->native(false)
                            ->live(),
                        Forms\Components\Select::make('payment_term_id')
                            ->relationship('payment_term', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
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
                            ->required(),
                        Forms\Components\Select::make('delivery_method_id')
                            ->relationship('delivery_method', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                            ])
                            ->required(),
                        Forms\Components\Select::make('sales_person_id')
                            ->relationship('sales_person', 'name')
                            ->createOptionForm([
                                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('email')->required(),
                            ])
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('inco_term')
                            ->options([
                                'EXW (Ex Works)' => 'EXW (Ex Works)',
                                'FOB (Free on Board)' => 'FOB (Free on Board)',
                                'CIF (Cost, Insurance and Freight)' => 'CIF (Cost, Insurance and Freight)',
                                'DDP MINESITE' => 'DDP MINESITE',
                            ]),
                        Forms\Components\TextInput::make('lead_time'),
                        Forms\Components\TextInput::make('payment_time'),
                    ]),
                TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('items', [])) {
                            $set('items', $request->input('items', []));
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
                                        $total += (floatval($item['tax']) / 100 * $total);
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
                        Forms\Components\TextInput::make('alternative'),

                        Forms\Components\TextInput::make('weight'),
                        Forms\Components\TextInput::make('lead_time'),
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
                Forms\Components\Textarea::make('customer_notes'),
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
                Forms\Components\TextInput::make('sub_total')
                    ->default(0)
                    ->numeric(),
                Forms\Components\TextInput::make('shipment_charges')
                    ->default(0)
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
                Forms\Components\Hidden::make('status')
                    ->default('Send'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Sales Order#')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference #')
                    ->searchable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'view' => Pages\ViewQuotation::route('/{record}'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
