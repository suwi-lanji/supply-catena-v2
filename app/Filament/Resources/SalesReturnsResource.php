<?php

namespace App\Filament\Resources;
use Filament\Facades\Filament;

use App\Filament\Resources\SalesReturnsResource\Pages;
use App\Filament\Resources\SalesReturnsResource\RelationManagers;
use App\Models\SalesReturns;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
class SalesReturnsResource extends Resource
{
    protected static ?string $model = SalesReturns::class;

    protected static ?string $navigationGroup = "Sales";
    protected static ?int $navigationSort = 10;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sales_returns_number')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('reason'),
                Forms\Components\Toggle::make('credit_only_goods'),
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
                        Forms\Components\Select::make('account')
                        ->options(['Advanced Tax', 'Employee Advance']),
                        Forms\Components\TextInput::make('quantity')
                        ->numeric(),
                        Forms\Components\TextInput::make('rate')
                        ->numeric(),
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
                Forms\Components\Toggle::make('approved'),
                Forms\Components\TextInput::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sales_returns_number')

->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')

->searchable(),
                Tables\Columns\IconColumn::make('credit_only_goods')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')

->searchable(),
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
            'index' => Pages\ListSalesReturns::route('/'),
            'edit' => Pages\EditSalesReturns::route('/{record}/edit'),
        ];
    }
}
