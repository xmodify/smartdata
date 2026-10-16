<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MophF5ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Version 1 (Standard)
Route::prefix('v1/moph-f5')->group(function () {
    Route::get('/health', [MophF5ApiController::class, 'health'])->name('api.v1.moph_f5.health');
    Route::post('/upload', [MophF5ApiController::class, 'upload'])->middleware('throttle:60,1')->name('api.v1.moph_f5.upload');
});

// Shorthand alias without v1
Route::prefix('moph-f5')->group(function () {
    Route::get('/health', [MophF5ApiController::class, 'health'])->name('api.moph_f5.health');
    Route::post('/upload', [MophF5ApiController::class, 'upload'])->middleware('throttle:60,1')->name('api.moph_f5.upload');
});
