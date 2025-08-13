<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorCreditResource\Pages;
use App\Models\Bill;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\VendorCredit;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;

class VendorCreditResource extends Resource
{
    protected static ?string $model = VendorCredit::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Purchases';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
                            ->live()
                            ->afterStateHydrated(function (Request $request, $set) {
                                if ($request->input('bill_id')) {
                                    $bill = Bill::where('id', $request->input('bill_id'))->get()->first();
                                    $set('vendor_id', $bill->vendor_id);
                                    $set('order_number', PurchaseOrder::where('id', $bill->order_number)->pluck('id')->first());
                                }
                            })
                            ->relationship('vendor', 'vendor_display_name')
                            ->live(onBlur: true)
                            ->preload()
                            ->searchable()
                            ->required()
                            ->suffixAction(
                                Action::make('Add Vendor')
                                    ->icon('heroicon-o-plus')
                                    ->url(route('filament.dashboard.resources.vendors.create', ['tenant' => Filament::getTenant()]))
                            ),
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('credit_note_number')
                            ->default('DN-0000'.VendorCredit::where('team_id', Filament::getTenant()->id)->count() + 1)
                            ->required(),
                        Forms\Components\TextInput::make('order_number')
                            ->live()
                            ->afterStateHydrated(function (Request $request, $set) {
                                if ($request->input('bill_id')) {
                                    $bill = Bill::where('id', $request->input('bill_id'))->get()->first();
                                    $set('order_number', PurchaseOrder::where('id', $bill->order_number)->pluck('purchase_order_number')->first());
                                }
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('vendor_credit_date')
                            ->native(false)->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('subject'),
                    ]),
                TableRepeater::make('items')
                    ->live()
                    ->afterStateHydrated(function (Request $request, $set) {
                        if ($request->input('bill_id')) {
                            $bill = Bill::where('id', $request->input('bill_id'))->get()->first();
                            $set('items', $bill->items);
                        }
                    })
                    ->live(onBlur: true)
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
                                $set('amount_due', $total);
                            }),
                    ])
                    ->schema([
                        Forms\Components\Select::make('item')
                            ->required()
                            ->options(Item::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $items = [];
                                $obj = Item::where('id', $get('item'))->get()->toArray();
                                $set('rate', $obj[0]['cost_price']);
                                $amount = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $amount);
                            }),
                        Forms\Components\Select::make('account')
                            ->default(0)
                            ->options([0 => 'Cost Of Goods']),
                        Forms\Components\TextInput::make('quantity')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($get, $set) {
                                $amount = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $amount);
                            })
                            ->afterStateUpdated(function ($get, $set) {
                                $amount = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $amount);
                            })
                            ->numeric()
                            ->default(1),
                        Forms\Components\TextInput::make('rate')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->label('Tax (%)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $amount = floatval($get('amount')) - (floatval($get('amount')) * floatval($get('tax')) / 100);

                                $set('amount', $amount);
                            })
                            ->default(0),
                        Forms\Components\TextInput::make('amount')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $amount = floatval($get('quantity')) * floatval($get('rate'));
                                $amount = floatval($get('amount')) - (floatval($get('amount')) * floatval($get('tax')) / 100);

                                $set('amount', $amount);
                            })
                            ->numeric()
                            ->default(0),
                    ])
                    ->colStyles([
                        'item' => 'width:200px',
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('sub_total')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('discount')
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
                        Forms\Components\TextInput::make('total')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $set('amount_due', $get('total'));
                            })
                            ->numeric(),

                    ])
                    ->columns(1),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Hidden::make('amount_due'),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vendor_credit_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit_note_number')

->searchable(),
                Tables\Columns\TextColumn::make('order_number')

->searchable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateDescription('Create vendor credits and apply them to multiple bills when buying stuff from your vendor.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Vendor Credits')
                    ->color('success')
                    ->url(route('filament.dashboard.resources.vendor-credits.create', ['tenant' => Filament::getTenant()]))
                    ->icon('heroicon-m-plus')
                    ->button(),
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
            'index' => Pages\ListVendorCredits::route('/'),
            'create' => Pages\CreateVendorCredit::route('/create'),
            'view' => Pages\ViewVendorCredits::route('/{record}'),
            'edit' => Pages\EditVendorCredit::route('/{record}/edit'),
        ];
    }
}
