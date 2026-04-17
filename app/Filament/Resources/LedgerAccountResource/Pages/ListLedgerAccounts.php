<?php

namespace App\Filament\Resources\LedgerAccountResource\Pages;

use App\Filament\Resources\LedgerAccountResource;
use App\Services\Accounting\ChartOfAccountsService;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListLedgerAccounts extends ListRecords
{
    protected static string $resource = LedgerAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('initialize_defaults')
                ->label('Initialize Default Accounts')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Initialize Default Chart of Accounts')
                ->modalDescription('This will create a standard chart of accounts for your organization. Only use this if you have not already set up your accounts.')
                ->action(function () {
                    $team = Filament::getTenant();
                    $service = app(ChartOfAccountsService::class);

                    try {
                        $count = $service->initializeDefaultAccounts($team)->count();
                        Notification::make()
                            ->title('Success')
                            ->body("Initialized {$count} default accounts.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
