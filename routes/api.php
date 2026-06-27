<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaCalendarController;
use App\Http\Controllers\Api\VicentinoApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rota usada pelo app Android (mantém compatibilidade)
Route::get('/user', function (Request $request) {
    return auth()->user();
})->middleware('web');

// Rotas protegidas por Sanctum (novo módulo)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user-auth', function (Request $request) {
        return $request->user();
    });

    Route::prefix('vicentinos')->group(function () {
        Route::get('/apuracoes', [VicentinoApiController::class, 'getApuracoes']);
        Route::get('/fichas', [VicentinoApiController::class, 'getFichas']);
    });
});

// Mantido para compartilhamento da sessão web
Route::middleware('auth:web')->group(function () {
    // Routes moved to web.php to share session state
});

// Rota utilizada pelo aplicativo Android para obter
// dados do usuário autenticado e um novo CSRF Token
Route::middleware('web')->get('/profile-data', function (Request $request) {

    if (!auth()->check()) {
        return response()->json([
            'authenticated' => false,
        ], 401);
    }

    /** @var \App\Models\User $user */
    $user = auth()->user();

    return response()->json([
        'authenticated' => true,
        'name'          => $user->name ?? '',
        'email'         => $user->email ?? '',
        'csrf_token'    => csrf_token(),
    ]);
});