<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\RegisterController;
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/privacy', [App\Http\Controllers\SettingsController::class, 'updatePrivacy'])->name('settings.update.privacy');
    Route::put('/settings/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('settings.update.password');

    // Módulos Internos
    // Registros Gerais
    Route::get('registers/search', [RegisterController::class, 'searchPeople'])->name('registers.search');
    Route::post('registers/pdf', [RegisterController::class, 'generatePdf'])->name('registers.pdf');
    Route::get('registers/check-phone', [RegisterController::class, 'checkPhone'])->name('registers.check-phone');
    Route::delete('registers/attachments/{id}', [RegisterController::class, 'destroyAttachment'])->name('registers.attachments.destroy');
    Route::resource('registers', RegisterController::class);

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
    Route::get('turmas-eucaristia/{id}/attendance', [App\Http\Controllers\TurmasEucaristiaController::class, 'getAttendance'])->name('turmas-eucaristia.attendance.get');
    Route::post('turmas-eucaristia/attendance', [App\Http\Controllers\TurmasEucaristiaController::class, 'saveAttendance'])->name('turmas-eucaristia.attendance.save');
    Route::resource('turmas-eucaristia', App\Http\Controllers\TurmasEucaristiaController::class);
    
    // Turmas Crisma
    Route::get('turmas-crisma/{id}/students', [App\Http\Controllers\TurmasCrismaController::class, 'getStudents'])->name('turmas-crisma.students');
    Route::get('turmas-crisma/export-bulk', [App\Http\Controllers\TurmasCrismaController::class, 'exportBulk'])->name('turmas-crisma.export-bulk');
    Route::get('turmas-crisma/{id}/export', [App\Http\Controllers\TurmasCrismaController::class, 'exportStudents'])->name('turmas-crisma.export');
    Route::post('turmas-crisma/transfer', [App\Http\Controllers\TurmasCrismaController::class, 'transferStudent'])->name('turmas-crisma.transfer');
    Route::get('turmas-crisma/{id}/attendance', [App\Http\Controllers\TurmasCrismaController::class, 'getAttendance'])->name('turmas-crisma.attendance.get');
    Route::post('turmas-crisma/attendance', [App\Http\Controllers\TurmasCrismaController::class, 'saveAttendance'])->name('turmas-crisma.attendance.save');
    Route::resource('turmas-crisma', App\Http\Controllers\TurmasCrismaController::class);

    // Chat
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/users', [App\Http\Controllers\ChatController::class, 'getUsers'])->name('chat.users');
    Route::get('/chat/messages/{userId}', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/unread', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread');
    Route::post('/chat/toggle-pin', [App\Http\Controllers\ChatController::class, 'toggleUserPin'])->name('chat.toggle-pin');
    Route::post('/chat/block', [App\Http\Controllers\ChatController::class, 'blockUser'])->name('chat.block');
    Route::post('/chat/unblock', [App\Http\Controllers\ChatController::class, 'unblockUser'])->name('chat.unblock');
    Route::post('/chat/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');


    // Onboarding / Setup Routes
    Route::get('/setup/password', [OnboardingController::class, 'showPasswordForm'])->name('setup.password');
    Route::post('/setup/password', [OnboardingController::class, 'updatePassword'])->name('setup.password.update');
    
    Route::get('/setup/welcome', [OnboardingController::class, 'showWelcome'])->name('setup.welcome');
    Route::post('/setup/welcome', [OnboardingController::class, 'updateWelcome'])->name('setup.welcome.update');
    Route::post('/setup/welcome/skip', [OnboardingController::class, 'skipWelcome'])->name('setup.welcome.skip');
});
