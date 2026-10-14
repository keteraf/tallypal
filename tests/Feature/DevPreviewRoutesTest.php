<?php

declare(strict_types=1);

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
