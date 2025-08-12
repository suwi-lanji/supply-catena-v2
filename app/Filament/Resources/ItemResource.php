<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Brand;
use App\Models\Item;
use App\Models\Manufucturer;
use App\Models\PurchasesAccount;
use App\Models\SalesAccount;
use App\Models\Vendor;
use App\Models\Warehouse;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('General Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Select::make('item_type')
                            ->options(['Goods' => 'Goods', 'Service' => 'Service'])
                            ->required(),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1'),
                        Forms\Components\TextInput::make('sku')
                            ->afterStateHydrated(function ($set) {
                                $length = 12;
                                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                $sku = '';
                                for ($i = 0; $i < $length; $i++) {
                                    $sku .= $characters[rand(0, strlen($characters) - 1)];
                                }

                                $set('sku', $sku);
                            })
                            ->required(),
                        Forms\Components\TextInput::make('part_number')->unique(ignoreRecord: true)
                            ->validationMessages([
                            'unique' => 'The :attribute has already been registered.',
                            ]),
                        Forms\Components\Checkbox::make('returnable_item'),
                        Forms\Components\Textarea::make('description'),
                        Forms\Components\TextInput::make('condition'),
                    ])->columns(1),
                Forms\Components\Fieldset::make('Sales Information')
                    ->schema([
                        Forms\Components\TextInput::make('selling_price')
                            ->required(),
                        Forms\Components\Select::make('sales_account_id')
                            ->suffixAction(
                                Actions\Action::make('create_sales_account')
                                    ->form([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                    ])
                                    ->action(function (array $data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = SalesAccount::create($data);

                                        return $created;
                                    })
                                    ->icon('heroicon-o-plus')
                            )
                            ->label('Sales Account')
                            ->options(SalesAccount::where('team_id', Filament::getTenant()->id)->pluck('name', 'id')),
                        Forms\Components\TextInput::make('sales_description'),
                    ]),
                Forms\Components\Fieldset::make('Purchase Information')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->required(),
                        Forms\Components\Select::make('purchase_account_id')
                            ->suffixAction(
                                Actions\Action::make('create_purchase_account')
                                    ->form([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                    ])
                                    ->action(function (array $data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = PurchasesAccount::create($data);

                                        return $created;
                                    })
                                    ->icon('heroicon-o-plus')
                            )
                            ->label('Purchase Account')
                            ->options(PurchasesAccount::where('team_id', Filament::getTenant()->id)->pluck('name', 'id')),
                        Forms\Components\TextInput::make('purchases_description'),
                    ]),
                Forms\Components\Checkbox::make('track_inventory_for_this_item')->live(),
                Forms\Components\Fieldset::make('Inventory Information')
                    ->hidden(fn (Get $get): bool => ! $get('track_inventory_for_this_item'))
                    ->schema([
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->hidden(fn () => ! Filament::getTenant()->has_warehouses)
                            ->options(Warehouse::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->preload()
                            ->searchable(),
                        Forms\Components\TextInput::make('stock_on_hand')->label('Opening Stock'),
                        Forms\Components\TextInput::make('reorder_level'),
                        Forms\Components\Select::make('inventory_account')
                            ->options(['Inventory assets']),
                        Forms\Components\Select::make('preferred_vendor_id')
                            ->suffixAction(
                                Actions\Action::make('create_vendor')
                                    ->form([
                                        Forms\Components\TextInput::make('first_name')
                                            ->required(),
                                        Forms\Components\TextInput::make('last_name')
                                            ->required(),
                                        Forms\Components\TextInput::make('vendor_display_name')
                                            ->label('Display Name')
                                            ->required(),
                                    ])
                                    ->action(function (array $data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = Vendor::create($data);

                                        return $created;
                                    })
                                    ->icon('heroicon-o-plus')
                            )
                            ->label('Preferred Vendor')
                            ->options(Vendor::where('team_id', Filament::getTenant()->id)->pluck('vendor_display_name', 'id')),

                    ]),
                Forms\Components\Fieldset::make('More Fields')
                    ->schema([
                        Forms\Components\TextInput::make('dimensions'),
                        Forms\Components\TextInput::make('weight'),
                        Forms\Components\Select::make('manufucturer_id')
                            ->suffixAction(
                                Actions\Action::make('create_manufucturer')
                                    ->form([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                    ])
                                    ->action(function (array $data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = Manufucturer::create($data);

                                        return $created;
                                    })
                                    ->icon('heroicon-o-plus')
                            )
                            ->label('Manufucturer')
                            ->options(Manufucturer::where('team_id', Filament::getTenant()->id)->pluck('name', 'id')),
                        Forms\Components\Select::make('brand_id')
                            ->suffixAction(
                                Actions\Action::make('create_brand')
                                    ->form([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                    ])
                                    ->action(function (array $data) {
                                        $data['team_id'] = Filament::getTenant()->id;
                                        $created = Brand::create($data);

                                        return $created;
                                    })
                                    ->icon('heroicon-o-plus')
                            )
                            ->label('Brand')
                            ->options(Brand::where('team_id', Filament::getTenant()->id)->pluck('name', 'id')),

                        Forms\Components\TextInput::make('upc'),
                        Forms\Components\TextInput::make('mpn'),
                        Forms\Components\TextInput::make('ean'),
                        Forms\Components\TextInput::make('isbn'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_on_hand')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reorder_level')
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
                Filter::make('low_stock_items')
                    ->query(function (Builder $query) {
                        $query->whereColumn('stock_on_hand', '<', 'reorder_level')
                            ->whereNotNull('reorder_level');
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
