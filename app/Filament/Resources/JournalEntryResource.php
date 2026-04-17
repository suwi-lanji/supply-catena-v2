<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerAccount;
use App\Services\Accounting\JournalEntryService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Journal Entry';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Entry Details')
                    ->schema([
                        Forms\Components\DatePicker::make('entry_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\TextInput::make('entry_number')
                            ->label('Entry Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpan(2),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Entry Lines')
                    ->description('Debits must equal credits for a balanced entry.')
                    ->schema([
                        TableRepeater::make('lines')
                            ->relationship('lines')
                            ->schema([
                                Forms\Components\Select::make('ledger_account_id')
                                    ->label('Account')
                                    ->options(LedgerAccount::where('team_id', Filament::getTenant()->id)
                                        ->where('is_active', true)
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($account) => [$account->id => "{$account->code} - {$account->name}"])
                                    )
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        JournalEntryLine::TYPE_DEBIT => 'Debit',
                                        JournalEntryLine::TYPE_CREDIT => 'Credit',
                                    ])
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->minValue(0.01),
                                Forms\Components\TextInput::make('description')
                                    ->maxLength(255),
                            ])
                            ->columnSpanFull()
                            ->reorderable()
                            ->defaultItems(2)
                            ->addActionLabel('Add Line'),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('calculate_totals')
                                ->label('Calculate Totals')
                                ->icon('heroicon-o-calculator')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    $lines = $get('lines') ?? [];
                                    $debits = 0;
                                    $credits = 0;

                                    foreach ($lines as $line) {
                                        $amount = floatval($line['amount'] ?? 0);
                                        if ($line['type'] ?? null === 'debit') {
                                            $debits += $amount;
                                        } elseif ($line['type'] ?? null === 'credit') {
                                            $credits += $amount;
                                        }
                                    }

                                    $set('total_debits', number_format($debits, 2, '.', ''));
                                    $set('total_credits', number_format($credits, 2, '.', ''));
                                    $set('difference', number_format(abs($debits - $credits), 2, '.', ''));
                                }),
                        ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('total_debits')
                                    ->label('Total Debits')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric()
                                    ->afterStateHydrated(function (Forms\Set $set, $state, $record) {
                                        if ($record) {
                                            $set('total_debits', number_format($record->getTotalDebits(), 2, '.', ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('total_credits')
                                    ->label('Total Credits')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric()
                                    ->afterStateHydrated(function (Forms\Set $set, $state, $record) {
                                        if ($record) {
                                            $set('total_credits', number_format($record->getTotalCredits(), 2, '.', ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('difference')
                                    ->label('Difference')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric()
                                    ->afterStateHydrated(function (Forms\Set $set, $state, $record) {
                                        if ($record) {
                                            $set('difference', number_format(abs($record->getTotalDebits() - $record->getTotalCredits()), 2, '.', ''));
                                        }
                                    }),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        JournalEntry::STATUS_DRAFT => 'warning',
                        JournalEntry::STATUS_POSTED => 'success',
                        JournalEntry::STATUS_VOIDED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('total_debits')
                    ->label('Debits')
                    ->money('ZMW')
                    ->getStateUsing(fn (JournalEntry $record): float => $record->getTotalDebits()),
                Tables\Columns\TextColumn::make('total_credits')
                    ->label('Credits')
                    ->money('ZMW')
                    ->getStateUsing(fn (JournalEntry $record): float => $record->getTotalCredits()),
                Tables\Columns\IconColumn::make('is_balanced')
                    ->label('Balanced')
                    ->boolean()
                    ->getStateUsing(fn (JournalEntry $record): bool => $record->isBalanced()),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        JournalEntry::STATUS_DRAFT => 'Draft',
                        JournalEntry::STATUS_POSTED => 'Posted',
                        JournalEntry::STATUS_VOIDED => 'Voided',
                    ]),
                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('entry_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('entry_date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (JournalEntry $record): bool => $record->canBePosted())
                    ->requiresConfirmation()
                    ->modalHeading('Post Journal Entry')
                    ->modalDescription('Are you sure you want to post this journal entry? This action cannot be undone.')
                    ->action(function (JournalEntry $record) {
                        $service = app(JournalEntryService::class);
                        try {
                            $service->post($record, auth()->id());
                            \Filament\Notifications\Notification::make()
                                ->title('Success')
                                ->body('Journal entry posted successfully.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (JournalEntry $record): bool => $record->canBeVoided())
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('reason')
                            ->label('Void Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->modalHeading('Void Journal Entry')
                    ->action(function (JournalEntry $record, array $data) {
                        $service = app(JournalEntryService::class);
                        try {
                            $service->void($record, auth()->id(), $data['reason']);
                            \Filament\Notifications\Notification::make()
                                ->title('Success')
                                ->body('Journal entry voided successfully.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn (JournalEntry $record): bool => $record->status !== JournalEntry::STATUS_DRAFT),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (JournalEntry $record): bool => $record->status !== JournalEntry::STATUS_DRAFT),
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
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'view' => Pages\ViewJournalEntry::route('/{record}'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
