<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaCalendarController;
use App\Http\Controllers\Api\VicentinoApiController;
use App\Http\Controllers\Api\CatequeseApiController;
use App\Http\Controllers\Api\AcolitoApiController;

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
    Route::get('/{tipo}/turmas/{turma_id}/attendance-history/{student_id}', [CatequeseApiController::class, 'getAttendanceHistory']);
});

// Rotas de API para validação de bilhetes de Excursão
Route::middleware(['web', 'auth:web'])->prefix('excursoes')->group(function () {
    // Consulta bilhete pelo ID do assento (decodificado do QR Code)
    Route::get('/bilhete/{id}', [\App\Http\Controllers\Api\ExcursaoApiController::class, 'consultarBilhete']);
    // Valida (marca como usado) o bilhete
    Route::post('/bilhete/{id}/validar', [\App\Http\Controllers\Api\ExcursaoApiController::class, 'validarBilhete']);
    // Lista os bilhetes validados de um ônibus
    Route::get('/{excursao}/onibus/{onibus}/validados', [\App\Http\Controllers\Api\ExcursaoApiController::class, 'listarValidados']);
});

// Rotas de API para o módulo de Acólitos
Route::middleware(['web', 'auth:web'])->prefix('acolitos')->group(function () {
    // Lista todas as escalas da paróquia
    Route::get('/escalas', [AcolitoApiController::class, 'getEscalas']);
    // Detalhe completo de uma escala com celebrações, acólitos e funções
    Route::get('/escalas/{id}', [AcolitoApiController::class, 'getEscalaDetalhe']);
    // Celebrações de uma escala (com filtro opcional por ?dia=<1-31>)
    Route::get('/escalas/{id}/celebracoes', [AcolitoApiController::class, 'getCelebracoes']);
    // Dias em que o usuário logado vai servir
    Route::get('/meus-servicos', [AcolitoApiController::class, 'getMeusServicos']);
    // Lista de funções cadastradas na paróquia
    Route::get('/funcoes', [AcolitoApiController::class, 'getFuncoes']);
});
