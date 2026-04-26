<?php

use App\Http\Controllers\Print\LocationLabelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/locations/print-label/{location}', [LocationLabelController::class, 'printSingle'])
        ->name('locations.print-label');
    Route::get('/locations/print-labels', [LocationLabelController::class, 'printBulk'])
        ->name('locations.print-labels');
});
