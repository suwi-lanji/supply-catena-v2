<?php

namespace App\Filament\Resources\VendorCreditResource\Pages;

use App\Filament\Resources\VendorCreditResource;
use Filament\Resources\Pages\EditRecord;

class EditVendorCredit extends EditRecord
{
    protected static string $resource = VendorCreditResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
