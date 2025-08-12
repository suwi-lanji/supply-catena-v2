<?php

namespace App\Filament\Resources\SalesReceiptResource\Pages;

use App\Filament\Resources\SalesReceiptResource;
use Filament\Resources\Pages\EditRecord;

class EditSalesReceipt extends EditRecord
{
    protected static string $resource = SalesReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
