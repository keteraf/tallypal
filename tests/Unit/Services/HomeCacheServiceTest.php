<?php

declare(strict_types=1);

use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Actions\Transaction\GetTallyDebtsAction;
use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
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
    return new HomeCacheService($tallyRepo, $statsRepo, new GetBalanceSummaryAction(new GetTallyDebtsAction()));
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

    expect(Cache::has(HomeCacheService::key($userId, 'tally_ids')))->toBeTrue();
});

// ─── primaryCurrency ──────────────────────────────────────────────────────────

test('primaryCurrency returns currency from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $expected = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('primaryCurrency')->once()->with($tallyIds, 3)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->primaryCurrency($userId, $tallyIds, 3);

    expect($result)->toBe($expected);
});

test('primaryCurrency caches the result', function (): void {
    $userId = 4;
    $tallyIds = collect([1]);

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 2]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->primaryCurrency($userId, $tallyIds, null);
    $service->primaryCurrency($userId, $tallyIds, null);
});

test('primaryCurrency stores result under user-scoped cache key', function (): void {
    $userId = 5;
    $tallyIds = collect([1]);

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1]);

    makeCacheService(stubTallyRepo(), $statsRepo)->primaryCurrency($userId, $tallyIds, null);

    expect(Cache::has(HomeCacheService::key($userId, 'primary_currency')))->toBeTrue();
});

// ─── tallySummaries ───────────────────────────────────────────────────────────

test('tallySummaries returns summaries from tally repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1, 2]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');
    $expected = collect([['uuid' => 'abc', 'name' => 'Main', 'member_count' => 1, 'monthly_totals' => []]]);

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('tallySummaries')->once()->with($userId, $tallyIds, $monthStart, $now)->andReturn($expected);

    $result = makeCacheService($tallyRepo, stubStatsRepo())->tallySummaries($userId, $tallyIds, $monthStart, $now, $monthKey);

    expect($result)->toEqual($expected);
});

test('tallySummaries caches the result', function (): void {
    $userId = 6;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-06-01');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect());

    $service = makeCacheService($tallyRepo, stubStatsRepo());
    $service->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $now, $now->format('Y-m'));
    $service->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $now, $now->format('Y-m'));
});

test('tallySummaries stores result under month-scoped cache key', function (): void {
    $userId = 7;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-07-10');
    $monthKey = $now->format('Y-m');

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('tallySummaries')->once()->andReturn(collect());

    makeCacheService($tallyRepo, stubStatsRepo())->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $now, $monthKey);

    expect(Cache::has(HomeCacheService::key($userId, "tallies_summary:{$monthKey}")))->toBeTrue();
});

// ─── monthlyBalance ───────────────────────────────────────────────────────────

test('monthlyBalance returns balance from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $dayKey = $now->format('Y-m-d');
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];
    $balance = ['net' => 50.0, 'income' => 100.0, 'expense' => 50.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2];
    $otherCurrencies = [['amount' => 7.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2]];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('monthlyBalance')->once()->with($tallyIds, $monthStart, $now, $currency)->andReturn($balance);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->with($tallyIds, $monthStart, $now, 1)->andReturn($otherCurrencies);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->monthlyBalance($userId, $tallyIds, $monthStart, $now, $currency, $dayKey);

    expect($result)->toBe([...$balance, 'other_currencies' => $otherCurrencies]);
});

test('monthlyBalance caches the result', function (): void {
    $userId = 8;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-08-20');
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 2];
    $balance = ['net' => 0.0, 'income' => 0.0, 'expense' => 0.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('monthlyBalance')->once()->andReturn($balance);
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);

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
    $statsRepo->shouldReceive('otherCurrencyNets')->once()->andReturn([]);

    makeCacheService(stubTallyRepo(), $statsRepo)->monthlyBalance($userId, $tallyIds, $now->startOfMonth(), $now, $currency, $dayKey);

    expect(Cache::has(HomeCacheService::key($userId, "balance:{$dayKey}")))->toBeTrue();
});

// ─── recentTransactions ───────────────────────────────────────────────────────

