<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TallyController;
use App\Http\Controllers\Api\TallyInvitationController;
use App\Http\Controllers\Api\TallyShareController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::apiResource('tallies', TallyController::class)->names('api.tallies');
    Route::apiResource('tallies.transactions', TransactionController::class)->names('api.tallies.transactions');
    Route::put('tallies/{tally}/shares', [TallyShareController::class, 'update'])->name('api.tallies.shares.update');
    Route::apiResource('tallies.shares', TallyShareController::class)->only(['index', 'store', 'destroy'])->names('api.tallies.shares');
    Route::apiResource('tallies.invitations', TallyInvitationController::class)->only(['index', 'destroy'])->names('api.tallies.invitations');
});
