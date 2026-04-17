<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        if (Filament::getTenant()->has_warehouses != null && Filament::getTenant()->has_warehouses != false) {
            return true;
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('attention'),
                Forms\Components\TextInput::make('street_1'),
                Forms\Components\TextInput::make('street_2'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\TextInput::make('province'),
                Forms\Components\TextInput::make('country')->required(),
                Forms\Components\TextInput::make('phone')
                    ->required()
                    ->tel(),
                Forms\Components\TextInput::make('email')
                    ->email(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean(),
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
            ])
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateDescription('Create multiple warehouses to store items in different locations')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Warehouse')
                    ->color('success')
                    ->url(route('filament.dashboard.resources.warehouses.create', ['tenant' => Filament::getTenant()]))
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
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'view' => Pages\ViewWarehouse::route('/{record}'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
