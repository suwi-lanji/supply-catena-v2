<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryNote extends ViewRecord
{
    protected static string $resource = DeliveryNoteResource::class;
    protected static string $view = 'filament.resources.delivery-notes.pages.view-delivery-note';
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function ($record) {
                    return redirect('/delivery-note/'.\Filament\Facades\Filament::getTenant()->id.'/'.$record->id);
                }),
        ];
    }
}
