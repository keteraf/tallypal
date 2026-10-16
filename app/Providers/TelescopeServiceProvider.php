<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Telescope records only in the local environment, whatever TELESCOPE_ENABLED says.
     *
     * Application providers register before any provider boots, so the switch is off by the time the
     * package provider reads `telescope.enabled` to start recording and to register the dashboard routes.
     */
    public function register(): void
    {
        if (! $this->app->environment('local')) {
            config(['telescope.enabled' => false]);
        }
    }

    /**
     * Register the Telescope gate.
     *
     * Nobody is allowed through it: the dashboard is reachable in the local environment only.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', fn (User $user): bool => false);
    }
}
