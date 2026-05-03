<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UtilisateurController;
use App\Http\Controllers\API\LineController;
use App\Http\Controllers\API\RouteController;
use App\Http\Controllers\API\StopController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\AuthController;

Route::middleware('redirectifauthenticatedapi')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('utilisateurs', UtilisateurController::class);
        Route::post('/lines', [LineController::class, 'store']);
        Route::put('/lines/{id}', [LineController::class, 'update']);
        Route::delete('/lines/{id}', [LineController::class, 'destroy']);
        Route::apiResource('stops', StopController::class);
        Route::apiResource('schedules', ScheduleController::class);
        Route::apiResource('notifications', NotificationController::class);

        Route::patch('/utilisateurs/{id}/toggle-block', [UtilisateurController::class,'toggleBlock']);
    });

    Route::get('/lines', [LineController::class,'index']);
    Route::get('/lines/{id}', [LineController::class,'show']);
    Route::get('/search', [RouteController::class,'search']);
    Route::get('/notifications', [NotificationController::class,'index']);
    Route::get('/next-bus/{line_id}', [RouteController::class, 'nextBus']);
    Route::post('/route-path', [RouteController::class,'routePath']);
    Route::post('/route-user-stop', [RouteController::class, 'userToStopRoute']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});