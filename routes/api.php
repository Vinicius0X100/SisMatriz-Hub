<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaCalendarController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('vicentinos')->group(function () {
        Route::get('/apuracoes', [\App\Http\Controllers\Api\VicentinoApiController::class, 'getApuracoes']);
        Route::get('/fichas', [\App\Http\Controllers\Api\VicentinoApiController::class, 'getFichas']);
    });
});

Route::middleware('auth:web')->group(function () {
    // Routes moved to web.php to share session state
});
