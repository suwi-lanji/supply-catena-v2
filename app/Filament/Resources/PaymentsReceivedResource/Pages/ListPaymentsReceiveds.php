<?php

namespace App\Filament\Resources\PaymentsReceivedResource\Pages;

use App\Filament\Resources\PaymentsReceivedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentsReceiveds extends ListRecords
{
    protected static string $resource = PaymentsReceivedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
