<?php

declare(strict_types=1);

use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\TelescopeServiceProvider as TelescopePackageServiceProvider;

covers(TelescopeServiceProvider::class, AppServiceProvider::class);

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

it('registers neither Telescope provider outside local', function (string $environment): void {
    $app = $this->app;
    $app['env'] = $environment;

    (new AppServiceProvider($app))->register();

    expect($app->getProviders(TelescopePackageServiceProvider::class))->toBeEmpty()
        ->and($app->getProviders(TelescopeServiceProvider::class))->toBeEmpty();
})->with(['production', 'staging', 'testing']);

it('registers both Telescope providers in the local environment when the package is installed', function (): void {
    $app = $this->app;
    $app['env'] = 'local';

    (new AppServiceProvider($app))->register();

    expect($app->getProviders(TelescopePackageServiceProvider::class))->toHaveCount(1)
        ->and($app->getProviders(TelescopeServiceProvider::class))->toHaveCount(1);
});

it('does not list the application Telescope provider among the always-loaded providers', function (): void {
    expect(require base_path('bootstrap/providers.php'))->not->toContain(TelescopeServiceProvider::class);
});

it('references Telescope nowhere in routes/console.php', function (): void {
    expect(mb_strtolower((string) file_get_contents(base_path('routes/console.php'))))->not->toContain('telescope');
});
