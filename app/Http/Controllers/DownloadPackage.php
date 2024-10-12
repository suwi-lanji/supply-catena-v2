<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function Spatie\LaravelPdf\Support\pdf;
use App\Models\Packages;
use App\Models\Team;
class DownloadPackage extends Controller
{
    public function __invoke(Team $tenant,Packages $record)
    {
        return pdf()
            ->view('pdf-package', ['record' => $record, 'tenant' => $tenant])
            ->name('invoice-2023-04-10.pdf');
    }
}