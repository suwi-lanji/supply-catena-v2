<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoicesResource\Pages;
use App\Models\Invoices;
use App\Models\Item;
use App\Models\SalesAccount;
use App\Models\SalesOrder;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InvoicesResource extends Resource
{
    protected static ?string $model = Invoices::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->relationship(
                                        name: 'customer',
                                        titleAttribute: 'company_display_name',
                                        modifyQueryUsing: fn (Builder $query) => 
                                            $query->where('team_id', Filament::getTenant()->id)
                                    )
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Clear related data if customer changes
                                        if (empty($state) || $state !== $get('original_customer_id')) {
                                            $set('order_number', null);
                                            $set('items', []);
                                        }
                                    }),
                                
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'tax' => 'Tax Invoice',
                                        'proforma' => 'Proforma Invoice',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (in_array($state, ['tax', 'proforma'])) {
                                            $prefix = $state === 'tax' ? 'INV-' : 'PI-';
                                            $yearMonth = date('ym');
                                            $count = Invoices::where('team_id', Filament::getTenant()->id)
                                                ->where('type', $state)
                                                ->count() + 1;
                                            $set('invoice_number', $prefix . $yearMonth . str_pad($count, 4, '0', STR_PAD_LEFT));
                                        }
                                    })
                                    ->default('tax'),
                                
                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->placeholder('Auto-generated')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->disabled(fn ($operation) => $operation === 'edit')
                                    ->dehydrated(),
                            ]),
                        
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('order_number')
                                    ->label('Sales Order')
                                    ->relationship(
                                        name: 'salesOrder',
                                        titleAttribute: 'sales_order_number',
                                        modifyQueryUsing: fn (Builder $query, $get) => 
                                            $query->where('team_id', Filament::getTenant()->id)
                                                ->when($get('customer_id'), 
                                                    fn ($query, $customerId) => $query->where('customer_id', $customerId)
                                                )
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $salesOrder = SalesOrder::find($state);
                                            if ($salesOrder) {
                                                // Set items from sales order
                                                $items = json_decode($salesOrder->items, true) ?? [];
                                                $formattedItems = [];
                                                
                                                foreach ($items as $item) {
                                                    $formattedItems[] = [
                                                        'item' => $item['item_id'] ?? null,
                                                        'quantity' => $item['quantity'] ?? 1,
                                                        'rate' => $item['rate'] ?? 0,
                                                        'alternative' => $item['alternative'] ?? '',
                                                        'discount' => $item['discount'] ?? 0,
                                                        'weight' => $item['weight'] ?? '',
                                                        'lead_time' => $item['lead_time'] ?? '',
                                                        'tax' => $item['tax'] ?? 0,
                                                        'amount' => $item['amount'] ?? 0,
                                                        'account' => $item['account'] ?? null,
                                                    ];
                                                }
                                                
                                                $set('items', $formattedItems);
                                                $set('original_customer_id', $salesOrder->customer_id);
                                                
                                                // Calculate totals
                                                self::calculateTotals($get, $set);
                                            }
                                        }
                                    }),
                                
                                Forms\Components\DatePicker::make('invoice_date')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                                
                                Forms\Components\Select::make('payment_terms_id')
                                    ->label('Payment Terms')
                                    ->relationship(
                                        name: 'paymentTerm',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query) => 
                                            $query->where('team_id', Filament::getTenant()->id)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\Section::make('Payment Terms')
                                            ->schema([
                                                Forms\Components\Hidden::make('team_id')
                                                    ->default(Filament::getTenant()->id),
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('days')
                                                    ->label('Payment Days')
                                                    ->numeric()
                                                    ->default(30),
                                            ]),
                                    ])
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('due_date')
                                    ->required()
                                    ->default(now()->addDays(30))
                                    ->native(false),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('sales_person_id')
                                    ->label('Sales Person')
                                    ->relationship(
                                        name: 'salesPerson',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query) => 
                                            $query->where('team_id', Filament::getTenant()->id)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\Section::make('Sales Person')
                                            ->schema([
                                                Forms\Components\Hidden::make('team_id')
                                                    ->default(Filament::getTenant()->id),
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('phone')
                                                    ->tel()
                                                    ->maxLength(20),
                                            ]),
                                    ]),
                                
                                Forms\Components\TextInput::make('subject')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Invoice Items')
                    ->schema([
                        TableRepeater::make('items')
                            ->live()
                            ->schema([
                                Forms\Components\Select::make('item')
                                    ->label('Product/Service')
                                    ->options(
                                        Item::where('team_id', Filament::getTenant()->id)
                                            ->select('id', DB::raw('COALESCE(part_number, name) as display_name'))
                                            ->get()
                                            ->pluck('display_name', 'id')
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        if ($state) {
                                            $item = Item::find($state);
                                            if ($item) {
                                                $set('rate', $item->selling_price);
                                                $set('description', $item->description);
                                                self::calculateItemTotal($get, $set);
                                            }
                                        }
                                    })
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('description')
                                    ->label('Description')
                                    ->columnSpan(3),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateItemTotal($get, $set)),
                                
                                Forms\Components\TextInput::make('rate')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateItemTotal($get, $set)),
                                
                                Forms\Components\TextInput::make('discount')
                                    ->label('Discount %')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateItemTotal($get, $set)),
                                
                                Forms\Components\TextInput::make('tax')
                                    ->label('Tax %')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateItemTotal($get, $set)),
                                
                                Forms\Components\TextInput::make('amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated(),
                                
                                Forms\Components\Select::make('account')
                                    ->label('Account')
                                    ->options(
                                        SalesAccount::where('team_id', Filament::getTenant()->id)
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(8)
                            ->defaultItems(1)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible()
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()
                            )
                            ->addActionLabel('Add Item')
                            ->hintActions([
                                Action::make('apply_account_to_all')
                                    ->icon('heroicon-o-cog')
                                    ->label('Apply Account to All')
                                    ->form([
                                        Forms\Components\Select::make('account')
                                            ->options(
                                                SalesAccount::where('team_id', Filament::getTenant()->id)
                                                    ->pluck('name', 'id')
                                            )
                                            ->required(),
                                    ])
                                    ->action(function ($data, $get, $set) {
                                        $items = $get('items');
                                        $updatedItems = [];
                                        
                                        foreach ($items as $item) {
                                            $item['account'] = $data['account'];
                                            $updatedItems[] = $item;
                                        }
                                        
                                        $set('items', $updatedItems);
                                    }),
                                
                                Action::make('calculate_all_totals')
                                    ->icon('heroicon-o-calculator')
                                    ->label('Calculate All Totals')
                                    ->action(function ($get, $set) {
                                        $items = $get('items');
                                        $updatedItems = [];
                                        
                                        foreach ($items as $item) {
                                            $total = self::calculateSingleItemTotal(
                                                $item['quantity'] ?? 1,
                                                $item['rate'] ?? 0,
                                                $item['tax'] ?? 0,
                                                $item['discount'] ?? 0
                                            );
                                            $item['amount'] = $total;
                                            $updatedItems[] = $item;
                                        }
                                        
                                        $set('items', $updatedItems);
                                        self::calculateTotals($get, $set);
                                    }),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Totals')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Placeholder::make('sub_total')
                                    ->label('Subtotal')
                                    ->content(function ($get) {
                                        $subtotal = 0;
                                        foreach ($get('items') ?? [] as $item) {
                                            $itemTotal = self::calculateSingleItemTotal(
                                                $item['quantity'] ?? 1,
                                                $item['rate'] ?? 0,
                                                0, // Don't include tax in subtotal
                                                $item['discount'] ?? 0
                                            );
                                            $subtotal += $itemTotal;
                                        }
                                        return '€' . number_format($subtotal, 2);
                                    }),
                                
                                Forms\Components\TextInput::make('discount_amount')
                                    ->label('Total Discount')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->suffix('€')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateTotals($get, $set)),
                                
                                Forms\Components\Placeholder::make('total_tax')
                                    ->label('Total Tax')
                                    ->content(function ($get) {
                                        $totalTax = 0;
                                        foreach ($get('items') ?? [] as $item) {
                                            $itemSubtotal = ($item['quantity'] ?? 1) * ($item['rate'] ?? 0);
                                            $itemDiscount = ($item['discount'] ?? 0) / 100 * $itemSubtotal;
                                            $itemSubtotal -= $itemDiscount;
                                            $itemTax = ($item['tax'] ?? 0) / 100 * $itemSubtotal;
                                            $totalTax += $itemTax;
                                        }
                                        return '€' . number_format($totalTax, 2);
                                    }),
                                
                                Forms\Components\TextInput::make('adjustment')
                                    ->label('Adjustment')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->suffix('€')
                                    ->helperText('Positive for addition, negative for deduction')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateTotals($get, $set)),
                                
                                Forms\Components\TextInput::make('total')
                                    ->label('Grand Total')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('€')
                                    ->dehydrated(),
                                
                                Forms\Components\Hidden::make('balance_due'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Notes & Terms')
                    ->schema([
                        Forms\Components\Textarea::make('customer_notes')
                            ->label('Customer Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('terms_and_conditions')
                            ->label('Terms & Conditions')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                Forms\Components\Hidden::make('team_id')
                    ->default(Filament::getTenant()->id),
                Forms\Components\Hidden::make('original_customer_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('customer.company_display_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tax' => 'success',
                        'proforma' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tax' => 'Tax Invoice',
                        'proforma' => 'Proforma',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('total')
                    ->sortable()
                    ->alignRight(),
                
                Tables\Columns\TextColumn::make('balance_due')
                    ->sortable()
                    ->alignRight()
                    ->color(fn ($record) => $record->balance_due > 0 ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->balance_due <= 0 ? 'Paid' : 'Pending'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'tax' => 'Tax Invoice',
                        'proforma' => 'Proforma',
                    ]),
                
                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'company_display_name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())),
                
                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('balance_due')
                            ->icon('heroicon-o-currency-euro'),
                        NumberConstraint::make('total')
                            ->icon('heroicon-o-currency-euro'),
                    ]),
            ])
            ->actions([
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Invoice')
                    ->modalDescription('Are you sure you want to delete this invoice? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->recordUrl(fn ($record) => Pages\ViewInvoice::getUrl([$record]));
    }

    public static function getRelations(): array
    {
        return [
            // Add relations if needed, e.g., payments relation
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoices::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoices::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('team_id', Filament::getTenant()->id)
            ->where('balance_due', '>', 0)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /**
     * Calculate single item total
     */
    private static function calculateSingleItemTotal($quantity, $rate, $tax, $discount = 0): float
    {
        $quantity = floatval($quantity);
        $rate = floatval($rate);
        $tax = floatval($tax);
        $discount = floatval($discount);
        
        $subtotal = $quantity * $rate;
        
        // Apply discount
        if ($discount > 0) {
            $discountAmount = ($discount / 100) * $subtotal;
            $subtotal -= $discountAmount;
        }
        
        // Apply tax
        if ($tax > 0) {
            $taxAmount = ($tax / 100) * $subtotal;
            $subtotal += $taxAmount;
        }
        
        return round($subtotal, 2);
    }

    /**
     * Calculate item total and update the field
     */
    private static function calculateItemTotal($get, $set): void
    {
        $quantity = floatval($get('quantity')) ?: 0;
        $rate = floatval($get('rate')) ?: 0;
        $tax = floatval($get('tax')) ?: 0;
        $discount = floatval($get('discount')) ?: 0;
        
        $total = self::calculateSingleItemTotal($quantity, $rate, $tax, $discount);
        $set('amount', $total);
        
        // Also update global totals
        self::calculateTotals($get, $set);
    }

    /**
     * Calculate all totals (subtotal, tax, grand total, balance due)
     */
    private static function calculateTotals($get, $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;
        
        foreach ($items as $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $rate = floatval($item['rate'] ?? 0);
            $tax = floatval($item['tax'] ?? 0);
            $discount = floatval($item['discount'] ?? 0);
            
            $itemSubtotal = $quantity * $rate;
            
            // Calculate discount for this item
            if ($discount > 0) {
                $itemDiscount = ($discount / 100) * $itemSubtotal;
                $totalDiscount += $itemDiscount;
                $itemSubtotal -= $itemDiscount;
            }
            
            // Calculate tax for this item
            if ($tax > 0) {
                $itemTax = ($tax / 100) * $itemSubtotal;
                $totalTax += $itemTax;
                $itemSubtotal += $itemTax;
            }
            
            $subtotal += $itemSubtotal;
        }
        
        // Apply global discount and adjustment
        $globalDiscount = floatval($get('discount_amount') ?? 0);
        $adjustment = floatval($get('adjustment') ?? 0);
        
        $grandTotal = $subtotal - $globalDiscount + $adjustment;
        
        $set('sub_total', round($subtotal - $totalTax, 2)); // Subtotal without tax
        $set('total', round($grandTotal, 2));
        $set('balance_due', round($grandTotal, 2)); // Initially balance due equals total
    }
}
