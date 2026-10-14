<?php

declare(strict_types=1);

use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Actions\Transaction\GetTallyDebtsAction;
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
    $balanceSummary = new GetBalanceSummaryAction(new GetTallyDebtsAction());

    return new HomeService(new HomeCacheService($tallyRepo, $statsRepo, $balanceSummary), $balanceSummary);
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
        ->and($payload['balance']['net'])->toBe(0.0)
        ->and($payload['balance']['income'])->toBe(0.0)
        ->and($payload['balance']['expense'])->toBe(0.0)
        ->and($payload['balance']['currency_symbol'])->toBe('€')
        ->and($payload['balance']['currency_code'])->toBe('EUR')
        ->and($payload['balance']['decimals'])->toBe(2)
        ->and($payload)->toHaveKey('settle_suggestion')
        ->and($payload['settle_suggestion'])->toBeNull();
});

test('builds full payload when user has tallies', function (): void {
    $user = User::factory()->make(['name' => 'Bob', 'id' => 2]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $tallyIds = collect([1, 2]);
    $monthStart = $now->startOfMonth();
    $weekStart = $now->startOfWeek()->subWeeks(11);

    $tallySummaries = collect([
        ['uuid' => 'abc', 'name' => 'Personal', 'member_count' => 1, 'monthly_totals' => []],
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

    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->twice()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload['user_name'])->toBe('Bob')
        ->and($payload['current_month_label'])->toBe('May 2025')
        ->and($payload['current_month_start'])->toBe('2025-05-01')
        ->and($payload['primary_tally'])->toMatchArray(['uuid' => 'abc', 'name' => 'Personal'])
        ->and($payload['tallies'])->toHaveCount(1)
        ->and($payload['balance']['income'])->toBe(200.0)
        ->and($payload)->not->toHaveKey('shared_tally');
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

    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->twice()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

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
        HomeCacheService::key($userId, 'tally_ids'),
        HomeCacheService::key($userId, 'primary_currency'),
        HomeCacheService::key($userId, "tallies_summary:{$monthKey}"),
        HomeCacheService::key($userId, "balance:{$dayKey}"),
        HomeCacheService::key($userId, "previous_net:{$dayKey}"),
        HomeCacheService::key($userId, 'recent_tx'),
        HomeCacheService::key($userId, "weekly_history:{$dayKey}"),
        HomeCacheService::key($userId, "categories:{$monthKey}"),
        HomeCacheService::key($userId, "other_currency_expenses:{$monthKey}"),
        HomeCacheService::key($userId, 'balances'),
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

    expect($payload['current_month_label'])->toBe('March 2025')
        ->and($payload['current_month_start'])->toBe('2025-03-01');
});

test('payload contains all required top-level keys', function (): void {
    $user = User::factory()->make(['name' => 'Frank', 'id' => 60]);
    $now = CarbonImmutable::parse('2025-09-20 10:00:00');
    $tallyIds = collect([7]);
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 3];
    $balance = ['net' => 50.0, 'income' => 100.0, 'expense' => 50.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];
    $recentTx = [['uuid' => 'tx1', 'title' => 'Coffee', 'type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2025-09-20', 'category_id' => null, 'category_name' => null, 'category_code' => '··', 'tally_name' => 'Main', 'currency_symbol' => '$']];
    $weeklyHistory = [['label' => 'W 38', 'amount' => 50.0, 'is_current' => true]];
    $categories = [['name' => 'Food', 'amount' => 50.0, 'percentage' => 100.0, 'bar_fill' => 100.0, 'is_other' => false]];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect([['uuid' => 't1', 'name' => 'Main', 'member_count' => 1, 'monthly_totals' => []]]));

    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->twice()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn($recentTx);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn($weeklyHistory);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn($categories);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);
    $payload = $service->buildHomePayload($user, $now);

    expect($payload)->toHaveKeys([
        'user_name', 'current_month_label', 'current_month_start', 'primary_tally', 'tallies',
        'balance', 'recent_transactions', 'weekly_history', 'categories_breakdown', 'categories_other_currencies', 'settle_suggestion',
    ])
        ->and($payload['user_name'])->toBe('Frank')
        ->and($payload['current_month_label'])->toBe('September 2025')
        ->and($payload['current_month_start'])->toBe('2025-09-01')
        ->and($payload['primary_tally'])->toMatchArray(['uuid' => 't1', 'name' => 'Main'])
        ->and($payload['tallies'])->toHaveCount(1)
        ->and($payload['balance'])->toBe([...$balance, 'other_currencies' => [], 'previous_net' => $balance['net'], 'previous_period' => ['start' => '2025-08-01', 'end' => '2025-08-20'], 'delta_percentage' => 0.0])
        ->and($payload['recent_transactions'])->toBe($recentTx)
        ->and($payload['weekly_history'])->toBe($weeklyHistory)
        ->and($payload['categories_breakdown'])->toBe($categories)
        ->and($payload)->not->toHaveKey('shared_tally');
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
        ['uuid' => 'test-uuid-123', 'name' => 'Holiday Fund', 'member_count' => 1, 'monthly_totals' => []],
    ]));
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->twice()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

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
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->twice()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeService($tallyRepo, $statsRepo);

    // First call: hits repositories
    $service->buildHomePayload($user, $now);
    // Second call: should use cache — mocks will fail if called again
    $service->buildHomePayload($user, $now);
});

