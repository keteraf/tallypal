<?php

declare(strict_types=1);

use App\Repositories\Contracts\TallyRepositoryInterface;
use App\Repositories\Contracts\TransactionStatsRepositoryInterface;
use App\Services\HomeCacheService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

covers(HomeCacheService::class);

beforeEach(function (): void {
    Cache::flush();
});

function makeCacheService(
    TallyRepositoryInterface $tallyRepo,
    TransactionStatsRepositoryInterface $statsRepo,
): HomeCacheService {
    return new HomeCacheService($tallyRepo, $statsRepo);
}

function stubTallyRepo(): TallyRepositoryInterface&MockInterface
{
    return Mockery::mock(TallyRepositoryInterface::class);
}

function stubStatsRepo(): TransactionStatsRepositoryInterface&MockInterface
{
    return Mockery::mock(TransactionStatsRepositoryInterface::class);
}

// ─── tallyIds ─────────────────────────────────────────────────────────────────

test('tallyIds returns ids from repository', function (): void {
    $userId = 1;
    $expected = collect([10, 20]);

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->with($userId)->andReturn($expected);

    $result = makeCacheService($tallyRepo, stubStatsRepo())->tallyIds($userId);

    expect($result)->toEqual($expected);
});

test('tallyIds caches the result so repository is only called once', function (): void {
    $userId = 2;

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([5]));

    $service = makeCacheService($tallyRepo, stubStatsRepo());
    $service->tallyIds($userId);
    $service->tallyIds($userId);
});

test('tallyIds stores result under user-scoped cache key', function (): void {
    $userId = 3;

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([7]));

    makeCacheService($tallyRepo, stubStatsRepo())->tallyIds($userId);

    expect(Cache::has("home:user:{$userId}:tally_ids"))->toBeTrue();
});

// ─── primaryCurrency ──────────────────────────────────────────────────────────

test('primaryCurrency returns currency from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $expected = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('primaryCurrency')->once()->with($tallyIds)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->primaryCurrency($userId, $tallyIds);

    expect($result)->toBe($expected);
});

test('primaryCurrency caches the result', function (): void {
    $userId = 4;
    $tallyIds = collect([1]);

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 2]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->primaryCurrency($userId, $tallyIds);
    $service->primaryCurrency($userId, $tallyIds);
});

test('primaryCurrency stores result under user-scoped cache key', function (): void {
    $userId = 5;
    $tallyIds = collect([1]);

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1]);

    makeCacheService(stubTallyRepo(), $statsRepo)->primaryCurrency($userId, $tallyIds);

    expect(Cache::has("home:user:{$userId}:primary_currency"))->toBeTrue();
});

// ─── tallySummaries ───────────────────────────────────────────────────────────

test('tallySummaries returns summaries from tally repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1, 2]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');
    $expected = collect([['uuid' => 'abc', 'name' => 'Main', 'member_count' => 1, 'monthly_total' => 100.0]]);

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('tallySummaries')->once()->with($userId, $tallyIds, $monthStart)->andReturn($expected);

    $result = makeCacheService($tallyRepo, stubStatsRepo())->tallySummaries($userId, $tallyIds, $monthStart, $monthKey);

    expect($result)->toEqual($expected);
});

test('tallySummaries caches the result', function (): void {
    $userId = 6;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-06-01');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect());

    $service = makeCacheService($tallyRepo, stubStatsRepo());
    $service->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $now->format('Y-m'));
    $service->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $now->format('Y-m'));
});

test('tallySummaries stores result under month-scoped cache key', function (): void {
    $userId = 7;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-07-10');
    $monthKey = $now->format('Y-m');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect());

    makeCacheService($tallyRepo, stubStatsRepo())->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $monthKey);

    expect(Cache::has("home:user:{$userId}:tallies_summary:{$monthKey}"))->toBeTrue();
});

// ─── monthlyBalance ───────────────────────────────────────────────────────────

test('monthlyBalance returns balance from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $dayKey = $now->format('Y-m-d');
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];
    $expected = ['net' => 50.0, 'income' => 100.0, 'expense' => 50.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('monthlyBalance')->once()->with($tallyIds, $monthStart, $now, $currency)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->monthlyBalance($userId, $tallyIds, $monthStart, $now, $currency, $dayKey);

    expect($result)->toBe($expected);
});

test('monthlyBalance caches the result', function (): void {
    $userId = 8;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-08-20');
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 2];
    $balance = ['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->monthlyBalance($userId, $tallyIds, $now->startOfMonth(), $now, $currency, $now->format('Y-m-d'));
    $service->monthlyBalance($userId, $tallyIds, $now->startOfMonth(), $now, $currency, $now->format('Y-m-d'));
});

test('monthlyBalance stores result under day-scoped cache key', function (): void {
    $userId = 9;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-09-05');
    $dayKey = $now->format('Y-m-d');
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn(['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2]);

    makeCacheService(stubTallyRepo(), $statsRepo)->monthlyBalance($userId, $tallyIds, $now->startOfMonth(), $now, $currency, $dayKey);

    expect(Cache::has("home:user:{$userId}:balance:{$dayKey}"))->toBeTrue();
});

// ─── recentTransactions ───────────────────────────────────────────────────────

test('recentTransactions returns transactions from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $expected = [['uuid' => 'tx1', 'title' => 'Coffee', 'type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2025-05-01', 'category_name' => null, 'category_code' => '··', 'tally_name' => 'Main', 'currency_symbol' => '€']];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('recentTransactions')->once()->with($tallyIds)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->recentTransactions($userId, $tallyIds);

    expect($result)->toBe($expected);
});

test('recentTransactions caches the result', function (): void {
    $userId = 10;
    $tallyIds = collect([1]);

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->recentTransactions($userId, $tallyIds);
    $service->recentTransactions($userId, $tallyIds);
});

test('recentTransactions stores result under user-scoped cache key', function (): void {
    $userId = 11;
    $tallyIds = collect([1]);

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);

    makeCacheService(stubTallyRepo(), $statsRepo)->recentTransactions($userId, $tallyIds);

    expect(Cache::has("home:user:{$userId}:recent_tx"))->toBeTrue();
});

