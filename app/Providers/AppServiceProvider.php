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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TallyRepositoryInterface::class, TallyRepository::class);
        $this->app->bind(TransactionStatsRepositoryInterface::class, TransactionStatsRepository::class);
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
    }
}