test('aggregates use the primary currency resolved from the user default currency', function (): void {
    $user = User::factory()->make(['name' => 'Fay', 'id' => 77, 'default_currency_id' => 9]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $monthStart = $now->startOfMonth();
    $tallyIds = collect([1]);
    $sameIds = Mockery::on(fn (Illuminate\Support\Collection $ids): bool => $ids->all() === [1]);
    $at = fn (CarbonImmutable $expected): Mockery\Matcher\Closure => Mockery::on(fn (CarbonImmutable $actual): bool => $actual->eq($expected));
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 9];
    $balance = ['net' => 5.0, 'income' => 10.0, 'expense' => 5.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];
    $nets = [['amount' => 50.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2]];
    $expenses = [['amount' => 20.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2]];

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('tallySummaries')->once()->with(77, $sameIds, $at($monthStart), $now)->andReturn(collect());
    $statsRepo->shouldReceive('primaryCurrency')->once()->with($sameIds, 9)->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->once()->with($sameIds, $at($monthStart), $now, $currency)->andReturn($balance);
    $statsRepo->shouldReceive('monthlyBalance')->once()->with($sameIds, $at($monthStart->subMonth()), $at($now->subMonthNoOverflow()->startOfDay()), $currency)->andReturn(['net' => 0.0] + $balance);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->with($sameIds, $at($monthStart), $now, 9)->andReturn($nets);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->with($sameIds, $at($now->startOfWeek()->subWeeks(11)), $now, 9)->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->with($sameIds, $at($monthStart), $now, 9)->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->with($sameIds, $at($monthStart), $now, 9)->andReturn($expenses);

    $payload = makeService($tallyRepo, $statsRepo)->buildHomePayload($user, $now);

    expect($payload['balance'])->toBe([...$balance, 'other_currencies' => $nets, 'previous_net' => 0.0, 'previous_period' => ['start' => '2025-04-01', 'end' => '2025-04-15'], 'delta_percentage' => null])
        ->and($payload['categories_other_currencies'])->toBe($expenses);
});

test('each tally gets its cached my_balance and the payload the settle suggestion in the primary currency', function (): void {
    $user = User::factory()->make(['name' => 'Hana', 'id' => 81]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 4];
    $balance = ['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];
    $usd = ['code' => 'USD', 'symbol' => '$', 'decimals' => 2];
    $eur = ['code' => 'EUR', 'symbol' => '€', 'decimals' => 2];
    $tripBalance = [['currency' => $usd, 'amount' => 1250, 'direction' => 'owed_to_me', 'counterparty_name' => 'Noah', 'counterparties_count' => 1]];
    $homeBalance = [['currency' => $eur, 'amount' => -900, 'direction' => 'i_owe', 'counterparty_name' => 'Priya', 'counterparties_count' => 1]];
    Cache::put(HomeCacheService::key(81, 'balances'), [
        'my_balances' => ['trip' => $tripBalance, 'home' => $homeBalance],
        'owed_to_me' => [
            ['tally_uuid' => 'home', 'currency_id' => 3, 'currency' => $eur, 'counterparty_name' => 'Priya', 'amount' => 9000],
            ['tally_uuid' => 'trip', 'currency_id' => 4, 'currency' => $usd, 'counterparty_name' => 'Noah', 'amount' => 1250],
        ],
    ], 60);

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([1, 2]));
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect([
        ['uuid' => 'home', 'name' => 'Household', 'member_count' => 2, 'monthly_totals' => []],
        ['uuid' => 'trip', 'name' => 'Trip', 'member_count' => 2, 'monthly_totals' => []],
    ]));
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyBalance')->twice()->andReturn($balance);
    $statsRepo->shouldReceive('recentTransactions')->once()->andReturn([]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $payload = makeService($tallyRepo, $statsRepo)->buildHomePayload($user, $now);

    expect($payload['tallies'])->toBe([
        ['uuid' => 'home', 'name' => 'Household', 'member_count' => 2, 'monthly_totals' => [], 'my_balance' => $homeBalance],
        ['uuid' => 'trip', 'name' => 'Trip', 'member_count' => 2, 'monthly_totals' => [], 'my_balance' => $tripBalance],
    ])
        ->and($payload['primary_tally'])->toBe(['uuid' => 'home', 'name' => 'Household'])
        ->and($payload['settle_suggestion'])->toBe(['tally_uuid' => 'trip', 'counterparty_name' => 'Noah', 'amount' => 1250, 'currency' => $usd]);
});

test('myBalances returns the cached balances of every accessible tally, keyed by uuid', function (): void {
    $user = User::factory()->make(['id' => 82]);
    $myBalances = ['abc' => [['currency' => ['code' => 'EUR', 'symbol' => '€', 'decimals' => 2], 'amount' => 0, 'direction' => 'settled', 'counterparty_name' => null, 'counterparties_count' => 0]]];
    Cache::put(HomeCacheService::key(82, 'tally_ids'), [5], 60);
    Cache::put(HomeCacheService::key(82, 'balances'), ['my_balances' => $myBalances, 'owed_to_me' => []], 60);

    expect(makeService(mockTallyRepo(), mockStatsRepo())->myBalances($user))->toBe($myBalances);
});

test('primaryCurrency resolves the default currency and hands out symbol, code and decimals only', function (): void {
    $user = User::factory()->make(['id' => 91, 'default_currency_id' => 6]);
    $sameIds = Mockery::on(fn (Illuminate\Support\Collection $ids): bool => $ids->all() === [3, 4]);

    $tallyRepo = mockTallyRepo();
    $statsRepo = mockStatsRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->with(91)->andReturn(collect([3, 4]));
    $statsRepo->shouldReceive('primaryCurrency')->once()->with($sameIds, 6)->andReturn(['symbol' => '¥', 'code' => 'JPY', 'decimals' => 0, 'currency_id' => 6]);

    expect(makeService($tallyRepo, $statsRepo)->primaryCurrency($user))->toBe(['symbol' => '¥', 'code' => 'JPY', 'decimals' => 0]);
});
