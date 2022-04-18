<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Line\LineConfigController;
use App\Http\Controllers\Settings\QueueCalendarSettingController;
use App\Http\Controllers\Settings\QueueSettingController;
use App\Http\Controllers\Settings\TicketGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public Route
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::get('logout', 'logout');
});

Route::apiResource('line_config', LineConfigController::class);
Route::prefix('line_config')->controller(LineConfigController::class)->group(function () {
});


// Private Route
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class, 'profile']);

    Route::prefix('setting')->group(function () {
        Route::controller(QueueSettingController::class)->group(function () {
            Route::get('queue', 'show');
            Route::post('queue', 'store');
            Route::put('queue', 'update');
        });

        Route::controller(QueueCalendarSettingController::class)->group(function () {
            Route::get('calendar', 'index');
            Route::get('calendar/{id}', 'show');
            Route::post('calendar', 'store');
            Route::put('calendar', 'update');
            Route::put('calendarActivate', 'calendarActivate');
        });

        Route::controller(TicketGroupController::class)->group(function () {
            Route::get('ticket', 'index');
            Route::get('ticket/{id}', 'show');
            Route::post('ticket', 'store');
            Route::put('ticket', 'update');
            Route::put('delete', 'destroy');
        });
    });
});
