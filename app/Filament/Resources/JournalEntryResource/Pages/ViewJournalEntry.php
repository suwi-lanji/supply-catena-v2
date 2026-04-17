<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('post')
                ->label('Post Entry')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->canBePosted())
                ->requiresConfirmation()
                ->modalHeading('Post Journal Entry')
                ->modalDescription('Are you sure you want to post this journal entry? This will update all account balances.')
                ->action(function () {
                    $service = app(JournalEntryService::class);
                    try {
                        $service->post($this->record, auth()->id());
                        Notification::make()
                            ->title('Success')
                            ->body('Journal entry posted successfully.')
                            ->success()
                            ->send();
                        $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('void')
                ->label('Void Entry')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->canBeVoided())
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\TextInput::make('reason')
                        ->label('Void Reason')
                        ->required()
                        ->maxLength(255),
                ])
                ->modalHeading('Void Journal Entry')
                ->action(function (array $data) {
                    $service = app(JournalEntryService::class);
                    try {
                        $service->void($this->record, auth()->id(), $data['reason']);
                        Notification::make()
                            ->title('Success')
                            ->body('Journal entry voided successfully.')
                            ->success()
                            ->send();
                        $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\EditAction::make()
                ->visible(fn (): bool => $this->record->status === JournalEntry::STATUS_DRAFT),
        ];
    }
}
