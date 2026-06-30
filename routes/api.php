<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaCalendarController;
use App\Http\Controllers\Api\VicentinoApiController;

use App\Http\Controllers\Api\CatequeseApiController;

Route::get('/user', function (Request $request) {
    return auth()->user();
})->middleware('web');

Route::middleware('auth:web')->group(function () {
    // Routes moved to web.php to share session state
});

// Rota para o app Android/iOS buscar dados do perfil + CSRF Token
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

// Novas rotas do módulo Vicentinos
Route::middleware(['web', 'auth:web'])->prefix('vicentinos')->group(function () {
    Route::get('/apuracoes', [VicentinoApiController::class, 'getApuracoes']);
    Route::get('/fichas', [VicentinoApiController::class, 'getFichas']);
});

// Novas rotas de API para os módulos de Catequese (Eucaristia, Crisma, Adultos)
Route::middleware(['web', 'auth:web'])->prefix('catequese')->group(function () {
    Route::get('/{tipo}/turmas', [CatequeseApiController::class, 'getTurmas']);
    Route::get('/{tipo}/turmas/{id}/attendance', [CatequeseApiController::class, 'getAttendance']);
    Route::post('/{tipo}/turmas/attendance/save', [CatequeseApiController::class, 'saveAttendance']);
    Route::post('/{tipo}/turmas/attendance/save-bulk', [CatequeseApiController::class, 'saveBulkAttendance']);
});
