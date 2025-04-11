<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\SurveyQuestionController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Маршруты авторизации
//Route::get('/auth/login', [CustomAuthController::class, 'showLoginForm'])->name('auth.login');
//Route::post('/auth/login', [CustomAuthController::class, 'login']);
//Route::post('/auth/verify', [CustomAuthController::class, 'verifyCode']);
//Route::post('/auth/resend-code', [CustomAuthController::class, 'resendCode']);
//Route::post('/auth/logout', [CustomAuthController::class, 'logout'])->name('auth.logout');
//Route::get('/auth/check', [CustomAuthController::class, 'checkAuth'])->name('auth.check');

// Маршруты административной панели
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Дашборд
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Управление пользователями
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('/users/{id}/survey-responses', [UserController::class, 'getSurveyResponses'])->name('users.survey-responses');

    // Управление событиями
    Route::resource('events', EventController::class);
    Route::post('/events/order', [EventController::class, 'updateOrder'])->name('events.update-order');

    // Управление опросником
    Route::resource('survey', SurveyQuestionController::class);
    Route::post('/survey/order', [SurveyQuestionController::class, 'updateOrder'])->name('survey.update-order');

    // Настройки
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/password', [SettingController::class, 'changePassword'])->name('settings.change-password');
    Route::post('/settings/admin', [SettingController::class, 'createAdmin'])->name('settings.create-admin');
    Route::delete('/settings/admin/{id}', [SettingController::class, 'deleteAdmin'])->name('settings.delete-admin');
});
