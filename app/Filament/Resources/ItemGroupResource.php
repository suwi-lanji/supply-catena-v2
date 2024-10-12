<?php

namespace App\Filament\Resources;
use Filament\Facades\Filament;
use App\Filament\Resources\ItemGroupResource\Pages;
use App\Filament\Resources\ItemGroupResource\RelationManagers;
use App\Models\ItemGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
class ItemGroupResource extends Resource
{
    protected static ?string $model = ItemGroup::class;

    protected static ?string $navigationGroup = "Inventory";
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options(['Goods', 'Service'])
                    ->required(),
                Forms\Components\TextInput::make('item_group_name')
                    ->required(),
                
                Forms\Components\Toggle::make('returnable_item')
                    ->required(),
                Forms\Components\FileUpload::make('images')
                    ->directory('item-group-images')
                    ->multiple()
                    ->image()
                    ->getUploadedFileNameForStorageUsing(fn($file) => 'item-group-images-'.$file->getClientOriginalName()),
                Forms\Components\Select::make('unit')
                    ->options(['box', 'cm', 'ft', 'g', 'in', 'kg', 'km', 'lb', 'mg', 'ml', 'm', 'pcs'])
                    ->required(),
                Forms\Components\Repeater::make('attributes')
                    ->defaultItems(1)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->live()
                            ->afterStateUpdated(function($get, $set) {
                                $new_items = array();
                                foreach($get('items') as $item) {
                                    $item["warehouse_id"] = $get("warehouse_id");
                                    array_push($new_items, $item);
                                }

                                $set("items", $new_items);
                            })
                            ->visible(function() {
                                if(Filament::getTenant()->has_warehouses) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }),
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\TagsInput::make('options')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function(array|null $state, array|null $old, $get, $set) {
                            $items = array();
                            $length = 12;
                            $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                            foreach($state as $item) {
                                $sku = '';
                                for($i=0;$i< $length;$i++) {
                                    $sku.=$characters[rand(0,strlen($characters) - 1)];
                                }
                                $i = array('name' => $item, 'sku' => $sku, 'cost_price' => null, 'selling_price' => 0, 'item_type' => $get('type'), 'upc' => null, 'ean' => null, 'isbn' => null, 'team_id' => Filament::getTenant()->id, 'warehouse_id' => $get('warehouse_id'));
                                array_push($items, $i);
                            }
                            $set('items', $items);
                        }),
                        TableRepeater::make('items')
                            ->relationship('items')
                            ->dehydrateStateUsing(function($state,$get, $set) {
                                $new_state = array();
                                foreach($state as $item) {
                                    $item['team_id'] = Filament::getTenant()->id;
                                    $item['warehouse_id'] = $get('warehouse_id');
                                    array_push($new_state, $item);
                                }
                                throw \Exception($new_state);

                                return $new_state;
                            })
                            ->hintActions([
                                Action::make('type')
                                ->form([
                                    Forms\Components\Select::make('type')
                                    ->options(['Goods', 'Services'])
                                ])
                                ->action(function($data, $get, $set) {
                                    $items = $get('items');
                                    $new_items = array();

                                    foreach($items as $item) {
                                        if($data['type'] == 0) {
                                            $item['item_type'] = 'Goods';
                                        } else {
                                            $item['item_type'] = 'Services';
                                        }
                                        array_push($new_items, $item);
                                    }

                                    $set('items', $new_items);
                                }),
                                Action::make('add_selling_price_to_all')
                                ->form([
                                    Forms\Components\TextInput::make('amount')
                                ])
                                ->action(function($data, $get, $set) {
                                    $items = $get('items');
                                    $new_items = array();

                                    foreach($items as $item) {
                                        $item['selling_price'] = $data['amount'];
                                        array_push($new_items, $item);
                                    }

                                    $set('items', $new_items);
                                }),
                                Action::make('add_cost_price_to_all')
                                ->form([
                                    Forms\Components\TextInput::make('amount')
                                ])
                                ->action(function($data, $get, $set) {
                                    $items = $get('items');
                                    $new_items = array();

                                    foreach($items as $item) {
                                        $item['cost_price'] = $data['amount'];
                                        array_push($new_items, $item);
                                    }

                                    $set('items', $new_items);
                                })
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('name'),
                                Forms\Components\TextInput::make('sku'),
                                Forms\Components\TextInput::make('item_type')->label('type'),
                                Forms\Components\TextInput::make('cost_price'),
                                Forms\Components\TextInput::make('selling_price'),
                                Forms\Components\TextInput::make('upc'),
                                Forms\Components\TextInput::make('ean'),
                                Forms\Components\TextInput::make('isbn'),
                                Forms\Components\Hidden::make('team_id'),
                                Forms\Components\Hidden::make('warehouse_id'),
                            ])
                            ->colStyles([
                                'default' => 'margin-bottom:10px',
                            ])
                            ->addable(false)
                    ]),
                Forms\Components\Fieldset::make('Configure Accounts')
                ->schema([
                Forms\Components\Select::make('sales_account_id')
                    ->relationship('sales_account', 'name')
                    ->createOptionForm([
                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                        Forms\Components\TextInput::make('name')
                            ->required()
                    ])
                    ->required(),
                Forms\Components\Select::make('purchases_account_id')
                    ->relationship('purchases_account', 'name')
                    ->createOptionForm([
                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                        Forms\Components\TextInput::make('name')
                            ->required()
                    ])
                    ->required(),
                Forms\Components\Select::make('inventory_account')
                    ->options(['Finished Goods', 'Inventory Assets', 'Work In Progress'])
                    ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    
->searchable(),
                Tables\Columns\IconColumn::make('is_inventory')
                    ->boolean(),
                Tables\Columns\TextColumn::make('item_group_name')
                    
->searchable(),
                Tables\Columns\IconColumn::make('returnable_item')
                    ->boolean(),
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
            'index' => Pages\ListItemGroups::route('/'),
            'create' => Pages\CreateItemGroup::route('/create'),
            'edit' => Pages\EditItemGroup::route('/{record}/edit'),
        ];
    }
}
