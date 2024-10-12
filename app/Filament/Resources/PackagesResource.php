<?php

namespace App\Filament\Resources;
use Filament\Facades\Filament;

use App\Filament\Resources\PackagesResource\Pages;
use App\Filament\Resources\PackagesResource\RelationManagers;
use App\Models\Packages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class PackagesResource extends Resource
{
    protected static ?string $model = Packages::class;
    protected static ?string $navigationGroup = "Sales";
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('package_slip')
                            ->default("PKG-0000" . Packages::where('team_id', Filament::getTenant()->id)->count() + 1),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                    ]),
                    TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if($request->input('sales_order_id')) {
                            $set('items', SalesOrder::where('id', $request->input('sales_order_id'))->pluck('items')->toArray()[0]);
                        }
                    })
                    ->schema([
                        Forms\Components\Select::make('item')
                        ->options(Item::where('team_id', Filament::getTenant()->id)
    ->select('id', DB::raw('COALESCE(part_number, name) as part_number_or_name'))
    ->get()
    ->pluck('part_number_or_name', 'id')
)
                        ->preload()
->searchable(),
                        Forms\Components\Select::make('account')
                        ->options(['Advanced Tax', 'Employee Advance']),
                        Forms\Components\TextInput::make('quantity')
                        ->numeric(),
                        Forms\Components\TextInput::make('rate')
                        ->numeric(),
                        Forms\Components\Select::make('source_warehouse')
                    ->label("Warehouse")
                    ->visible(fn($get): bool=> $get('item') != null)
                    ->live()
                    ->afterStateHydrated(function($get, $set) {
                        $new_rate = floatval($get('rate')) + floatval(DB::table('warehouse_items')->where('warehouse_id', '=', floatval($get('source_warehouse')))->where('item_id', '=', floatval($get('item')))->pluck('price_adjustment')->first());
                        $set('rate', $new_rate);
                        $total = floatval($get('quantity')) * floatval($get('rate'));


                        $set('amount', $total);
                    })
                    ->afterStateUpdated(function($get, $set) {
                        $new_rate = floatval($get('rate')) + floatval(DB::table('warehouse_items')->where('warehouse_id', '=', floatval($get('source_warehouse')))->where('item_id', '=', floatval($get('item')))->pluck('price_adjustment')->first());
                        $set('rate', $new_rate);
                        $total = floatval($get('quantity')) * floatval($get('rate'));


                        $set('amount', $total);
                    })

                    ->options(fn($get) => Warehouse::whereIn('id', DB::table('warehouse_items')->where('item_id', '=', $get('item'))->pluck('warehouse_id'))->pluck('name', 'id')),
                        Forms\Components\TextInput::make('tax')
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
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Textarea::make('internal_notes')->columns(1),
                    ]),
            ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package_slip'),
                Tables\Columns\TextColumn::make('date'),
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
            'index' => Pages\ListPackages::route('/'),
            'view' => Pages\ViewPackage::route('/{record}'),
            'edit' => Pages\EditPackages::route('/{record}/edit'),
        ];
    }

}