test('recentTransactions returns transactions from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $expected = [['uuid' => 'tx1', 'title' => 'Coffee', 'type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2025-05-01', 'category_id' => null, 'category_name' => null, 'tally_name' => 'Main', 'currency_symbol' => '€']];

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

    expect(Cache::has(HomeCacheService::key($userId, 'recent_tx')))->toBeTrue();
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
    $statsRepo->shouldReceive('weeklyHistory')->once()->with($tallyIds, $weekStart, $now, 4)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->weeklyHistory($userId, $tallyIds, $weekStart, $now, 4, $dayKey);

    expect($result)->toBe($expected);
});

test('weeklyHistory caches the result', function (): void {
    $userId = 12;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-10-01');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, 1, $now->format('Y-m-d'));
    $service->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, 1, $now->format('Y-m-d'));
});

test('weeklyHistory stores result under day-scoped cache key', function (): void {
    $userId = 13;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-11-10');
    $dayKey = $now->format('Y-m-d');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);

    makeCacheService(stubTallyRepo(), $statsRepo)->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, 1, $dayKey);

    expect(Cache::has(HomeCacheService::key($userId, "weekly_history:{$dayKey}")))->toBeTrue();
});

// ─── categoriesBreakdown ──────────────────────────────────────────────────────

test('categoriesBreakdown returns breakdown from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $monthKey = $now->format('Y-m');
    $categories = [['name' => 'Food', 'amount' => 50.0, 'percentage' => 100.0]];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->with($tallyIds, $monthStart, $now, 4)->andReturn($categories);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->categoriesBreakdown($userId, $tallyIds, $monthStart, $now, 4, $monthKey);

    expect($result)->toBe([['name' => 'Food', 'amount' => 50.0, 'percentage' => 100.0, 'bar_fill' => 100.0, 'is_other' => false]]);
});

test('categoriesBreakdown folds the tail beyond the top five into one Other row of the true total', function (): void {
    $now = CarbonImmutable::parse('2025-05-15');
    $categories = array_map(
        static fn (int $rank, float $amount): array => ['name' => "Cat{$rank}", 'amount' => $amount, 'percentage' => 0.0],
        range(1, 8),
        [40.0, 30.0, 20.0, 10.0, 5.0, 3.0, 2.0, 1.0],
    );

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn($categories);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->categoriesBreakdown(1, collect([1]), $now->startOfMonth(), $now, 4, $now->format('Y-m'));

    expect($result)->toHaveCount(6)
        ->and($result[5])->toBe(['name' => 'Other', 'slug' => null, 'is_uncategorised' => false, 'amount' => 6.0, 'percentage' => 5.4, 'bar_fill' => 15.0, 'is_other' => true])
        ->and(array_sum(array_column($result, 'amount')))->toBe(111.0);
});

test('categoriesBreakdown caches the result', function (): void {
    $userId = 14;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-12-01');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->categoriesBreakdown($userId, $tallyIds, $now->startOfMonth(), $now, 1, $now->format('Y-m'));
    $service->categoriesBreakdown($userId, $tallyIds, $now->startOfMonth(), $now, 1, $now->format('Y-m'));
});

test('categoriesBreakdown stores result under month-scoped cache key', function (): void {
    $userId = 15;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-01-20');
    $monthKey = $now->format('Y-m');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);

    makeCacheService(stubTallyRepo(), $statsRepo)->categoriesBreakdown($userId, $tallyIds, $now->startOfMonth(), $now, 1, $monthKey);

    expect(Cache::has(HomeCacheService::key($userId, "categories:{$monthKey}")))->toBeTrue();
});

// ─── otherCurrencyExpenses ────────────────────────────────────────────────────

test('otherCurrencyExpenses returns the other currencies from stats repository', function (): void {
    $userId = 1;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-05-15');
    $monthStart = $now->startOfMonth();
    $expected = [['amount' => 12.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2]];

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->with($tallyIds, $monthStart, $now, 4)->andReturn($expected);

    $result = makeCacheService(stubTallyRepo(), $statsRepo)->otherCurrencyExpenses($userId, $tallyIds, $monthStart, $now, 4, $now->format('Y-m'));

    expect($result)->toBe($expected);
});

