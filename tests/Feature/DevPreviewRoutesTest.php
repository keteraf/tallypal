<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the error page preview with a 404 status outside production', function (): void {
    $this->get(route('dev.error-page'))
        ->assertNotFound()
        ->assertInertia(fn ($page) => $page
            ->component('ErrorPage')
            ->where('status', 404)
        );
});

it('renders the error page preview for other supported statuses', function (int $status): void {
    $this->get(route('dev.error-page', ['status' => $status]))
        ->assertStatus($status)
        ->assertInertia(fn ($page) => $page
            ->component('ErrorPage')
            ->where('status', $status)
        );
})->with([403, 500, 503]);

it('rejects unsupported statuses on the error page preview', function (): void {
    $this->get('/dev/error-page/418')->assertNotFound();
});

it('ships the status-specific error titles in every locale', function (string $locale, string $key, string $title): void {
    expect(file_get_contents(resource_path("js/i18n/locales/{$locale}.ts")))
        ->toContain("title: {$title}");
})->with([
    'en 404' => ['en', 'not_found', "'Page not found'"],
    'en 403' => ['en', 'forbidden', '"You can\'t open this"'],
    'en 500' => ['en', 'generic', "'Oops! Something went wrong'"],
    'en 503' => ['en', 'unavailable', "'Back in a few minutes'"],
    'it 404' => ['it', 'not_found', "'Pagina non trovata'"],
    'it 403' => ['it', 'forbidden', "'Non puoi aprire questa pagina'"],
    'it 500' => ['it', 'generic', "'Ops! Qualcosa è andato storto'"],
    'it 503' => ['it', 'unavailable', "'Torniamo tra pochi minuti'"],
]);

it('renders every email preview from the seeded data', function (string $template, string $expected): void {
    $owner = User::factory()->create(['name' => 'Preview Owner']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'Preview Tally']);
    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'token-preview',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->get(route('dev.mail', ['template' => $template]))
        ->assertOk()
        ->assertSee($expected, false);
})->with([
    'existing user invitation' => ['invitation-existing', 'Accept invitation'],
    'new user invitation' => ['invitation-new', 'Create account and join'],
    'verify email' => ['verify-email', 'Verify Email Address'],
    'reset password' => ['reset-password', 'Reset Password'],
]);

it('rejects unknown email previews', function (): void {
    $this->get('/dev/mail/unknown')->assertNotFound();
});
