<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryAdjustmentResource\Pages;
use App\Models\InventoryAdjustment;
use App\Models\Item;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class InventoryAdjustmentResource extends Resource
{
    protected static ?string $model = InventoryAdjustment::class;

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mode_of_adjustment')
                    ->live(onBlur: true)
                    ->options(['Quantity', 'Value'])
                    ->required(),
                Forms\Components\TextInput::make('reference_number')
                    ->default('RN-0000'.InventoryAdjustment::where('team_id', Filament::getTenant()->id)->count() + 1),
                Forms\Components\DatePicker::make('date')
                    ->native(false)->default(now())
                    ->required(),
                Forms\Components\Select::make('account')
                    ->options(['Inventory Asset Account', 'Cost of Goods Sold (COGS) Account', 'Expense Account', 'Income Account', 'Other Asset Account', 'Other Liability Account'])
                    ->required(),
                Forms\Components\Textarea::make('reason'),
                Forms\Components\Textarea::make('description'),
                TableRepeater::make('items')
                    ->visible(fn ($get): bool => $get('mode_of_adjustment') == 0)
                    ->reactive()
                    ->schema([
                        Forms\Components\Select::make('name')
                            ->options(Item::where('team_id', Filament::getTenant()->id)->where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Components\Select $component, ?string $state, $get, $set) {
                                $item = Item::where('id', floatval($get('name')))->first();
                                if ($item) {
                                    $set('quantity_available', $item->stock_on_hand);
                                }
                            })
                            ->required(),
                        Forms\Components\TextInput::make('quantity_available')
                            ->readonly(),
                        Forms\Components\TextInput::make('new_quantity_available')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $set('quantity_adjusted', floatval($get('new_quantity_available')) - floatval($get('quantity_available')));
                            }),
                        Forms\Components\TextInput::make('quantity_adjusted'),
                    ]),
                TableRepeater::make('items')
                    ->visible(fn ($get): bool => $get('mode_of_adjustment') == 1)
                    ->reactive()
                    ->schema([
                        Forms\Components\Select::make('name')
                            ->options(Item::where('stock_on_hand', '>', 0)->where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Components\Select $component, ?string $state, $get, $set) {
                                $item = Item::where('id', floatval($get('name')))->where('team_id', Filament::getTenant()->id)->first();
                                if ($item) {
                                    $set('current_value', $item->stock_on_hand * $item->quantity);
                                }
                            })
                            ->required(),
                        Forms\Components\TextInput::make('current_value')
                            ->readonly(),
                        Forms\Components\TextInput::make('new_value')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $set('value_adjusted', floatval($get('new_value')) - floatval($get('current_value')));
                            }),
                        Forms\Components\TextInput::make('value_adjusted'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('mode_of_adjustment')
                    ->state(function ($record) {
                        switch ($record->mode_of_adjustment) {
                            case 0:
                                return 'Quantity';
                            default:
                                return 'Value';
                        }
                    })
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nmber_of_items affected')
                    ->state(fn ($record) => count($record->items))
                    ->badge()
                    ->color('gray'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListInventoryAdjustments::route('/'),
            'create' => Pages\CreateInventoryAdjustment::route('/create'),
            'edit' => Pages\EditInventoryAdjustment::route('/{record}/edit'),
        ];
    }
}