// ─── weeklyHistory ────────────────────────────────────────────────────────────

test('weeklyHistory returns history from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $weekStart = $now->startOfWeek()->subWeeks(11);
    $dayKey = $now->format('Y-m-d');
    $expected = [['label' => 'W 20', 'amount' => 50.0, 'is_current' => true]];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('weeklyHistory')->once()->with($tallyIds, $weekStart, $now)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->weeklyHistory($userId, $tallyIds, $weekStart, $now, $dayKey);

    expect($result)->toBe($expected);
});

test('weeklyHistory caches the result', function (): void {
    $userId = 12;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-10-01');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, $now->format('Y-m-d'));
    $service->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, $now->format('Y-m-d'));
});

test('weeklyHistory stores result under day-scoped cache key', function (): void {
    $userId = 13;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-11-10');
    $dayKey = $now->format('Y-m-d');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);

    makeCacheService(stubTallyRepo(), $statsRepo)->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, $dayKey);

    expect(Cache::has("home:user:{$userId}:weekly_history:{$dayKey}"))->toBeTrue();
});

// ─── categoriesBreakdown ──────────────────────────────────────────────────────

test('categoriesBreakdown returns breakdown from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');
    $expected = [['name' => 'Food', 'amount' => 50.0, 'percentage' => 100.0]];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->with($tallyIds, $monthStart, $now)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->categoriesBreakdown($userId, $tallyIds, $monthStart, $now, $monthKey);

    expect($result)->toBe($expected);
});

test('categoriesBreakdown caches the result', function (): void {
    $userId = 14;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-12-01');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->categoriesBreakdown($userId, $tallyIds, $now->startOfMonth(), $now, $now->format('Y-m'));
    $service->categoriesBreakdown($userId, $tallyIds, $now->startOfMonth(), $now, $now->format('Y-m'));
});

test('categoriesBreakdown stores result under month-scoped cache key', function (): void {
    $userId = 15;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-01-20');
    $monthKey = $now->format('Y-m');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    makeCacheService(stubTallyRepo(), $statsRepo)->categoriesBreakdown($userId, $tallyIds, $now->startOfMonth(), $now, $monthKey);

    expect(Cache::has("home:user:{$userId}:categories:{$monthKey}"))->toBeTrue();
});

// ─── sharedTally ──────────────────────────────────────────────────────────────

test('sharedTally returns array from tally repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');
    $expected = ['uuid' => 'st1', 'name' => 'Trip', 'total_spent' => 200.0, 'members' => []];

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->with($tallyIds, $monthStart, $now)->andReturn($expected);

    $result = makeCacheService($tallyRepo, stubStatsRepo())->sharedTally($userId, $tallyIds, $monthStart, $now, $monthKey);

    expect($result)->toBe($expected);
});

test('sharedTally returns null when repository returns null', function (): void {
    $userId = 16;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);

    $result = makeCacheService($tallyRepo, stubStatsRepo())->sharedTally($userId, $tallyIds, $monthStart, $now, $monthKey);

    expect($result)->toBeNull();
});

test('sharedTally caches null result so repository is only called once', function (): void {
    $userId = 17;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);

    $service = makeCacheService($tallyRepo, stubStatsRepo());
    expect($service->sharedTally($userId, $tallyIds, $monthStart, $now, $monthKey))->toBeNull();
    expect($service->sharedTally($userId, $tallyIds, $monthStart, $now, $monthKey))->toBeNull();
});

test('sharedTally stores result under month-scoped cache key', function (): void {
    $userId = 18;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-03-10');
    $monthKey = $now->format('Y-m');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('sharedTallySummary')->once()->andReturn(null);

    makeCacheService($tallyRepo, stubStatsRepo())->sharedTally($userId, $tallyIds, $now->startOfMonth(), $now, $monthKey);

    expect(Cache::has("home:user:{$userId}:shared_tally:{$monthKey}"))->toBeTrue();
});

// ─── forgetForUser ────────────────────────────────────────────────────────────

test('forgetForUser removes all cache keys for the user', function (): void {
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
        Cache::put($key, 'value', 60);
    }

    makeCacheService(stubTallyRepo(), stubStatsRepo())->forgetForUser($userId);

    foreach ($keys as $key) {
        expect(Cache::has($key))->toBeFalse("Expected [{$key}] to be forgotten");
    }
});

test('forgetForUser does not remove cache keys for other users', function (): void {
    $userId = 50;
    $otherId = 51;
    $now = CarbonImmutable::now();

    Cache::put("home:user:{$otherId}:tally_ids", 'other-value', 60);
    Cache::put("home:user:{$userId}:tally_ids", 'my-value', 60);

    makeCacheService(stubTallyRepo(), stubStatsRepo())->forgetForUser($userId);

    expect(Cache::has("home:user:{$otherId}:tally_ids"))->toBeTrue()
        ->and(Cache::has("home:user:{$userId}:tally_ids"))->toBeFalse();
});
