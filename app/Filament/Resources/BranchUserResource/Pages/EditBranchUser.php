<?php

namespace App\Filament\Resources\BranchUserResource\Pages;

use App\Filament\Resources\BranchUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranchUser extends EditRecord
{
    protected static string $resource = BranchUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
