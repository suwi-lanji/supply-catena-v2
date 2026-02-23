<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoicesResource\Pages;
use App\Models\Invoices;
use App\Models\Item;
use App\Models\SalesAccount;
use App\Models\SalesOrder;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Support\Facades\DB;

class InvoicesResource extends Resource
{
    protected static ?string $model = Invoices::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 5;

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
                        Forms\Components\Select::make('type')
                            ->options([
                                'tax' => 'Tax',
                                'proforma' => 'Proforma',
                            ])
                            ->afterStateUpdated(function ($state, $set) {
    if ($state === 'tax') {
        $lastInvoice = Invoices::where('team_id', Filament::getTenant()->id)
            ->where('type', 'tax')
            ->latest()
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strrpos($lastInvoice->invoice_number, '-') + 1);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $set('invoice_number', 'INV-2304' . $nextNumber);
        } else {
            $set('invoice_number', 'INV-2304001');
        }
    } else {
        $lastInvoice = Invoices::where('team_id', Filament::getTenant()->id)
            ->where('type', 'proforma')
            ->latest()
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strrpos($lastInvoice->invoice_number, '-') + 1);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $set('invoice_number', 'PI-2304' . $nextNumber);
        } else {
            $set('invoice_number', 'PI-2304001');
        }
    }
})
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('invoice_number')
                            ->placeholder('Leave blank for auto generation')
                            ->required(),
                        Forms\Components\Select::make('order_number')
                            ->relationship('sales_order', 'sales_order_number')
                            ->required(),
                        Forms\Components\DatePicker::make('invoice_date')->required()->native(false)->default(now()),
                        Forms\Components\Select::make('payment_terms_id')->required()
                            ->relationship('payment_term', 'name')
                            ->createOptionForm([
                                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
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
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')->required(),
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('sales_person_id')->required()
                            ->relationship('sales_person', 'name')
                            ->createOptionForm([
                                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('email')->required(),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('subject'),
                    ]),
                TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->hintActions([
                        Action::make('account')
                            ->form([
                                Forms\Components\Select::make('account')
                                    ->options(SalesAccount::where('team_id', Filament::getTenant()->id)->pluck('name', 'id')),
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
                                        $total += (floatval($item['tax']) / 100 * $total);
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
                                        $total += (floatval($item['tax']) / 100 * $total);
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
                                $sub_total = 0;
                                $vat_total = 0;
                                foreach ($items as $item) {
                                    $sub_total += $item['amount'];
                                    $vat_total += (floatval($item['tax']) / 100 * (floatval($item['quantity']) * floatval($item['rate'])));
                                }

                                $set('sub_total', $sub_total);
                                $total = floatval($sub_total) + floatval($vat_total);
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if ($state) {
                                    $item = Item::find($state);
                                    $set('rate', $item ? $item->selling_price : 0);
                                }
                            })
                            ->preload()
                            ->searchable(),

                        Forms\Components\TextInput::make('quantity')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get('quantity')) * floatval($get('rate'));
                                if (floatval($get('tax')) > 0) {
                                    $total += (floatval($get('tax')) / 100 * $total);
                                }
                                $set('amount', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('rate')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get('quantity')) * floatval($get('rate'));
                                if (floatval($get('tax')) > 0) {
                                    $total += (floatval($get('tax')) / 100 * $total);
                                }
                                $set('amount', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('alternative'),
                        Forms\Components\TextInput::make('discount'),
                        Forms\Components\TextInput::make('weight'),
                        Forms\Components\TextInput::make('lead_time'),
                        Forms\Components\TextInput::make('tax')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $total = floatval($get(['quantity'])) * floatval($get('rate'));
                                if (floatval($get('tax')) > 0) {
                                    $total += (floatval($get('tax')) / 100 * $total);
                                }
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
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('discount')
                            ->default(0)
                            ->afterStateUpdated(function ($get, $set) {
                                $vat_total = 0;
                                $sub_total = 0;
                                foreach ($get('items') as $item) {
                                    $item_subtotal = floatval($item['quantity']) * floatval($item['rate']);
                                    $vat_total += (floatval($item['tax']) / 100 * $item_subtotal);
                                    $sub_total += $item['amount'];
                                }
                                
                                $total = floatval($sub_total);
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
                                $vat_total = 0;
                                $sub_total = 0;
                                foreach ($get('items') as $item) {
                                    $item_subtotal = floatval($item['quantity']) * floatval($item['rate']);
                                    $vat_total += (floatval($item['tax']) / 100 * $item_subtotal);
                                    $sub_total += $item['amount'];
                                }
                                
                                $total = floatval($sub_total);
                                $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                                $total += floatval($get('adjustment'));
                                $set('total', $total);
                                $set('balance_due', $total);
                            })
                            ->numeric(),
                        Forms\Components\TextInput::make('sub_total')
                            ->default(0)
                            ->numeric(),
                        Forms\Components\TextInput::make('total')
                            ->default(0)
                            ->numeric(),
                        Forms\Components\Hidden::make('balance_due'),
                        Forms\Components\TextInput::make('customer_notes'),
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\Repeater::make('terms_and_conditions')
                                    ->schema([
                                        Forms\Components\Textarea::make('terms_and_conditions'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance_due')->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->searchable(),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('balance_due'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoices::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoices::route('/{record}/edit'),
        ];
    }
}
