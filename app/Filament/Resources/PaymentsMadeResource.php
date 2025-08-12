<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentsMadeResource\Pages;
use App\Models\Bill;
use App\Models\PaymentsMade;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Http\Request;

class PaymentsMadeResource extends Resource
{
    protected static ?string $model = PaymentsMade::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Purchases';

    protected static ?string $navigationLabel = 'Payments Made';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('')
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
                            ->relationship('vendor', 'vendor_display_name')
                            ->live()
                            ->afterStateHydrated(function (Request $request, $get, $set) {
                                if ($request->input('bill_id')) {
                                    $set('vendor_id', Bill::where('id', $request->input('bill_id'))->pluck('vendor_id')->first());
                                    $items = [];
                                    $total = 0;
                                    $vat = 0;
                                    $bills = Bill::where('id', $request->input('bill_id'))->where('balance_due', '>', 0)->get()->toArray();
                                    foreach ($bills as $bill) {
                                        $item = ['bill_id' => $bill['id'], 'date' => $bill['bill_date'], 'bill_number' => $bill['bill_number'], 'purchase_order_number' => $bill['order_number'], 'bill_amount' => $bill['total'], 'amount_due' => $bill['balance_due'], 'payment' => 0];
                                        $total += $bill['balance_due'];
                                        array_push($items, $item);
                                    }
                                    $set('full_amount', $total);
                                    $set('items', $items);
                                }
                            })
                            ->afterStateUpdated(function ($set, $get) {
                                $items = [];
                                $total = 0;
                                $vat = 0;
                                $bills = Bill::where('vendor_id', $get('vendor_id'))->where('balance_due', '>', 0)->get()->toArray();
                                $set('payment_mode', $get('vendor_id'));
                                foreach ($bills as $bill) {
                                    $item = ['bill_id' => $bill['id'], 'date' => $bill['bill_date'], 'bill_number' => $bill['bill_number'], 'purchase_order_number' => $bill['order_number'], 'bill_amount' => $bill['total'], 'amount_due' => $bill['balance_due'], 'payment' => 0];
                                    $total += $bill['balance_due'];
                                    array_push($items, $item);
                                }
                                $set('full_amount', $total);
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
                                return PaymentsMade::where('team_id', Filament::getTenant()->id)->count() + 1;
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required(),
                        Forms\Components\TextInput::make('full_amount')->hidden(true),
                        Forms\Components\TextInput::make('payment_made')
                            ->hintAction(
                                Action::make('Pay Full Amount')
                                    ->action(function ($get, $set) {
                                        $set('payment_made', $get('full_amount'));
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
                        Forms\Components\Toggle::make('clear_applied_amount')
                            ->visible(false)
                            ->default(false)
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->default('RN-0000'.PaymentsMade::where('team_id', Filament::getTenant()->id)->count() + 1)
                            ->required(),
                    ]),

                TableRepeater::make('items')
                    ->live()
                    ->afterStateHydrated(function (Request $request, $get, $set) {
                        if ($request->input('bill_id')) {
                            $items = [];
                            $total = 0;
                            $vat = 0;
                            $bills = Bill::where('id', $request->input('bill_id'))->where('balance_due', '>', 0)->get()->toArray();
                            foreach ($bills as $bill) {
                                $item = ['bill_id' => $bill['id'], 'date' => $bill['bill_date'], 'bill_number' => $bill['bill_number'], 'purchase_order_number' => $bill['order_number'], 'bill_amount' => $bill['total'], 'amount_due' => $bill['balance_due'], 'payment' => 0];
                                $total += $bill['balance_due'];
                                array_push($items, $item);
                            }
                            $set('full_amount', $total);
                            $set('items', $items);
                        }
                    })
                    ->schema([
                        Forms\Components\TextInput::make('bill_id')->visible(false),
                        Forms\Components\TextInput::make('date'),
                        Forms\Components\TextInput::make('bill_number'),
                        Forms\Components\TextInput::make('purchase_order_number'),
                        Forms\Components\TextInput::make('bill_amount')->numeric(),
                        Forms\Components\TextInput::make('amount_due')->numeric(),
                        Forms\Components\TextInput::make('payment')->numeric(),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->default('.'),
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
                Tables\Columns\TextColumn::make('payment_made')
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
            ])
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateDescription('Receipts of your bill payments will show up here.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Payment')
                    ->url(route('filament.dashboard.resources.payments-mades.create', ['tenant' => Filament::getTenant()]))
                    ->icon('heroicon-m-plus')
                    ->button(),
                Tables\Actions\Action::make('unpaid_bills')
                    ->label('Go To Unpaid Bills')
                    ->url(route('filament.dashboard.resources.bills.index', ['tenant' => Filament::getTenant(), 'tableFilters[is_paid][isActive]' => false]))
                    ->color('success')
                    ->button(),
                Tables\Actions\Action::make('import_payments')
                    ->label('Import Payments')
                    ->url(route('filament.dashboard.resources.payments-mades.create', ['tenant' => Filament::getTenant()]))
                    ->color('default')
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
            'index' => Pages\ListPaymentsMades::route('/'),
            'create' => Pages\CreatePaymentsMade::route('/create'),
            'view' => Pages\ViewPaymentsMade::route('/{record}'),
            'edit' => Pages\EditPaymentsMade::route('/{record}/edit'),
        ];
    }
}
