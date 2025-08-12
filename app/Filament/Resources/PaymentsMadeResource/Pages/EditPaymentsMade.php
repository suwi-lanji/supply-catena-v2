<?php

namespace App\Filament\Resources\PaymentsMadeResource\Pages;

use App\Filament\Resources\PaymentsMadeResource;
use Filament\Resources\Pages\EditRecord;

class EditPaymentsMade extends EditRecord
{
    protected static string $resource = PaymentsMadeResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
