<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReceivesResource\Pages;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceives;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Support\Facades\DB;

class PurchaseReceivesResource extends Resource
{
    protected static ?string $model = PurchaseReceives::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Purchases';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
                            ->preload()
                            ->searchable()
                            ->relationship('vendor', 'vendor_display_name')
                            ->required(),
                        Forms\Components\Select::make('purchase_order_number')
                            ->options(function ($get): array {
                                return PurchaseOrder::where('vendor_id', floatval($get('vendor_id')))->where('received', false)->pluck('purchase_order_number', 'id')->toArray();
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Components\Select $component, string $state, $get, $set) {
                                $items = PurchaseOrder::where('id', $get('purchase_order_number'))->pluck('items');
                                $parsed = [];
                                foreach ($items[0] as $item) {
                                    $i = ['item' => $item['item'], 'ordered' => $item['quantity'], 'received' => 0, 'in_transit' => 0, 'quantity_to_receive' => $item['quantity']];
                                    array_push($parsed, $i);
                                }
                                $set('items', $parsed);
                            })
                            ->preload()
                            ->searchable()
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
                            ->native(false)->default(now())
                            ->required(),
                        TableRepeater::make('items')
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
                                    ->required(),
                                Forms\Components\TextInput::make('in_transit')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity_to_receive')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->colStyles([
                                                                    'item' => 'width: 200px',
                                                                ]),
                        Forms\Components\Textarea::make('notes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vendor.vendor_display_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_order_number')
                    ->state(function($record) {
                        $order = PurchaseOrder::where('id', $record->purchase_order_number)->get()->first();
                        return $order->purchase_order_number;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_receive_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPurchaseReceives::route('/'),
            'create' => Pages\CreatePurchaseReceives::route('/create'),
            'edit' => Pages\EditPurchaseReceives::route('/{record}/edit'),
        ];
    }
}
