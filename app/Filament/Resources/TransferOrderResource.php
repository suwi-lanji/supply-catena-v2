<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferOrderResource\Pages;
use App\Filament\Resources\TransferOrderResource\RelationManagers;
use App\Models\TransferOrder;
use App\Models\Item;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TransferOrderResource extends Resource
{
    protected static ?string $model = TransferOrder::class;

    protected static ?string $navigationGroup = "Inventory";
    protected static ?int $navigationSort = 5;
    public static function shouldRegisterNavigation(): bool {
        if(Filament::getTenant()->has_warehouses) {
            return true;
        } else {
            return false;
        }
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                ->schema([
                Forms\Components\TextInput::make('transfer_order_number')
                    ->required()
                    ->default("TO-0000".TransferOrder::where('team_id', Filament::getTenant()->id)->count() + 1),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\Textarea::make('reason'),
                ])
                ->columns(1),
                Forms\Components\Fieldset::make('')
                ->schema([
                Forms\Components\Select::make('source_warehouse_id')
                    ->live()
                    ->preload()
->searchable()
                    ->afterStateUpdated(function(Request $request, $get) {
                        $request->merge(['source_warehouse_id' => $get('source_warehouse_id')]);
                    })
                    ->relationship('source_warehouse', 'name'),
                Forms\Components\Select::make('destination_warehouse_id')
                    ->live()
                    ->preload()
->searchable()
                    ->relationship('destination_warehouse', 'name'),
                ]),
                Forms\Components\Fieldset::make('')
                ->schema([
                Forms\Components\KeyValue::make('costs')
                ->addActionLabel("Add Cost")
                ->keyLabel("Expense")
                ->valueLabel("Amount")
                ]),
                TableRepeater::make('items')
                ->live()
                ->hidden(fn($get) => !$get('source_warehouse_id'))
                ->afterStateUpdated(function($get, $set) {
                    $items = $get('items');
                    $new_items = array();
                    foreach($items as $item) {
                        $item['source_warehouse_id'] = $get('source_warehouse_id');
                        array_push($new_items, $item);
                    }

                    $set('items', $new_items);
                })
                ->defaultItems(0)
                ->schema([
                    Forms\Components\Hidden::make('source_warehouse_id'),
                    Forms\Components\Select::make('item_name')
                    ->options(Item::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                    ->live()
                    ->preload()
->searchable()
                    ->afterStateUpdated(function($get, $set) {
                        $item = DB::table('warehouse_items')->where('warehouse_id', $get('source_warehouse_id'))->where('item_id', $get('item_name'))->pluck('quantity')->first();
                        $set('current_available', $item);
                    }),
                    Forms\Components\TextInput::make('current_available')->readonly(),
                    Forms\Components\TextInput::make('transfer_quantity'),
                ])
                ->columnSpanFull(),

                Forms\Components\Toggle::make('delivered')
                    ->label('Mark as Delivered'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transfer_order_number')
                    
->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('delivered')
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListTransferOrders::route('/'),
            'create' => Pages\CreateTransferOrder::route('/create'),
            'view' => Pages\ViewTransferOrder::route('/{record}'),
            'edit' => Pages\EditTransferOrder::route('/{record}/edit'),
        ];
    }
}
