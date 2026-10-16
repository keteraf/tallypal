<?php

declare(strict_types=1);

use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Models\Tally;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\TallyObserver;
use App\Observers\TallyUserObserver;
use App\Observers\TransactionObserver;
use App\Providers\AppServiceProvider;
use App\Repositories\TallyRepository;
use App\Repositories\TransactionStatsRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;

covers(AppServiceProvider::class);

/**
 * The limits a named rate limiter returns for a request, as `[maxAttempts, decaySeconds, key]` triples.
 *
 * @return array<int, array{0: int, 1: int, 2: mixed}>
 */
function rateLimits(string $name, Request $request): array
{
    $limits = RateLimiter::limiter($name)($request);

    return array_map(
        fn (Limit $limit): array => [$limit->maxAttempts, $limit->decaySeconds, $limit->key],
        is_array($limits) ? $limits : [$limits],
    );
}

function rateLimitedRequest(?User $user = null): Request
{
    $request = Request::create('/', 'POST', ['email' => 'Zoë@Example.com'], server: ['REMOTE_ADDR' => '10.0.0.7']);
    $request->setUserResolver(fn (): ?User => $user);

    return $request;
}

it('binds the repository contracts to their implementations', function (): void {
    expect(app(TallyRepositoryInterface::class))->toBeInstanceOf(TallyRepository::class)
        ->and(app(TransactionStatsRepositoryInterface::class))->toBeInstanceOf(TransactionStatsRepository::class);
});

it('lets a Super Admin pass every gate and leaves other users to the policies', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    expect(Gate::forUser($admin)->allows('an-ability-nobody-defines'))->toBeTrue()
        ->and(Gate::forUser(User::factory()->create())->allows('an-ability-nobody-defines'))->toBeFalse();
});

it('observes created, updated and deleted on transactions, tallies and tally members', function (string $model, string $observer): void {
    $listeners = Event::getRawListeners();

    foreach (['created', 'updated', 'deleted'] as $event) {
        expect($listeners["eloquent.{$event}: {$model}"] ?? [])->toContain("{$observer}@{$event}");
    }
})->with([
    'transactions' => [Transaction::class, TransactionObserver::class],
    'tallies' => [Tally::class, TallyObserver::class],
    'tally members' => [TallyUser::class, TallyUserObserver::class],
]);

it('limits login per IP and per transliterated, lowercased email and IP', function (): void {
    expect(rateLimits('login', rateLimitedRequest()))->toBe([
        [10, 60, '10.0.0.7'],
        [5, 60, 'zoe@example.com|10.0.0.7'],
    ]);
});

it('limits registration per IP', function (): void {
    expect(rateLimits('register', rateLimitedRequest()))->toBe([[5, 60, '10.0.0.7']]);
});

it('limits password requests per IP and per transliterated, lowercased email and IP', function (): void {
    expect(rateLimits('password', rateLimitedRequest()))->toBe([
        [5, 60, '10.0.0.7'],
        [3, 60, 'zoe@example.com|10.0.0.7'],
    ]);
});

it('limits the API per user when signed in and per IP otherwise', function (): void {
    $user = User::factory()->create();

    expect(rateLimits('api', rateLimitedRequest($user)))->toBe([[60, 60, $user->id]])
        ->and(rateLimits('api', rateLimitedRequest()))->toBe([[60, 60, '10.0.0.7']]);
});

it('limits invitations per IP', function (): void {
    expect(rateLimits('invitations', rateLimitedRequest()))->toBe([[10, 60, '10.0.0.7']]);
});
