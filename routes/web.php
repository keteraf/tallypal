<?php

declare(strict_types=1);

use App\Http\Controllers\TallyController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', []);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (! auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('tallies.index');
        }

        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('tallies', TallyController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('tallies.transactions', TransactionController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
