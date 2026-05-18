<?php

declare(strict_types=1);

use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Models\User;
use App\Services\HomeCacheService;
use App\Services\HomeService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

covers(HomeService::class);

beforeEach(function (): void {
    Cache::flush();
});

function makeService(
    TallyRepositoryInterface $tallyRepo,
    TransactionStatsRepositoryInterface $statsRepo,
): HomeService {
    return new HomeService(new HomeCacheService($tallyRepo, $statsRepo));
}

function mockTallyRepo(): TallyRepositoryInterface&MockInterface
{
    return Mockery::mock(TallyRepositoryInterface::class);
}

function mockStatsRepo(): TransactionStatsRepositoryInterface&MockInterface
{
    return Mockery::mock(TransactionStatsRepositoryInterface::class);
}

test('returns empty payload when user has no tallies', function (): void {
    $user = User::factory()->make(['name' => 'Alice', 'id' => 1]);
    $now = CarbonImmutable::parse('2025-05-01 12:00:00');

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')
        ->once()
        ->with(1)
        ->andReturn(collect());

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload['user_name'])->toBe('Alice')
        ->and($payload['tallies'])->toBeEmpty()
        ->and($payload['primary_tally'])->toBeNull()
        ->and($payload['recent_transactions'])->toBeEmpty()
        ->and($payload['weekly_history'])->toBeEmpty()
        ->and($payload['categories_breakdown'])->toBeEmpty()
        ->and($payload['shared_tally'])->toBeNull()
        ->and($payload['balance']['net'])->toBe(0.0)
        ->and($payload['balance']['income'])->toBe(0.0)
        ->and($payload['balance']['expense'])->toBe(0.0)
        ->and($payload['balance']['currency_symbol'])->toBe('€')
        ->and($payload['balance']['currency_code'])->toBe('EUR')
        ->and($payload['balance']['decimals'])->toBe(2);
});

test('builds full payload when user has tallies', function (): void {
    $user = User::factory()->make(['name' => 'Bob', 'id' => 2]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $tallyIds = collect([1, 2]);
    $monthStart = $now->startOfMonth();
    $weekStart = $now->startOfWeek()->subWeeks(11);

    $tallySummaries = collect([
        ['uuid' => 'abc', 'name' => 'Personal', 'member_count' => 1, 'monthly_total' => 50.0],
    ]);

    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];

    $balance = [
        'net' => 100.0,
        'income' => 200.0,
        'expense' => 100.0,
        'currency_symbol' => '€',
        'currency_code' => 'EUR',
        'decimals' => 2,
    ];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn($tallySummaries);
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);

    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload['user_name'])->toBe('Bob')
        ->and($payload['current_month_label'])->toBe('May 2025')
        ->and($payload['primary_tally'])->toMatchArray(['uuid' => 'abc', 'name' => 'Personal'])
        ->and($payload['tallies'])->toHaveCount(1)
        ->and($payload['balance']['income'])->toBe(200.0)
        ->and($payload['shared_tally'])->toBeNull();
});

test('primary_tally is null when tallies collection is empty after repository returns some ids', function (): void {
    $user = User::factory()->make(['name' => 'Carol', 'id' => 3]);
    $now = CarbonImmutable::parse('2025-06-01 08:00:00');
    $tallyIds = collect([5]);

    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 2];
    $balance = ['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect());
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);

    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload['primary_tally'])->toBeNull();
});

