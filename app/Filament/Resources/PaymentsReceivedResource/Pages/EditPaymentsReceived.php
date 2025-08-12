<?php

namespace App\Filament\Resources\PaymentsReceivedResource\Pages;

use App\Filament\Resources\PaymentsReceivedResource;
use Filament\Resources\Pages\EditRecord;

class EditPaymentsReceived extends EditRecord
{
    protected static string $resource = PaymentsReceivedResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
