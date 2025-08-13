<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditNotesResource\Pages;
use App\Models\CreditNotes;
use App\Models\Item;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class CreditNotesResource extends Resource
{
    protected static ?string $model = CreditNotes::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'company_display_name')
                            ->live(onBlur: true)
                            ->preload()
                            ->searchable()
                            ->required()
                            ->suffixAction(
                                Action::make('Add Customer')
                                    ->icon('heroicon-o-plus')
                                    ->url(route('filament.dashboard.resources.customers.create', ['tenant' => Filament::getTenant()]))
                            ),
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('credit_note_number')
                            ->default('DN-0000'.CreditNotes::where('team_id', Filament::getTenant()->id)->count() + 1)
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->default('RN-0000'.CreditNotes::where('team_id', Filament::getTenant()->id)->count() + 1)
                            ->required(),

                        Forms\Components\DatePicker::make('credit_note_date')
                            ->native(false)->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('subject'),
                    ])->columns(1),
                TableRepeater::make('items')
                    ->live(onBlur: true)
                    ->hintActions([
                        Action::make('add_quantity_to_all')
                            ->form([
                                Forms\Components\TextInput::make('quantity'),
                            ])
                            ->action(function ($data, $get, $set) {
                                $items = $get('items');
                                $new_items = [];

                                foreach ($items as $item) {
                                    $item['quantity'] = $data['quantity'];
                                    $total = floatval($item['quantity']) * floatval($item['rate']);
                                    if (floatval($item['tax']) > 0) {
                                        $total -= (floatval($item['tax']) / 100 * $total);
                                    }
                                    $item['amount'] = $total;
                                    array_push($new_items, $item);
                                }

                                $set('items', $new_items);
                            }),
                        Action::make('calculate_total')
                            ->action(function ($get, $set) {
                                $items = $get('items');
                                $total = 0;
                                $vat = 0;
                                foreach ($items as $item) {
                                    $total += $item['amount'];
                                    $vat += $item['tax'];
                                }

                                $set('sub_total', $total);
                                $total = floatval($get('sub_total')) + (floatval($vat) / 100 * floatval($get('sub_total')));
                                $total = floatval($total) - (floatval($get('discount')) / 100 * floatval($total));
                                $total += floatval($get('adjustment'));
                                $set('total', $total);
                                $set('amount_due', $total);
                            }),
                    ])
                    ->schema([
                        Forms\Components\Select::make('item')
                            ->required()
                            ->options(Item::where('team_id', Filament::getTenant()->id)->pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $items = [];
                                $obj = Item::where('id', $get('item'))->get()->toArray();
                                $set('rate', $obj[0]['cost_price']);
                                $amount = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $amount);
                            }),
                        Forms\Components\TextInput::make('account')
                            ->default('Cost Of Goods'),
                        Forms\Components\TextInput::make('quantity')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($get, $set) {
                                $amount = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $amount);
                            })
                            ->afterStateUpdated(function ($get, $set) {
                                $amount = floatval($get('quantity')) * floatval($get('rate'));

                                $set('amount', $amount);
                            })
                            ->numeric()
                            ->default(1),
                        Forms\Components\TextInput::make('rate')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->label('Tax (%)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $amount = floatval($get('amount')) - (floatval($get('amount')) * floatval($get('tax')) / 100);

                                $set('amount', $amount);
                            })
                            ->default(0),
                        Forms\Components\TextInput::make('amount')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $amount = floatval($get('quantity')) * floatval($get('rate'));
                                $amount = floatval($get('amount')) - (floatval($get('amount')) * floatval($get('tax')) / 100);

                                $set('amount', $amount);
                            })
                            ->numeric()
                            ->default(0),
                    ])
                    ->colStyles([
                        'item' => 'width:200px',
                    ]),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\TextInput::make('sub_total')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('discount')
                            ->default(0)
                            ->numeric(),
                        Forms\Components\TextInput::make('adjustment')
                            ->default(0)

                            ->numeric(),
                        Forms\Components\TextInput::make('total')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set) {
                                $set('amount_due', $get('total'));
                            })
                            ->numeric(),

                    ])
                    ->columns(1),
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Hidden::make('amount_due'),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('credit_note_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit_note_number')

->searchable(),
                Tables\Columns\TextColumn::make('reference_number')

->searchable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateDescription('Create Customer credits and apply them to multiple invoices when selling stuff to your customers.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Credit Notes')
                    ->color('success')
                    ->url(route('filament.dashboard.resources.credit-notes.create', ['tenant' => Filament::getTenant()]))
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
            'index' => Pages\ListCreditNotes::route('/'),
            'create' => Pages\CreateCreditNotes::route('/create'),
            'view' => Pages\ViewCreditNotes::route('/{record}'),
            'edit' => Pages\EditCreditNotes::route('/{record}/edit'),
        ];
    }
}
