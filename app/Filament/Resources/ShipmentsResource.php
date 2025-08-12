<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentsResource\Pages;
use App\Models\Packages;
use App\Models\Shipments;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Http\Request;

class ShipmentsResource extends Resource
{
    protected static ?string $model = Shipments::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'company_display_name')
                            ->afterStateHydrated(function (Request $request, $set) {
                                if ($request->input('customer_id')) {
                                    $set('customer_id', floatval($request->input('customer_id')));
                                }
                            })
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('packages')
                            ->afterStateHydrated(function (Request $request, $set) {
                                if ($request->input('package_id')) {
                                    $set('packages', [floatval($request->input('package_id'))]);
                                }
                            })
                            ->options(Packages::where('team_id', Filament::getTenant()->id)->pluck('package_slip', 'id'))
                            ->preload()
                            ->searchable()
                            ->multiple(),
                        Forms\Components\TextInput::make('shipment_order_number')
                            ->default('SHP-0000'.Shipments::where('team_id', Filament::getTenant()->id)->count() + 1)
                            ->required(),
                        Forms\Components\DatePicker::make('shipment_date')
                            ->required(),
                    ])
                    ->columns(1),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('delivery_method_id')
                            ->relationship('delivery_method', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\Hidden::make('team_id')->default(Filament::getTenant()->id),
                                Forms\Components\TextInput::make('name'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('tracking_number'),
                        Forms\Components\TextInput::make('tracking_url'),
                        Forms\Components\TextInput::make('shipping_charges')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(1),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('notes'),
                        Forms\Components\Toggle::make('delivered')
                            ->required(),
                    ])->columns(1),
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
                Tables\Columns\TextColumn::make('delivery_method.name')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tracking_url')
                    ->searchable(),
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
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipments::route('/create'),
            'view' => Pages\ViewShipment::route('/{record}'),
            'edit' => Pages\EditShipments::route('/{record}/edit'),
        ];
    }
}
