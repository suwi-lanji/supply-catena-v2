<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DndTemplateMaker;
use App\Http\Controllers\PDFController;
Route::view('/', 'landing-page');
Route::get('/content/{filename}', [ContentController::class, 'show']);
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
Route::get('/make-template', [DndTemplateMaker::class, 'loadForm']);
Route::post('/save-template', [DndTemplateMaker::class, 'saveTemplate']);
Route::get('/documents/bill/{tenant_id}/{document_id}/{data_id}', [DocumentController::class, 'showBill']);
Route::get('/save-pdf', [PdfController::class, 'generatePdf'])->name('generate-pdf');

require __DIR__.'/auth.php';
