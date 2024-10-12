<?php

namespace App\Filament\Resources\VendorCreditResource\Pages;

use App\Filament\Resources\VendorCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorCredits extends ListRecords
{
    protected static string $resource = VendorCreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
