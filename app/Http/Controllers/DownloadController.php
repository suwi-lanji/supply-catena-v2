<?php

namespace App\Http\Controllers;

use App\Models\Packages;
use App\Models\Quotation;
use App\Models\Team;

use function Spatie\LaravelPdf\Support\pdf;

class DownloadController extends Controller
{
    public function download_package(Team $tenant, Packages $record)
    {
        return pdf()
            ->view('pdf-package', ['record' => $record, 'tenant' => $tenant])
            ->name($record->package_slip.'.pdf');
    }

    public function download_quotation(Team $tenant, Quotation $record)
    {
        return pdf()
            ->view('pdf-quotation', ['record' => $record, 'tenant' => $tenant])
            ->name($record->quotation_number.'.pdf');
    }
}
