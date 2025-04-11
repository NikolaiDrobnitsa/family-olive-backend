<?php
// routes/api.php - add these routes to your existing API routes file

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\EventController as AdminEventController;
use App\Http\Controllers\Api\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Api\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Существующие публичные API маршруты
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify', [AuthController::class, 'verifyCode']);
Route::post('/auth/resend-code', [AuthController::class, 'resendCode']);

// Admin login
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// Защищенные API маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Аутентификация
    Route::get('/auth/check', [AuthController::class, 'check']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Опросы
    Route::get('/survey/questions', [SurveyController::class, 'getQuestions']);
    Route::post('/survey/responses', [SurveyController::class, 'saveResponses']);
    Route::get('/survey/user-responses', [SurveyController::class, 'getUserResponses']);
    Route::get('/survey/check', [SurveyController::class, 'checkSurveyStatus']);

    // События
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{category}', [EventController::class, 'getByCategory']);

    // Админ маршруты (с проверкой роли админа)
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Дашборд
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Пользователи
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/export', [UserController::class, 'export']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::get('/users/{id}/survey-responses', [UserController::class, 'getSurveyResponses']);
        Route::get('/users/{id}/visits', [UserController::class, 'getVisits']);

        // События
        Route::get('/events', [AdminEventController::class, 'index']);
        Route::post('/events', [AdminEventController::class, 'store']);
        Route::get('/events/{id}', [AdminEventController::class, 'show']);
        Route::post('/events/{id}', [AdminEventController::class, 'update']); // Using POST with _method=PUT for file uploads
        Route::delete('/events/{id}', [AdminEventController::class, 'destroy']);
        Route::post('/events/order', [AdminEventController::class, 'updateOrder']);

        // Опросник
        Route::get('/survey', [AdminSurveyController::class, 'index']);
        Route::post('/survey', [AdminSurveyController::class, 'store']);
        Route::get('/survey/{id}', [AdminSurveyController::class, 'show']);
        Route::put('/survey/{id}', [AdminSurveyController::class, 'update']);
        Route::delete('/survey/{id}', [AdminSurveyController::class, 'destroy']);
        Route::post('/survey/order', [AdminSurveyController::class, 'updateOrder']);

        // Настройки
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'update']);
        Route::post('/settings/password', [SettingController::class, 'changePassword']);

        // Super Admin routes
        Route::post('/settings/admin', [SettingController::class, 'createAdmin']);
        Route::delete('/settings/admin/{id}', [SettingController::class, 'deleteAdmin']);
    });
});
