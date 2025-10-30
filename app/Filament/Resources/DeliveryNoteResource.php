<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use App\Models\Item;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select; // Used for layout instead of Tabs
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater; // Import the custom component

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. DELIVERY DETAILS SECTION
                Section::make('Delivery Details')
                    ->description('Enter general information about the delivery.')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2) // Set 3 columns for this section
                    ->schema([
                        // Row 1
                        TextInput::make('dnote_number')
                            ->label('DNote Number')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1), // Takes 1 of 3 columns

                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'company_display_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        // Row 2
                        Select::make('sales_order_id')
                            ->label('Sales Order')
                            ->relationship('salesOrder', 'sales_order_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->live()
                            ->afterStateUpdated(function ($get, $set, $state) {
                                $dnoteItems = [];
                                $salesOrder = \App\SalesOrder::find($state);
                                if ($salesOrder) {
                                    foreach ($salesOrder->items as $item) {
                                        $dnoteItems[] = [
                                            'item_id' => $item['item'],
                                            'material_number' => '',
                                            'description' => '',
                                            'ordered' => $item['quantity'],
                                            'delivered' => 0,
                                            'outstanding' => 0,
                                        ];
                                    }
                                    $set('items', $dnoteItems);
                                }
                            }),

                        TextInput::make('mode_of_transport')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                    ]),
                Section::make('Delivery Items')
                    ->description('Specify the items and quantities being delivered.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->schema([
                        TableRepeater::make('items')
                            ->label('Line Items')
                            ->addable(true)
                            ->deletable(true)
                            ->reorderable(true)
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item (Part No.)')
                                    ->options(
                                        Item::where('team_id', Filament::getTenant()->id)
                                            ->pluck('part_number', 'id')
                                    )
                                    ->searchable()
                                    ->required(),

                                TextInput::make('material_number')
                                    ->label('Material No.'),

                                TextInput::make('description')
                                    ->label('Description'),

                                TextInput::make('ordered')
                                    ->label('Ordered')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('delivered')
                                    ->label('Delivered')
                                    ->numeric()
                                    ->required()
                                    ->default(0),

                                TextInput::make('outstanding')
                                    ->label('Outstanding')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dnote_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('DNote No.'),

                TextColumn::make('customer.company_display_name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer Name'),

                TextColumn::make('salesOrder.sales_order_number')
                    ->searchable()
                    ->sortable()
                    ->label('Sales Order'),

                TextColumn::make('mode_of_transport')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date Created')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'company_display_name')
                    ->label('Filter by Customer'),
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
            'index' => Pages\ListDeliveryNotes::route('/'),
            'create' => Pages\CreateDeliveryNote::route('/create'),
            'view' => Pages\ViewDeliveryNote::route('/{record}'),
            'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        ];
    }
}
