<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerAccountResource\Pages;
use App\Models\LedgerAccount;
use App\Services\Accounting\ChartOfAccountsService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LedgerAccountResource extends Resource
{
    protected static ?string $model = LedgerAccount::class;

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Ledger Account';

    protected static ?string $pluralModelLabel = 'Chart of Accounts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Details')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                return $rule->where('team_id', Filament::getTenant()->id);
                            }),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                LedgerAccount::TYPE_ASSET => 'Asset',
                                LedgerAccount::TYPE_LIABILITY => 'Liability',
                                LedgerAccount::TYPE_EQUITY => 'Equity',
                                LedgerAccount::TYPE_REVENUE => 'Revenue',
                                LedgerAccount::TYPE_EXPENSE => 'Expense',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('sub_type', null)),
                        Forms\Components\Select::make('sub_type')
                            ->options(function (Forms\Get $get) {
                                $type = $get('type');
                                if (!$type) {
                                    return [];
                                }

                                $subTypes = LedgerAccount::getSubTypesForType($type);
                                return array_combine($subTypes, array_map('ucwords', str_replace('_', ' ', $subTypes)));
                            })
                            ->searchable(),
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Account')
                            ->relationship('parent', 'name', modifyQueryUsing: function (Builder $query) {
                                return $query->where('team_id', Filament::getTenant()->id);
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Balances')
                    ->schema([
                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->default(0)
                            ->step(0.01),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LedgerAccount::TYPE_ASSET => 'success',
                        LedgerAccount::TYPE_LIABILITY => 'danger',
                        LedgerAccount::TYPE_EQUITY => 'warning',
                        LedgerAccount::TYPE_REVENUE => 'info',
                        LedgerAccount::TYPE_EXPENSE => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('sub_type')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucwords(str_replace('_', ' ', $state)) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('current_balance')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        LedgerAccount::TYPE_ASSET => 'Asset',
                        LedgerAccount::TYPE_LIABILITY => 'Liability',
                        LedgerAccount::TYPE_EQUITY => 'Equity',
                        LedgerAccount::TYPE_REVENUE => 'Revenue',
                        LedgerAccount::TYPE_EXPENSE => 'Expense',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (LedgerAccount $record): bool => $record->is_system || $record->transactions()->exists()),
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
            'index' => Pages\ListLedgerAccounts::route('/'),
            'create' => Pages\CreateLedgerAccount::route('/create'),
            'edit' => Pages\EditLedgerAccount::route('/{record}/edit'),
        ];
    }
}
