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

    // Onboarding / Setup Routes
    Route::get('/setup/password', [OnboardingController::class, 'showPasswordForm'])->name('setup.password');
    Route::post('/setup/password', [OnboardingController::class, 'updatePassword'])->name('setup.password.update');
    
    Route::get('/setup/welcome', [OnboardingController::class, 'showWelcome'])->name('setup.welcome');
    Route::post('/setup/welcome', [OnboardingController::class, 'updateWelcome'])->name('setup.welcome.update');
    Route::post('/setup/welcome/skip', [OnboardingController::class, 'skipWelcome'])->name('setup.welcome.skip');
});
