<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Models\Tally;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\TallyObserver;
use App\Observers\TallyUserObserver;
use App\Observers\TransactionObserver;
use App\Repositories\TallyRepository;
use App\Repositories\TransactionStatsRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Telescope\TelescopeServiceProvider as TelescopePackageServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TallyRepositoryInterface::class, TallyRepository::class);
        $this->app->bind(TransactionStatsRepositoryInterface::class, TransactionStatsRepository::class);

        $this->registerTelescope();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user, $ability): ?bool => $user->hasRole('Super Admin') ? true : null);

        Transaction::observe(TransactionObserver::class);
        Tally::observe(TallyObserver::class);
        TallyUser::observe(TallyUserObserver::class);

        $this->configureRateLimiters();
    }

    /**
     * Telescope is a dev dependency: register it in the local environment only, and only when installed.
     */
    private function registerTelescope(): void
    {
        if (! $this->app->environment('local') || ! class_exists(TelescopePackageServiceProvider::class)) {
            return;
        }

        $this->app->register(TelescopePackageServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', fn (Request $request): array => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perMinute(5)->by(Str::transliterate(Str::lower($request->string('email')->toString()) . '|' . $request->ip())),
        ]);

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('password', fn (Request $request): array => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perMinute(3)->by(Str::transliterate(Str::lower($request->string('email')->toString()) . '|' . $request->ip())),
        ]);

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('invitations', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }
}
