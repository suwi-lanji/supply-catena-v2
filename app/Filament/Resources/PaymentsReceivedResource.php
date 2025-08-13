<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentsReceivedResource\Pages;
use App\Models\Invoices;
use App\Models\PaymentsReceived;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;

class PaymentsReceivedResource extends Resource
{
    protected static ?string $model = PaymentsReceived::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Payments Received';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'company_display_name')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (Request $request, $get, $set) {
                                if ($request->input('invoice_id')) {

                                    $set('customer_id', Invoices::where('id', $request->input('invoice_id'))->pluck('customer_id')->first());
                                    $request->merge(['customer_id' => $get('customer_id')]);
                                    $items = [];
                                    $invoice = Invoices::where('id', $request->input('invoice_id'))->get()->first();
                                    $item = ['invoice_id' => $invoice['id'], 'date' => $invoice['invoice_date'], 'invoice_number' => $invoice['invoice_number'], 'purchase_order_number' => $invoice['order_number'], 'invoice_amount' => $invoice['total'], 'amount_due' => $invoice['balance_due'], 'payment' => 0];
                                    array_push($items, $item);
                                    $set('items', $items);
                                }
                            })
                            ->afterStateUpdated(function ($set, $get, Request $request) {
                                $items = [];
                                $request->merge(['customer_id' => $get('customer_id')]);
                                $invoices = Invoices::where('customer_id', $get('customer_id'))->where('balance_due', '>', 0)->get()->toArray();
                                $set('payment_mode', $get('customer_id'));
                                foreach ($invoices as $invoice) {
                                    $item = ['invoice_id' => $invoice['id'], 'date' => $invoice['invoice_date'], 'invoice_number' => $invoice['invoice_number'], 'purchase_order_number' => $invoice['order_number'], 'invoice_amount' => $invoice['total'], 'amount_due' => $invoice['balance_due'], 'payment' => 0];
                                    array_push($items, $item);
                                }

                                $set('items', $items);
                            })
                            ->preload()
                            ->searchable()
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('payment_number')
                            ->default(function () {
                                return PaymentsReceived::where('team_id', Filament::getTenant()->id)->count() + 1;
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')   
                            ->native(false)->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('amount_received')
                            ->hintAction(
                                Action::make('Pay Full Amount')
                                    ->action(function ($get, $set) {
                                        $items = $get('items');
                                        $new_items = [];
                                        $total = 0;
                                        $vat = 0;
                                        foreach ($items as $item) {
                                            $total += floatval($item['amount_due']);
                                            $item['payment'] = $item['amount_due'];
                                            array_push($new_items, $item);
                                        }

                                        $set('amount_received', $total);
                                        $set('items', $new_items);
                                    })
                            )
                            ->required()
                            ->numeric(),
                        Forms\Components\Select::make('payment_mode')
                            ->options(['Bank Remittance', 'Bank Transfer', 'Cash', 'Check', 'Credit Card', 'Other'])
                            ->required(),
                        Forms\Components\Select::make('paid_through')
                            ->options(['Petty Cash', 'Undeposited funds', 'Employee Reimbursements', 'Drawings', 'Opening Balance Offset', 'Owners Equity', 'Employee Advance', 'Other'])
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->required(),
                    ]),

                TableRepeater::make('items')
                    ->live()
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('invoice_id')) {
                            $items = [];
                            $invoice = Invoices::where('id', $request->input('invoice_id'))->get()->first();
                            $item = ['invoice_id' => $invoice['id'], 'date' => $invoice['invoice_date'], 'invoice_number' => $invoice['invoice_number'], 'purchase_order_number' => $invoice['order_number'], 'invoice_amount' => $invoice['total'], 'amount_due' => $invoice['balance_due'], 'payment' => 0];
                            array_push($items, $item);
                            $set('items', $items);
                        }
                    })
                    ->schema([
                        Forms\Components\Hidden::make('invoice_id'),
                        Forms\Components\TextInput::make('invoice_number'),
                        Forms\Components\TextInput::make('date'),
                        Forms\Components\TextInput::make('purchase_order_number'),
                        Forms\Components\TextInput::make('invoice_amount')->numeric(),
                        Forms\Components\TextInput::make('amount_due')->numeric(),
                        Forms\Components\TextInput::make('payment')->numeric(),
                    ])
                    ->addable()
                    ->defaultItems(0),
                Forms\Components\Textarea::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')

->searchable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_received')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_charges')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')

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
            'index' => Pages\ListPaymentsReceiveds::route('/'),
            'create' => Pages\CreatePaymentsReceived::route('/create'),
            'view' => Pages\ViewPaymentsReceived::route('/{record}'),
            'edit' => Pages\EditPaymentsReceived::route('/{record}/edit'),
        ];
    }
}
