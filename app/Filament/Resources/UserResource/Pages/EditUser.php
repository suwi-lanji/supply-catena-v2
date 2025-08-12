<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->color('danger')->icon('heroicon-o-trash'),
            Actions\ActionGroup::make([
                Actions\Action::make('make_user_admin')
                    ->visible(fn ($record): bool => ! $record->administrating()->whereKey(Filament::getTenant()->id)->exists())
                    ->action(function ($record) {
                        $created = User::where('id', $record->id)->update(['is_admin' => true]);

                        return $created;
                    })->requiresConfirmation(),
                Actions\Action::make('remove_admin_role')
                    ->visible(fn ($record): bool => $record->administrating()->whereKey(Filament::getTenant()->id)->exists())
                    ->action(function ($record) {
                        $created = User::where('id', $record->id)->update(['is_admin' => false]);

                        return $created;
                    })->requiresConfirmation(),
            ])
                ->label('User Roles')
                ->button()
                ->color('default'),

        ];
    }
}
