<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TallyController;
use App\Http\Controllers\Api\TallyShareController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::apiResource('tallies', TallyController::class);
    Route::apiResource('tallies.transactions', TransactionController::class);
    Route::put('tallies/{tally}/shares', [TallyShareController::class, 'update']);
    Route::apiResource('tallies.shares', TallyShareController::class)->only(['index', 'store', 'destroy']);
});
