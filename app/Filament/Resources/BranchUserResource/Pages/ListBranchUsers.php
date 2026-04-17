<?php

namespace App\Filament\Resources\BranchUserResource\Pages;

use App\Filament\Resources\BranchUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranchUsers extends ListRecords
{
    protected static string $resource = BranchUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
