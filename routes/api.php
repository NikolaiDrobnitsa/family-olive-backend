<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\EventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Публичные API маршруты
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify', [AuthController::class, 'verifyCode']);
Route::post('/auth/resend-code', [AuthController::class, 'resendCode']);

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
});
