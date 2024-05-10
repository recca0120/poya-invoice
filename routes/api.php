<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(EventController::class)
        ->prefix('/event')
        ->group(function () {
            Route::get('/', 'index');
        });

    Route::controller(EventUserController::class)
        ->prefix('/event/{event_id}')
        ->group(function () {
            Route::post('/', 'create');
        });
});
