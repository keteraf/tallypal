<?php

declare(strict_types=1);

use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;

covers(TelescopeServiceProvider::class);

it('switches Telescope off outside local whatever TELESCOPE_ENABLED says', function (string $environment): void {
    config(['telescope.enabled' => true]);
    $this->app['env'] = $environment;

    (new TelescopeServiceProvider($this->app))->register();

    expect(config('telescope.enabled'))->toBeFalse();
})->with(['production', 'staging', 'testing']);

it('leaves the Telescope switch alone in the local environment', function (): void {
    config(['telescope.enabled' => true]);
    $this->app['env'] = 'local';

    (new TelescopeServiceProvider($this->app))->register();

    expect(config('telescope.enabled'))->toBeTrue();
});

it('denies the Telescope dashboard gate to everyone', function (): void {
    (new TelescopeServiceProvider($this->app))->boot();

    expect(Gate::has('viewTelescope'))->toBeTrue()
        ->and(Gate::forUser(User::factory()->make())->allows('viewTelescope'))->toBeFalse();
});

it('prunes Telescope entries older than 48 hours every day', function (): void {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event): bool => str_contains((string) $event->command, 'telescope:prune --hours=48'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 0 * * *');
});
