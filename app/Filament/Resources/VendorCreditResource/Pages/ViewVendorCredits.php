<?php

namespace App\Filament\Resources\VendorCreditResource\Pages;

use App\Filament\Resources\VendorCreditResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewVendorCredits extends ViewRecord
{
    protected static string $resource = VendorCreditResource::class;

    protected static string $view = 'filament.resources.vendor-credits.pages.view-vendor-credits';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit')->color('default')
                ->color('default'),
            Actions\Action::make('pdf')
                ->label('PDF/Print')
                ->color('default')
                ->icon('heroicon-s-arrow-down-tray')
                ->action(function (Model $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo Pdf::loadView('pdf-vendor-credits', ['record' => $record, 'tenant' => Filament::getTenant()])->setOptions([
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'DOMPDF_ENABLE_HTML5PARSER' => true,
                            'chroot' => public_path(),
                            'fontDir' => storage_path('fonts/'),
                            'isRemoteEnabled' => true,
                            'css' => file_get_contents(public_path('css/purchase-order.css')),
                        ])->stream();
                    }, $record->credit_note_number.'.pdf');
                }),
        ];
    }
}