test('forgetCacheForUser removes all cache keys for the user', function (): void {
    $userId = 42;
    $now = CarbonImmutable::now();
    $monthKey = $now->format('Y-m');
    $dayKey = $now->format('Y-m-d');

    $keys = [
        "home:user:{$userId}:tally_ids",
        "home:user:{$userId}:primary_currency",
        "home:user:{$userId}:tallies_summary:{$monthKey}",
        "home:user:{$userId}:balance:{$dayKey}",
        "home:user:{$userId}:recent_tx",
        "home:user:{$userId}:weekly_history:{$dayKey}",
        "home:user:{$userId}:categories:{$monthKey}",
        "home:user:{$userId}:shared_tally:{$monthKey}",
    ];

    foreach ($keys as $key) {
        Cache::put($key, 'some-value', 60);
    }

    $service = makeService(mockTallyRepo(), mockStatsRepo());
    $service->forgetCacheForUser($userId);

    foreach ($keys as $key) {
        expect(Cache::has($key))->toBeFalse("Expected key [{$key}] to be forgotten");
    }
});

test('empty payload has correct month label', function (): void {
    $user = User::factory()->make(['name' => 'Eve', 'id' => 55]);
    $now = CarbonImmutable::parse('2025-03-15 10:00:00');

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect());

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload['current_month_label'])->toBe('March 2025');
});

test('payload contains all required top-level keys', function (): void {
    $user = User::factory()->make(['name' => 'Frank', 'id' => 60]);
    $now = CarbonImmutable::parse('2025-09-20 10:00:00');
    $tallyIds = collect([7]);
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 3];
    $balance = ['net' => 50.0, 'income' => 100.0, 'expense' => 50.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];
    $recentTx = [['uuid' => 'tx1', 'title' => 'Coffee', 'type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2025-09-20', 'category_name' => null, 'category_code' => '··', 'tally_name' => 'Main', 'currency_symbol' => '$']];
    $weeklyHistory = [['label' => 'W 38', 'amount' => 50.0, 'is_current' => true]];
    $categories = [['name' => 'Food', 'amount' => 50.0, 'percentage' => 100.0]];
    $sharedTally = ['uuid' => 'st1', 'name' => 'Trip', 'total_spent' => 200.0, 'members' => []];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect([['uuid' => 't1', 'name' => 'Main', 'member_count' => 1, 'monthly_total' => 50.0]]));
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn($sharedTally);

    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn($recentTx);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn($weeklyHistory);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn($categories);

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload)->toHaveKeys([
        'user_name', 'current_month_label', 'primary_tally', 'tallies',
        'balance', 'recent_transactions', 'weekly_history', 'categories_breakdown', 'shared_tally',
    ])
        ->and($payload['user_name'])->toBe('Frank')
        ->and($payload['current_month_label'])->toBe('September 2025')
        ->and($payload['primary_tally'])->toMatchArray(['uuid' => 't1', 'name' => 'Main'])
        ->and($payload['tallies'])->toHaveCount(1)
        ->and($payload['balance'])->toBe($balance)
        ->and($payload['recent_transactions'])->toBe($recentTx)
        ->and($payload['weekly_history'])->toBe($weeklyHistory)
        ->and($payload['categories_breakdown'])->toBe($categories)
        ->and($payload['shared_tally'])->toBe($sharedTally);
});

test('primary_tally contains only uuid and name not full tally summary', function (): void {
    $user = User::factory()->make(['name' => 'Grace', 'id' => 70]);
    $now = CarbonImmutable::parse('2025-11-01 08:00:00');
    $tallyIds = collect([3]);
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];
    $balance = ['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect([
        ['uuid' => 'test-uuid-123', 'name' => 'Holiday Fund', 'member_count' => 1, 'monthly_total' => 0.0],
    ]));
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload['primary_tally'])->toBe(['uuid' => 'test-uuid-123', 'name' => 'Holiday Fund']);
});

test('cache is used on second call within TTL', function (): void {
    $user = User::factory()->make(['name' => 'Dave', 'id' => 99]);
    $now = CarbonImmutable::parse('2025-07-10 09:00:00');
    $tallyIds = collect([10]);
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];
    $balance = ['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect());
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);

    // First call: hits repositories
    $service->buildHomePayload($user, $now);
    // Second call: should use cache — mocks will fail if called again
    $service->buildHomePayload($user, $now);
});