test('otherCurrencyExpenses caches the result under a month-scoped cache key', function (): void {
    $userId = 16;
    $tallyIds = collect([1]);
    $now = CarbonImmutable::parse('2025-02-20');
    $monthKey = $now->format('Y-m');

    $statsRepo = stubStatsRepo();
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeCacheService(stubTallyRepo(), $statsRepo);
    $service->otherCurrencyExpenses($userId, $tallyIds, $now->startOfMonth(), $now, 1, $monthKey);
    $service->otherCurrencyExpenses($userId, $tallyIds, $now->startOfMonth(), $now, 1, $monthKey);

    expect(Cache::has(HomeCacheService::key($userId, "other_currency_expenses:{$monthKey}")))->toBeTrue();
});

// ─── balanceSummary ───────────────────────────────────────────────────────────

test('balanceSummary computes the balances of every tally of the user and caches them under the balances key', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($eur)->create(['type' => 'expense', 'amount' => 30.00]);
    Cache::flush();

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->with((int) $owner->id)->andReturn(collect([$tally->tally_id]));

    $summary = makeCacheService($tallyRepo, stubStatsRepo())->balanceSummary((int) $owner->id);

    expect($summary['my_balances'][$tally->uuid][0]['amount'])->toBe(1500)
        ->and($summary['owed_to_me'][0]['counterparty_name'])->toBe($member->name)
        ->and(Cache::get(HomeCacheService::key((int) $owner->id, 'balances')))->toBe($summary);
});

test('balanceSummary reads the cached value instead of recomputing', function (): void {
    $cached = ['my_balances' => ['abc' => []], 'owed_to_me' => []];
    Cache::put(HomeCacheService::key(18, 'balances'), $cached, 60);

    expect(makeCacheService(stubTallyRepo(), stubStatsRepo())->balanceSummary(18))->toBe($cached);
});

// ─── key ──────────────────────────────────────────────────────────────────────

test('cache keys carry the version segment so entries of a previous release are never read', function (): void {
    expect(HomeCacheService::key(7, 'balance:2025-05-15'))->toBe('home:' . HomeCacheService::KEY_VERSION . ':user:7:balance:2025-05-15')
        ->and(HomeCacheService::KEY_VERSION)->toBe('v14');
});

test('a value cached under the unversioned key of the previous release is ignored', function (): void {
    $userId = 17;
    Cache::put("home:user:{$userId}:tally_ids", 'stale-shape', 60);

    $tallyRepo = stubTallyRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([3]));

    expect(makeCacheService($tallyRepo, stubStatsRepo())->tallyIds($userId)->all())->toBe([3]);
});

// ─── sidebarVersion ───────────────────────────────────────────────────────────

test('sidebarVersion is a 12 character token that stays the same until the user cache is forgotten', function (): void {
    $service = makeCacheService(stubTallyRepo(), stubStatsRepo());

    $first = $service->sidebarVersion(31);

    expect($first)->toHaveLength(12)
        ->and($service->sidebarVersion(31))->toBe($first)
        ->and($service->sidebarVersion(32))->not->toBe($first);

    $service->forgetForUser(31);

    expect($service->sidebarVersion(31))->toHaveLength(12)->not->toBe($first);
});

// ─── forgetForUser ────────────────────────────────────────────────────────────

test('forgetForUser removes all cache keys for the user', function (): void {
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

    Cache::put(HomeCacheService::key($otherId, 'tally_ids'), 'other-value', 60);
    Cache::put(HomeCacheService::key($userId, 'tally_ids'), 'my-value', 60);

    makeCacheService(stubTallyRepo(), stubStatsRepo())->forgetForUser($userId);

    expect(Cache::has(HomeCacheService::key($otherId, 'tally_ids')))->toBeTrue()
        ->and(Cache::has(HomeCacheService::key($userId, 'tally_ids')))->toBeFalse();
});
