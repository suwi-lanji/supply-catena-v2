<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderShipmentResource\Pages;
use App\Models\PurchaseOrderShipment;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderShipmentResource extends Resource
{
    protected static ?string $model = PurchaseOrderShipment::class;

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationGroup = 'Purchases';

    protected static ?string $navigationLabel = 'Shipments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_id')
                    ->relationship('vendor', 'vendor_display_name')
                    ->required()

                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('purchase_order_id')
                    ->relationship('purchase_order', 'purchase_order_number')
                    ->required()

                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('shipment_order_number')
                    ->required()
                    ->default('SHIP-0000'.PurchaseOrderShipment::where('team_id', Filament::getTenant()->id)->count() + 1),
                Forms\Components\DatePicker::make('shipment_date')
                    ->native(false)->default(now())
                    ->required(),
                Forms\Components\Select::make('delivery_method_id')
                    ->relationship('delivery_method', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('tracking_number'),
                Forms\Components\TextInput::make('tracking_url'),
                Forms\Components\TextInput::make('shipping_charges')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('notes'),
                Forms\Components\Toggle::make('delivered'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shipment_order_number')

->searchable(),
                Tables\Columns\TextColumn::make('shipment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_charges')
                    ->numeric()
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
            'index' => Pages\ListPurchaseOrderShipments::route('/'),
            'create' => Pages\CreatePurchaseOrderShipment::route('/create'),
            'view' => Pages\ViewPurchaseOrderShipment::route('/{record}'),
            'edit' => Pages\EditPurchaseOrderShipment::route('/{record}/edit'),
        ];
    }
}
