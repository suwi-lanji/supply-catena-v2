<?php

namespace App\Filament\Resources\PaymentsMadeResource\Pages;

use App\Filament\Resources\PaymentsMadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentsMades extends ListRecords
{
    protected static string $resource = PaymentsMadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
