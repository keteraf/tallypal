<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TallyController;
use App\Http\Controllers\TallyInvitationController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', []);
})->name('home');

Route::get('invitations/{uuid}/accept', [TallyInvitationController::class, 'accept'])->name('invitations.accept');
Route::get('invitations/{uuid}/decline', [TallyInvitationController::class, 'decline'])->name('invitations.decline');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('home', HomeController::class)->name('home.dashboard');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('tallies', TallyController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('tallies.transactions', TransactionController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
