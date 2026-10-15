<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TallyController;
use App\Http\Controllers\TallyInvitationController;
use App\Http\Controllers\TransactionController;
use App\Mail\TallyInvitationExistingUser;
use App\Mail\TallyInvitationNewUser;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', []);
})->name('home');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');

// Design-system showcase — dev only, never registered in production.
if (! app()->isProduction()) {
    Route::get('dev/design-system', fn () => Inertia::render('dev/DesignSystem', []))
        ->name('dev.design-system');

    // Error-page preview — `bootstrap/app.php` only renders ErrorPage outside local/testing,
    // so this is the only way to see the error pages in development. Defaults to 404.
    Route::get('dev/error-page/{status?}', fn (Request $request, int $status = 404) => Inertia::render('ErrorPage', ['status' => $status])
        ->toResponse($request)
        ->setStatusCode($status))
        ->whereIn('status', ['403', '404', '500', '503'])
        ->name('dev.error-page');

    // Email preview — renders each transactional email in the browser from the seeded demo data.
    Route::get('dev/mail/{template}', function (string $template) {
        $invitation = fn (): TallyInvitation => TallyInvitation::query()->latest()->firstOrFail();
        $user = fn (): User => User::query()->firstOrFail();

        return match ($template) {
            'invitation-existing' => new TallyInvitationExistingUser($invitation())->render(),
            'invitation-new' => new TallyInvitationNewUser($invitation())->render(),
            'verify-email' => (new VerifyEmail)->toMail($user())->render(),
            default => new ResetPassword('preview-token')->toMail($user())->render(),
        };
    })
        ->whereIn('template', ['invitation-existing', 'invitation-new', 'verify-email', 'reset-password'])
        ->name('dev.mail');
}

Route::get('invitations/{uuid}/accept', [TallyInvitationController::class, 'accept'])
    ->middleware(['signed', 'throttle:invitations'])
    ->name('invitations.accept');
Route::get('invitations/{uuid}/decline', [TallyInvitationController::class, 'decline'])
    ->middleware(['signed', 'throttle:invitations'])
    ->name('invitations.decline');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('home', HomeController::class)->name('home.dashboard');

    Route::get('stats', StatsController::class)->name('stats');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('tallies/picker', [TallyController::class, 'picker'])->name('tallies.picker');

    Route::resource('tallies', TallyController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('tallies/{tally}/transactions/form-data', [TransactionController::class, 'formData'])->name('tallies.transactions.form-data');

    Route::resource('tallies.transactions', TransactionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
