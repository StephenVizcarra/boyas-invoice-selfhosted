<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SenderController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\InvoiceController;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/sender', [SenderController::class, 'show']);
    Route::post('/sender', [SenderController::class, 'update']);
    Route::get('/sender/logo', [SenderController::class, 'getLogo']);
    Route::post('/sender/logo', [SenderController::class, 'uploadLogo']);
    Route::delete('/sender/logo', [SenderController::class, 'deleteLogo']);

    Route::get('/recipients', [RecipientController::class, 'index']);
    Route::post('/recipients', [RecipientController::class, 'store']);
    Route::delete('/recipients/{id}', [RecipientController::class, 'destroy']);
});

// Tighter limit for PDF generation (CPU-heavy)
Route::post('/invoice/generate', [InvoiceController::class, 'generate'])
    ->middleware('throttle:10,1');
