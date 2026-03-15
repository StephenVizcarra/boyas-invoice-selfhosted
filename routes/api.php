<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SenderController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\InvoiceController;

Route::get('/sender', [SenderController::class, 'show']);
Route::post('/sender', [SenderController::class, 'update']);
Route::post('/sender/logo', [SenderController::class, 'uploadLogo']);

Route::get('/recipients', [RecipientController::class, 'index']);
Route::post('/recipients', [RecipientController::class, 'store']);
Route::delete('/recipients/{id}', [RecipientController::class, 'destroy']);

Route::post('/invoice/generate', [InvoiceController::class, 'generate']);
