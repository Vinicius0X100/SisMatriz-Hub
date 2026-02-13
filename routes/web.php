<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CelebrationScheduleController;
use App\Http\Middleware\CheckOnboarding;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\ProfileController;

Route::middleware(['auth', CheckOnboarding::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/toggle-pin', [DashboardController::class, 'togglePin'])->name('dashboard.toggle-pin');
    Route::post('/dashboard/reorder-pins', [DashboardController::class, 'reorderPins'])->name('dashboard.reorder-pins');
    Route::post('/dashboard/update-pin-style', [DashboardController::class, 'updatePinStyle'])->name('dashboard.update-pin-style');
    Route::get('/dashboard/online-users', [DashboardController::class, 'getOnlineUsers'])->name('dashboard.online-users');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/privacy', [App\Http\Controllers\SettingsController::class, 'updatePrivacy'])->name('settings.update.privacy');
    Route::put('/settings/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('settings.update.password');

    // Chat
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/users', [App\Http\Controllers\ChatController::class, 'getUsers'])->name('chat.users');
    Route::get('/chat/messages/{userId}', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/block', [App\Http\Controllers\ChatController::class, 'blockUser'])->name('chat.block');
    Route::post('/chat/unblock', [App\Http\Controllers\ChatController::class, 'unblockUser'])->name('chat.unblock');
    Route::post('/chat/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');

    // Excursões
    Route::resource('excursoes', App\Http\Controllers\ExcursaoController::class)->parameters([
        'excursoes' => 'excursao'
    ]);
    Route::get('excursoes/{excursao}/onibus/create', [App\Http\Controllers\OnibusController::class, 'create'])->name('excursoes.onibus.create');
    Route::post('excursoes/{excursao}/onibus', [App\Http\Controllers\OnibusController::class, 'store'])->name('excursoes.onibus.store');
    Route::delete('excursoes/onibus/{onibus}', [App\Http\Controllers\OnibusController::class, 'destroy'])->name('excursoes.onibus.destroy');
    Route::get('excursoes/onibus/{onibus}/passageiros', [App\Http\Controllers\OnibusController::class, 'passageiros'])->name('excursoes.onibus.passageiros');
    Route::post('excursoes/onibus/{onibus}/passageiros', [App\Http\Controllers\OnibusController::class, 'storePassageiro'])->name('excursoes.onibus.passageiros.store');
    Route::delete('excursoes/passageiros/{passageiro}', [App\Http\Controllers\OnibusController::class, 'destroyPassageiro'])->name('excursoes.passageiros.destroy');
    Route::get('excursoes/onibus/{onibus}/pdf', [App\Http\Controllers\OnibusPdfController::class, 'generate'])->name('excursoes.onibus.pdf');

    // Módulos Internos
    // Registros Gerais
    Route::get('registers/search', [RegisterController::class, 'searchPeople'])->name('registers.search');
    Route::post('registers/pdf', [RegisterController::class, 'generatePdf'])->name('registers.pdf');
    Route::get('registers/check-phone', [RegisterController::class, 'checkPhone'])->name('registers.check-phone');
    Route::delete('registers/attachments/{id}', [RegisterController::class, 'destroyAttachment'])->name('registers.attachments.destroy');
    Route::resource('registers', RegisterController::class);

    // Batismos
    Route::resource('batismos', App\Http\Controllers\BatismoController::class);

    // Lembretes
    Route::get('lembretes/check', [App\Http\Controllers\LembreteController::class, 'checkDue'])->name('lembretes.check');
    Route::post('lembretes/{id}/snooze', [App\Http\Controllers\LembreteController::class, 'snooze'])->name('lembretes.snooze');
    Route::resource('lembretes', App\Http\Controllers\LembreteController::class);

    // Catequese
    Route::resource('catequese-eucaristia', App\Http\Controllers\CatequeseEucaristiaController::class);
    Route::resource('catequese-crisma', App\Http\Controllers\CatequeseCrismaController::class);
    Route::resource('catequistas-eucaristia', App\Http\Controllers\CatequistasEucaristiaController::class);
    Route::resource('catequistas-crisma', App\Http\Controllers\CatequistasCrismaController::class);
    
    // Turmas Eucaristia
    Route::get('turmas-eucaristia/{id}/students', [App\Http\Controllers\TurmasEucaristiaController::class, 'getStudents'])->name('turmas-eucaristia.students');
    Route::get('turmas-eucaristia/export-bulk', [App\Http\Controllers\TurmasEucaristiaController::class, 'exportBulk'])->name('turmas-eucaristia.export-bulk');
    Route::get('turmas-eucaristia/{id}/export', [App\Http\Controllers\TurmasEucaristiaController::class, 'exportStudents'])->name('turmas-eucaristia.export');
    Route::post('turmas-eucaristia/transfer', [App\Http\Controllers\TurmasEucaristiaController::class, 'transferStudent'])->name('turmas-eucaristia.transfer');
    Route::get('turmas-eucaristia/{id}/attendance', [App\Http\Controllers\TurmasEucaristiaController::class, 'getAttendance'])->name('turmas-eucaristia.attendance');
    Route::post('turmas-eucaristia/attendance/save', [App\Http\Controllers\TurmasEucaristiaController::class, 'saveAttendance'])->name('turmas-eucaristia.attendance.save');
    Route::post('turmas-eucaristia/attendance/save-bulk', [App\Http\Controllers\TurmasEucaristiaController::class, 'saveBulkAttendance'])->name('turmas-eucaristia.attendance.save-bulk');
    Route::get('turmas-eucaristia/{id}/attendance-analysis', [App\Http\Controllers\TurmasEucaristiaController::class, 'attendanceAnalysis'])->name('turmas-eucaristia.attendance-analysis');
    Route::get('turmas-eucaristia/{id}/attendance-history/{student_id}', [App\Http\Controllers\TurmasEucaristiaController::class, 'attendanceHistory'])->name('turmas-eucaristia.attendance-history');
    Route::post('turmas-eucaristia/attendance/justify', [App\Http\Controllers\TurmasEucaristiaController::class, 'storeJustification'])->name('turmas-eucaristia.attendance.justify');
    Route::resource('turmas-eucaristia', App\Http\Controllers\TurmasEucaristiaController::class);
    
    // Turmas Crisma
    Route::get('turmas-crisma/{id}/students', [App\Http\Controllers\TurmasCrismaController::class, 'getStudents'])->name('turmas-crisma.students');
    Route::get('turmas-crisma/export-bulk', [App\Http\Controllers\TurmasCrismaController::class, 'exportBulk'])->name('turmas-crisma.export-bulk');
    Route::get('turmas-crisma/{id}/export', [App\Http\Controllers\TurmasCrismaController::class, 'exportStudents'])->name('turmas-crisma.export');
    Route::post('turmas-crisma/transfer', [App\Http\Controllers\TurmasCrismaController::class, 'transferStudent'])->name('turmas-crisma.transfer');
    Route::get('turmas-crisma/{id}/attendance', [App\Http\Controllers\TurmasCrismaController::class, 'getAttendance'])->name('turmas-crisma.attendance');
    Route::post('turmas-crisma/attendance/save', [App\Http\Controllers\TurmasCrismaController::class, 'saveAttendance'])->name('turmas-crisma.attendance.save');
    Route::post('turmas-crisma/attendance/save-bulk', [App\Http\Controllers\TurmasCrismaController::class, 'saveBulkAttendance'])->name('turmas-crisma.attendance.save-bulk');
    Route::get('turmas-crisma/{id}/attendance-analysis', [App\Http\Controllers\TurmasCrismaController::class, 'attendanceAnalysis'])->name('turmas-crisma.attendance-analysis');
    Route::get('turmas-crisma/{id}/attendance-history/{student_id}', [App\Http\Controllers\TurmasCrismaController::class, 'attendanceHistory'])->name('turmas-crisma.attendance-history');
    Route::post('turmas-crisma/attendance/justify', [App\Http\Controllers\TurmasCrismaController::class, 'storeJustification'])->name('turmas-crisma.attendance.justify');
    Route::resource('turmas-crisma', App\Http\Controllers\TurmasCrismaController::class);

    // Catequese Adultos
    Route::resource('catequese-adultos', App\Http\Controllers\CatequeseAdultosController::class);
    Route::resource('catequistas-adultos', App\Http\Controllers\CatequistasAdultosController::class);

    // Solicitações de Segunda Via
    Route::post('solicitacoes-segunda-via/bulk-action', [App\Http\Controllers\SacramentoRequestController::class, 'bulkAction'])->name('solicitacoes-segunda-via.bulk-action');
    Route::get('solicitacoes-segunda-via/{id}/print-sheet', [App\Http\Controllers\SacramentoRequestController::class, 'printSheet'])->name('solicitacoes-segunda-via.print-sheet');
    Route::post('solicitacoes-segunda-via/{id}/status', [App\Http\Controllers\SacramentoRequestController::class, 'updateStatus'])->name('solicitacoes-segunda-via.update-status');
    Route::resource('solicitacoes-segunda-via', App\Http\Controllers\SacramentoRequestController::class);

    // Turmas Adultos
    Route::get('turmas-adultos/{id}/students', [App\Http\Controllers\TurmasAdultosController::class, 'getStudents'])->name('turmas-adultos.students');
    Route::get('turmas-adultos/export-bulk', [App\Http\Controllers\TurmasAdultosController::class, 'exportBulk'])->name('turmas-adultos.export-bulk');
    Route::get('turmas-adultos/{id}/export', [App\Http\Controllers\TurmasAdultosController::class, 'exportStudents'])->name('turmas-adultos.export');
    Route::post('turmas-adultos/transfer', [App\Http\Controllers\TurmasAdultosController::class, 'transferStudent'])->name('turmas-adultos.transfer');
    Route::get('turmas-adultos/{id}/attendance', [App\Http\Controllers\TurmasAdultosController::class, 'getAttendance'])->name('turmas-adultos.attendance');
    Route::post('turmas-adultos/attendance/save', [App\Http\Controllers\TurmasAdultosController::class, 'saveAttendance'])->name('turmas-adultos.attendance.save');
    Route::post('turmas-adultos/attendance/save-bulk', [App\Http\Controllers\TurmasAdultosController::class, 'saveBulkAttendance'])->name('turmas-adultos.attendance.save-bulk');
    Route::get('turmas-adultos/{id}/attendance-analysis', [App\Http\Controllers\TurmasAdultosController::class, 'attendanceAnalysis'])->name('turmas-adultos.attendance-analysis');
    Route::get('turmas-adultos/{id}/attendance-history/{student_id}', [App\Http\Controllers\TurmasAdultosController::class, 'attendanceHistory'])->name('turmas-adultos.attendance-history');
    Route::post('turmas-adultos/attendance/justify', [App\Http\Controllers\TurmasAdultosController::class, 'storeJustification'])->name('turmas-adultos.attendance.justify');
    Route::post('turmas-adultos/bulk-delete', [App\Http\Controllers\TurmasAdultosController::class, 'bulkDestroy'])->name('turmas-adultos.bulk-delete');
    Route::resource('turmas-adultos', App\Http\Controllers\TurmasAdultosController::class);

    // Reservas e Calendário (Page View)
    Route::get('reservas-calendar', [App\Http\Controllers\ReservaCalendarController::class, 'view'])->name('reservas-calendar.view');

    // Reservas e Calendário (API endpoints for React Component)
    // Using 'api' prefix to match frontend requests, but kept in web.php to share Auth session
    Route::prefix('api')->group(function () {
        Route::get('reservas-calendar/locais', [App\Http\Controllers\ReservaCalendarController::class, 'getLocais']);
        Route::resource('reservas-calendar', App\Http\Controllers\ReservaCalendarController::class)->except(['create', 'edit']);
    });

    // Calendário Matrimonial
    Route::get('calendario-matrimonio', [App\Http\Controllers\CalendarioMatrimonioController::class, 'index'])->name('calendario-matrimonio.index');
    
    Route::prefix('api/calendario-matrimonio')->group(function () {
        Route::get('reservas', [App\Http\Controllers\CalendarioMatrimonioController::class, 'events']);
        Route::post('reservas', [App\Http\Controllers\CalendarioMatrimonioController::class, 'store']);
        Route::put('reservas/{id}', [App\Http\Controllers\CalendarioMatrimonioController::class, 'update']);
        Route::delete('reservas/{id}', [App\Http\Controllers\CalendarioMatrimonioController::class, 'destroy']);
        
        Route::get('locais', [App\Http\Controllers\CalendarioMatrimonioController::class, 'getLocais']);
        Route::get('regras', [App\Http\Controllers\CalendarioMatrimonioController::class, 'getRules']);
        Route::post('regras', [App\Http\Controllers\CalendarioMatrimonioController::class, 'saveRules']);
    });

    // Documentação
    Route::resource('docs-crisma', App\Http\Controllers\DocsCrismaController::class);
    Route::get('inscricoes-crisma/search-users', [App\Http\Controllers\InscricoesCrismaController::class, 'searchUsers'])->name('inscricoes-crisma.search-users');
    Route::post('inscricoes-crisma/share', [App\Http\Controllers\InscricoesCrismaController::class, 'share'])->name('inscricoes-crisma.share');
    Route::get('inscricoes-crisma/export', [App\Http\Controllers\InscricoesCrismaController::class, 'export'])->name('inscricoes-crisma.export');
    Route::get('inscricoes-crisma/{id}/print', [App\Http\Controllers\InscricoesCrismaController::class, 'printSingle'])->name('inscricoes-crisma.print-single');
    Route::post('inscricoes-crisma/bulk-destroy', [App\Http\Controllers\InscricoesCrismaController::class, 'bulkDestroy'])->name('inscricoes-crisma.bulk-destroy');
    Route::post('inscricoes-crisma/bulk-print', [App\Http\Controllers\InscricoesCrismaController::class, 'bulkPrint'])->name('inscricoes-crisma.bulk-print');
    Route::put('inscricoes-crisma/{id}/status', [App\Http\Controllers\InscricoesCrismaController::class, 'updateStatus'])->name('inscricoes-crisma.update-status');
    Route::post('inscricoes-crisma/deadline', [App\Http\Controllers\InscricoesCrismaController::class, 'storeDeadline'])->name('inscricoes-crisma.store-deadline');
    Route::post('inscricoes-crisma/tax-config', [App\Http\Controllers\InscricoesCrismaController::class, 'storeTaxConfig'])->name('inscricoes-crisma.store-tax-config');
    Route::resource('inscricoes-crisma', App\Http\Controllers\InscricoesCrismaController::class);

    // Inscrições Catequese Adultos
    Route::get('inscricoes-catequese-adultos/search-users', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'searchUsers'])->name('inscricoes-catequese-adultos.search-users');
    Route::post('inscricoes-catequese-adultos/share', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'share'])->name('inscricoes-catequese-adultos.share');
    Route::get('inscricoes-catequese-adultos/export', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'export'])->name('inscricoes-catequese-adultos.export');
    Route::get('inscricoes-catequese-adultos/{id}/print', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'printSingle'])->name('inscricoes-catequese-adultos.print-single');
    Route::post('inscricoes-catequese-adultos/bulk-destroy', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'bulkDestroy'])->name('inscricoes-catequese-adultos.bulk-destroy');
    Route::post('inscricoes-catequese-adultos/bulk-print', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'bulkPrint'])->name('inscricoes-catequese-adultos.bulk-print');
    Route::put('inscricoes-catequese-adultos/{id}/status', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'updateStatus'])->name('inscricoes-catequese-adultos.update-status');
    Route::post('inscricoes-catequese-adultos/deadline', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'storeDeadline'])->name('inscricoes-catequese-adultos.store-deadline');
    Route::post('inscricoes-catequese-adultos/tax-config', [App\Http\Controllers\InscricoesCatequeseAdultosController::class, 'storeTaxConfig'])->name('inscricoes-catequese-adultos.store-tax-config');
    Route::resource('inscricoes-catequese-adultos', App\Http\Controllers\InscricoesCatequeseAdultosController::class);

    // Inscrições Eucaristia
    Route::get('inscricoes-eucaristia/search-users', [App\Http\Controllers\InscricoesEucaristiaController::class, 'searchUsers'])->name('inscricoes-eucaristia.search-users');
    Route::post('inscricoes-eucaristia/share', [App\Http\Controllers\InscricoesEucaristiaController::class, 'share'])->name('inscricoes-eucaristia.share');
    Route::get('inscricoes-eucaristia/export', [App\Http\Controllers\InscricoesEucaristiaController::class, 'export'])->name('inscricoes-eucaristia.export');
    Route::get('inscricoes-eucaristia/{id}/print', [App\Http\Controllers\InscricoesEucaristiaController::class, 'printSingle'])->name('inscricoes-eucaristia.print-single');
    Route::post('inscricoes-eucaristia/bulk-destroy', [App\Http\Controllers\InscricoesEucaristiaController::class, 'bulkDestroy'])->name('inscricoes-eucaristia.bulk-destroy');
    Route::post('inscricoes-eucaristia/bulk-print', [App\Http\Controllers\InscricoesEucaristiaController::class, 'bulkPrint'])->name('inscricoes-eucaristia.bulk-print');
    Route::put('inscricoes-eucaristia/{id}/status', [App\Http\Controllers\InscricoesEucaristiaController::class, 'updateStatus'])->name('inscricoes-eucaristia.update-status');
    Route::post('inscricoes-eucaristia/deadline', [App\Http\Controllers\InscricoesEucaristiaController::class, 'storeDeadline'])->name('inscricoes-eucaristia.store-deadline');
    Route::post('inscricoes-eucaristia/tax-config', [App\Http\Controllers\InscricoesEucaristiaController::class, 'storeTaxConfig'])->name('inscricoes-eucaristia.store-tax-config');
    Route::resource('inscricoes-eucaristia', App\Http\Controllers\InscricoesEucaristiaController::class);
    Route::resource('docs-eucaristia', App\Http\Controllers\DocsEucaristiaController::class);

    // Acólitos e Coroinhas
    Route::get('acolitos/search-registers', [App\Http\Controllers\AcolitoController::class, 'searchRegisters'])->name('acolitos.search-registers');
    Route::post('acolitos/check-user', [App\Http\Controllers\AcolitoController::class, 'checkUser'])->name('acolitos.check-user');
    Route::post('acolitos/{id}/link-user', [App\Http\Controllers\AcolitoController::class, 'linkUser'])->name('acolitos.link-user');
    Route::post('acolitos/check-bulk-matches', [App\Http\Controllers\AcolitoController::class, 'checkBulkUserMatches'])->name('acolitos.check-bulk-matches');
    Route::post('acolitos/bulk-link-users', [App\Http\Controllers\AcolitoController::class, 'bulkLinkUsers'])->name('acolitos.bulk-link-users');
    Route::post('acolitos/bulk-delete', [App\Http\Controllers\AcolitoController::class, 'bulkDestroy'])->name('acolitos.bulk-delete');
    Route::post('acolitos/funcoes/bulk-delete', [App\Http\Controllers\AcolitoFuncaoController::class, 'bulkDestroy'])->name('acolitos.funcoes.bulk-delete');
    Route::resource('acolitos/funcoes', App\Http\Controllers\AcolitoFuncaoController::class, ['as' => 'acolitos']);
    Route::post('acolitos/notes/bulk-delete', [App\Http\Controllers\AcolitoNoteController::class, 'bulkDestroy'])->name('acolitos.notes.bulk-delete');
    Route::resource('acolitos/notes', App\Http\Controllers\AcolitoNoteController::class, ['as' => 'acolitos']);
    
    // Escalas Management Routes
    Route::get('acolitos/escalas/{id}/manage', [App\Http\Controllers\AcolitoEscalaController::class, 'manage'])->name('acolitos.escalas.manage');
    Route::post('acolitos/escalas/{id}/celebrations', [App\Http\Controllers\AcolitoEscalaController::class, 'storeCelebration'])->name('acolitos.escalas.celebrations.store');
    Route::put('acolitos/escalas/{id}/celebrations/{celebrationId}', [App\Http\Controllers\AcolitoEscalaController::class, 'updateCelebration'])->name('acolitos.escalas.celebrations.update');
    Route::delete('acolitos/escalas/{id}/celebrations/{celebrationId}', [App\Http\Controllers\AcolitoEscalaController::class, 'destroyCelebration'])->name('acolitos.escalas.celebrations.destroy');
    Route::resource('acolitos/escalas', App\Http\Controllers\AcolitoEscalaController::class, ['as' => 'acolitos']);
    
    Route::resource('acolitos', App\Http\Controllers\AcolitoController::class);

    // Vicentinos
    Route::get('vicentinos/search-registers', [App\Http\Controllers\VicentinoController::class, 'searchRegisters'])->name('vicentinos.search-registers');
    Route::post('vicentinos/bulk-delete', [App\Http\Controllers\VicentinoController::class, 'bulkDestroy'])->name('vicentinos.bulk-delete');
    Route::resource('vicentinos', App\Http\Controllers\VicentinoController::class);

    // Pascom
    Route::resource('solicitacoes-pascom', App\Http\Controllers\SolicitacaoPascomController::class);

    // Categorias Doacao (Estoque)
    Route::post('categorias_doacao/bulk-delete', [App\Http\Controllers\CategoriaDoacaoController::class, 'bulkDestroy'])->name('categorias_doacao.bulk-delete');
    Route::resource('categorias_doacao', App\Http\Controllers\CategoriaDoacaoController::class);

    // Estoque (Social Assistant)
    Route::post('estoque/bulk-delete', [App\Http\Controllers\EstoqueController::class, 'bulkDestroy'])->name('estoque.bulk-delete');
    Route::any('estoque/pdf', [App\Http\Controllers\EstoqueController::class, 'generatePdf'])->name('estoque.pdf');
    Route::delete('estoque/image/{id}', [App\Http\Controllers\EstoqueController::class, 'deleteImage'])->name('estoque.image.delete');
    Route::resource('estoque', App\Http\Controllers\EstoqueController::class);

    // Saída de Estoque
    Route::resource('estoque-saida', App\Http\Controllers\EstoqueSaidaController::class);

    // Inventário
    Route::post('inventory/bulk-delete', [App\Http\Controllers\InventoryController::class, 'bulkDestroy'])->name('inventory.bulk-delete');
    Route::get('inventory/photo/{id}/delete', [App\Http\Controllers\InventoryController::class, 'destroyPhoto'])->name('inventory.photo.destroy');
    Route::resource('inventory', App\Http\Controllers\InventoryController::class);

    // Celebrações e Horários
    Route::post('celebration-schedules/bulk-delete', [App\Http\Controllers\CelebrationScheduleController::class, 'bulkDestroy'])->name('celebration-schedules.bulk-delete');
    Route::resource('celebration-schedules', App\Http\Controllers\CelebrationScheduleController::class);

    // Protocols
    Route::get('protocols/notification/{id}/read', [App\Http\Controllers\ProtocolController::class, 'markNotificationAsRead'])->name('protocols.notification.read');
    Route::resource('protocols', App\Http\Controllers\ProtocolController::class);
    
    // Admin Protocols
    Route::get('admin/protocols', [App\Http\Controllers\AdminProtocolController::class, 'index'])->name('admin.protocols.index');
    Route::get('admin/protocols/{id}', [App\Http\Controllers\AdminProtocolController::class, 'show'])->name('admin.protocols.show');
    Route::post('admin/protocols/{id}/status', [App\Http\Controllers\AdminProtocolController::class, 'updateStatus'])->name('admin.protocols.update-status');
    Route::put('admin/protocols/{id}', [App\Http\Controllers\AdminProtocolController::class, 'update'])->name('admin.protocols.update');

    // Comunidades
    Route::resource('comunidades', App\Http\Controllers\ComunidadeController::class);

    // Salas e Espaços
    Route::resource('reservas-locais', App\Http\Controllers\ReservasLocaisController::class);

    // Comunicação em Massa
    Route::get('mass-communication', [App\Http\Controllers\MassCommunicationController::class, 'index'])->name('mass-communication.index');
    Route::post('mass-communication/send', [App\Http\Controllers\MassCommunicationController::class, 'send'])->name('mass-communication.send');

    // Calendário Matrimonial
    Route::get('/calendario-matrimonio', [App\Http\Controllers\CalendarioMatrimonioController::class, 'index'])->name('calendario-matrimonio.index');
    
    // API interna para o React Calendar
    Route::prefix('api/matrimonio-calendar')->group(function () {
        Route::get('/', [App\Http\Controllers\CalendarioMatrimonioController::class, 'events']);
        Route::post('/', [App\Http\Controllers\CalendarioMatrimonioController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\CalendarioMatrimonioController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\CalendarioMatrimonioController::class, 'destroy']);
        Route::get('/rules', [App\Http\Controllers\CalendarioMatrimonioController::class, 'getRules']);
        Route::post('/rules', [App\Http\Controllers\CalendarioMatrimonioController::class, 'saveRules']);
        Route::get('/locais', [App\Http\Controllers\CalendarioMatrimonioController::class, 'getLocais']);
    });
});
