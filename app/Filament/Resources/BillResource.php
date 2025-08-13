<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use App\Models\Item;
use App\Models\PaymentTerm;
use App\Models\PurchaseOrder;
use App\Models\PurchasesAccount;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Purchases';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_id')
                    ->preload()
                    ->searchable()
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('purchase_order_id')) {
                            $set('vendor_id', PurchaseOrder::where('id', $request->input('purchase_order_id'))->pluck('vendor_id')->first());
                        }
                    })
                    ->relationship('vendor', 'vendor_display_name')
                    ->required(),
                Forms\Components\TextInput::make('bill_number')
                    ->readonly()
                    ->default(fn (): string => 'BL-0000'.Bill::where('team_id', Filament::getTenant()->id)->count() + 1)
                    ->required(),
                Forms\Components\Select::make('order_number')
                    ->options(function ($get): array {
                        return PurchaseOrder::where('vendor_id', floatval($get('vendor_id')))->where('billed', false)->pluck('purchase_order_number', 'id')->toArray();
                    })
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Components\Select $component, string $state, $get, $set) {
                        $items = PurchaseOrder::where('id', $get('order_number'))->pluck('items');
                        $set('items', $items[0]);
                    })
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('bill_date')
                    ->native(false)->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->native(false)->default(now())
                    ->required(),
                Forms\Components\Select::make('payment_terms')
                    ->required()
                    ->options(PaymentTerm::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
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
                Forms\Components\TextInput::make('subject'),
                TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('purchase_order_id')) {
                            $set('items', PurchaseOrder::where('id', $request->input('purchase_order_id'))->pluck('items')->toArray()[0]);
                        }
                    })
                    ->hintActions([
                        Action::make('account')
                            ->form([
                                Forms\Components\Select::make('account')
                                    ->options(PurchasesAccount::where('team_id', Filament::getTenant()->id)->pluck('name')),
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
                                $set('balance_due', $total);
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
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $item = Item::find($get('item'));
                                $set('rate', $item->selling_price);

                            }),
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
                        Forms\Components\TextInput::make('amount')
                            ->numeric(),
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
                Forms\Components\TextInput::make('discount')
                    ->default(0)
                    ->afterStateUpdated(function ($get, $set) {
                        $total = floatval($get('sub_total')) + (floatval($vat) / 100 * floatval($get('sub_total')));
                        $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                        $total += floatval($get('adjustment'));
                        $set('total', $total);
                        $set('balance_due', $total);
                    })
                    ->live(onBlur: true)
                    ->numeric(),
                Forms\Components\TextInput::make('adjustment')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $total = floatval($get('sub_total')) + (floatval($vat) / 100 * floatval($get('sub_total')));
                        $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                        $total += floatval($get('adjustment'));
                        $set('total', $total);
                        $set('balance_due', $total);
                    })
                    ->numeric(),
                Forms\Components\TextInput::make('sub_total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bill_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance_due')
                    ->searchable(),
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
                Filter::make('is_paid')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'paid')),
                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('balance_due'),
                    ]),
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
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'view' => Pages\ViewBillResource::route('/{record}'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
