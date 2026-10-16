<?php

declare(strict_types=1);

use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Actions\Transaction\GetTallyDebtsAction;
use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Enums\StatsRange;
use App\Models\User;
use App\Services\HomeCacheService;
use App\Services\StatsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

covers(StatsService::class);

beforeEach(function (): void {
    Cache::flush();
});

function makeStatsService(
    TallyRepositoryInterface $tallyRepo,
    TransactionStatsRepositoryInterface $statsRepo,
): StatsService {
    return new StatsService(new HomeCacheService($tallyRepo, $statsRepo, new GetBalanceSummaryAction(new GetTallyDebtsAction())), $statsRepo, $tallyRepo);
}

function mockStatsTallyRepo(): TallyRepositoryInterface|MockInterface
{
    return Mockery::mock(TallyRepositoryInterface::class);
}

function mockStatsStatsRepo(): TransactionStatsRepositoryInterface|MockInterface
{
    return Mockery::mock(TransactionStatsRepositoryInterface::class);
}

test('returns empty payload when user has no tallies', function (): void {
    $user = User::factory()->make(['id' => 1]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->with(1)->andReturn(collect());

    $service = makeStatsService($tallyRepo, $statsRepo);
    $payload = $service->buildStatsPayload($user, StatsRange::MONTH, $now);

    expect($payload['range'])->toBe('month')
        ->and($payload['range_label'])->toBe('May 2025')
        ->and($payload['total_out'])->toBe(0.0)
        ->and($payload['currency_symbol'])->toBe('€')
        ->and($payload['currency_code'])->toBe('EUR')
        ->and($payload['decimals'])->toBe(2)
        ->and($payload['series'])->toBeEmpty()
        ->and($payload['categories'])->toBeEmpty();
});

test('month range uses the 12-week trend from weeklyHistory', function (): void {
    $user = User::factory()->make(['id' => 2]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $tallyIds = collect([1, 2]);
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];
    $weeklyHistory = [['label' => 'W 20', 'amount' => 40.0, 'is_current' => true]];
    $categories = [['name' => 'Food', 'amount' => 40.0, 'percentage' => 100.0]];

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn($weeklyHistory);
    $statsRepo->shouldNotReceive('dailyHistory');
    $statsRepo->shouldNotReceive('monthlyHistory');
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(40.0, 0.0, 0.0);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn($categories);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeStatsService($tallyRepo, $statsRepo);
    $payload = $service->buildStatsPayload($user, StatsRange::MONTH, $now);

    expect($payload['range'])->toBe('month')
        ->and($payload['range_label'])->toBe('May 2025')
        ->and($payload['total_out'])->toBe(40.0)
        ->and($payload['series'])->toBe($weeklyHistory)
        ->and($payload['categories'])->toBe([['name' => 'Food', 'amount' => 40.0, 'percentage' => 100.0, 'bar_fill' => 100.0, 'is_other' => false]]);
});

test('week range uses dailyHistory and a week date-range label', function (): void {
    $user = User::factory()->make(['id' => 3]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00'); // Thursday
    $tallyIds = collect([1]);
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 2];
    $dailyHistory = [['label' => 'Mon', 'amount' => 10.0, 'is_current' => false]];

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('dailyHistory')->once()->andReturn($dailyHistory);
    $statsRepo->shouldNotReceive('weeklyHistory');
    $statsRepo->shouldNotReceive('monthlyHistory');
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(10.0, 0.0, 0.0);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeStatsService($tallyRepo, $statsRepo);
    $payload = $service->buildStatsPayload($user, StatsRange::WEEK, $now);

    expect($payload['range'])->toBe('week')
        ->and($payload['series'])->toBe($dailyHistory)
        ->and($payload['range_label'])->toBe(
            $now->startOfWeek()->format('M j') . ' – ' . $now->startOfWeek()->addDays(6)->format('M j'),
        );
});

test('year range uses monthlyHistory and the calendar year as the label', function (): void {
    $user = User::factory()->make(['id' => 4]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $tallyIds = collect([1]);
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1];
    $monthlyHistory = [['label' => 'Jan', 'amount' => 0.0, 'is_current' => false]];

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('monthlyHistory')->once()->andReturn($monthlyHistory);
    $statsRepo->shouldNotReceive('weeklyHistory');
    $statsRepo->shouldNotReceive('dailyHistory');
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(0.0, 0.0, 0.0);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeStatsService($tallyRepo, $statsRepo);
    $payload = $service->buildStatsPayload($user, StatsRange::YEAR, $now);

    expect($payload['range'])->toBe('year')
        ->and($payload['range_label'])->toBe('2025')
        ->and($payload['series'])->toBe($monthlyHistory);
});

test('payload contains all required top-level keys', function (): void {
    $user = User::factory()->make(['id' => 5]);
    $now = CarbonImmutable::parse('2025-09-20 10:00:00');
    $tallyIds = collect([7]);
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 3];

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn($currency);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(0.0, 0.0, 0.0);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    $service = makeStatsService($tallyRepo, $statsRepo);
    $payload = $service->buildStatsPayload($user, StatsRange::MONTH, $now);

    expect(array_keys($payload))->toBe([
        'range', 'range_label', 'total_out', 'currency_symbol', 'currency_code', 'decimals', 'series', 'categories', 'other_currencies',
        'daily_average', 'previous_total_out', 'previous_daily_average', 'previous_period', 'delta_percentage', 'shared_total_out',
        'shared_percentage', 'owed_to_me', 'activity', 'filter', 'tallies',
    ]);
});

test('aggregates use the primary currency resolved from the user default currency', function (): void {
    $user = User::factory()->make(['id' => 6, 'default_currency_id' => 9]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $periodStart = $now->startOfMonth();
    $tallyIds = collect([1, 2]);
    $sameIds = Mockery::on(fn (Illuminate\Support\Collection $ids): bool => $ids->all() === [1, 2]);
    $sharedIds = Mockery::on(fn (Illuminate\Support\Collection $ids): bool => $ids->all() === [2]);
    $at = fn (CarbonImmutable $expected): Mockery\Matcher\Closure => Mockery::on(fn (CarbonImmutable $actual): bool => $actual->eq($expected));
    $currency = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => 9];
    $others = [['amount' => 20.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2]];
    $options = collect([
        ['tally_id' => 2, 'uuid' => 'b', 'name' => 'Beach', 'is_shared' => true],
        ['tally_id' => 1, 'uuid' => 'a', 'name' => 'Rent', 'is_shared' => false],
    ]);

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $tallyRepo->shouldReceive('filterOptions')->once()->with($sameIds)->andReturn($options);
    $statsRepo->shouldReceive('primaryCurrency')->once()->with($sameIds, 9)->andReturn($currency);
    $statsRepo->shouldReceive('weeklyHistory')->once()->with($sameIds, $at($now->startOfWeek()->subWeeks(11)), $now, 9)->andReturn([]);
    $statsRepo->shouldReceive('expenseTotal')->once()->with($sameIds, $at($periodStart), $now, 9)->andReturn(30.0);
    $statsRepo->shouldReceive('expenseTotal')->once()->with($sameIds, $at(CarbonImmutable::parse('2025-04-01')), $at(CarbonImmutable::parse('2025-04-15')), 9)->andReturn(15.0);
    $statsRepo->shouldReceive('expenseTotal')->once()->with($sharedIds, $at($periodStart), $now, 9)->andReturn(12.0);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->with($sameIds, $at($periodStart), $now, 9)->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->with($sameIds, $at($periodStart), $now, 9)->andReturn($others);
    $statsRepo->shouldReceive('activity')->once()->with($sameIds, $at($periodStart), $now, 20)->andReturn([]);

    $payload = makeStatsService($tallyRepo, $statsRepo)->buildStatsPayload($user, StatsRange::MONTH, $now);

    expect($payload['currency_code'])->toBe('USD')
        ->and($payload['total_out'])->toBe(30.0)
        ->and($payload['daily_average'])->toBe(2.0)
        ->and($payload['previous_total_out'])->toBe(15.0)
        ->and($payload['previous_daily_average'])->toBe(1.0)
        ->and($payload['delta_percentage'])->toBe(100.0)
        ->and($payload['shared_total_out'])->toBe(12.0)
        ->and($payload['shared_percentage'])->toBe(40.0)
        ->and($payload['other_currencies'])->toBe($others)
        ->and($payload['tallies'])->toBe([['uuid' => 'b', 'name' => 'Beach', 'is_shared' => true], ['uuid' => 'a', 'name' => 'Rent', 'is_shared' => false]]);
});

test('week and year ranges pass the primary currency to their series', function (StatsRange $range, string $method): void {
    $user = User::factory()->make(['id' => 7]);
    $now = CarbonImmutable::parse('2025-05-15 10:00:00');
    $tallyIds = collect([1]);
    $sameIds = Mockery::on(fn (Illuminate\Support\Collection $ids): bool => $ids->all() === [1]);
    $at = fn (CarbonImmutable $expected): Mockery\Matcher\Closure => Mockery::on(fn (CarbonImmutable $actual): bool => $actual->eq($expected));
    $currency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 4];

    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();

    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn($tallyIds);
    $statsRepo->shouldReceive('primaryCurrency')->once()->with($sameIds, null)->andReturn($currency);
    $statsRepo->shouldReceive($method)->once()->with($sameIds, Mockery::any(), $now, 4)->andReturn([]);
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(0.0, 0.0, 0.0);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);

    makeStatsService($tallyRepo, $statsRepo)->buildStatsPayload($user, $range, $now);
})->with([
    'week' => [StatsRange::WEEK, 'dailyHistory'],
    'year' => [StatsRange::YEAR, 'monthlyHistory'],
]);

test('the empty payload is exactly the zeroed Stats shape', function (): void {
    $user = User::factory()->make(['id' => 1]);
    $tallyRepo = mockStatsTallyRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect());

    $payload = makeStatsService($tallyRepo, mockStatsStatsRepo())->buildStatsPayload($user, StatsRange::MONTH, CarbonImmutable::parse('2025-05-15 10:00:00'));

    expect($payload)->toBe([
        'range' => 'month',
        'range_label' => 'May 2025',
        'total_out' => 0.0,
        'currency_symbol' => '€',
        'currency_code' => 'EUR',
        'decimals' => 2,
        'series' => [],
        'categories' => [],
        'other_currencies' => [],
        'daily_average' => 0.0,
        'previous_total_out' => 0.0,
        'previous_daily_average' => 0.0,
        'previous_period' => null,
        'delta_percentage' => null,
        'shared_total_out' => 0.0,
        'shared_percentage' => null,
        'owed_to_me' => ['amount_minor' => 0, 'open_balances' => 0, 'other_currencies' => []],
        'activity' => [],
        'filter' => ['tally_uuid' => null],
        'tallies' => [],
    ]);
});

test('the activity feed is limited to twenty rows', function (): void {
    $user = User::factory()->make(['id' => 6]);
    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([7]));
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(0.0);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);
    $statsRepo->shouldReceive('activity')->once()->withArgs(fn ($ids, $start, $end, int $limit): bool => $limit === 20)->andReturn([]);

    makeStatsService($tallyRepo, $statsRepo)->buildStatsPayload($user, StatsRange::MONTH, CarbonImmutable::parse('2025-05-15 10:00:00'));
});

test('owed to me totals each other currency once, however many debts it has', function (): void {
    $user = User::factory()->make(['id' => 8]);
    $debt = fn (int $currencyId, string $code, string $symbol, int $amount): array => [
        'tally_uuid' => 't-1',
        'currency_id' => $currencyId,
        'currency' => ['code' => $code, 'symbol' => $symbol, 'decimals' => 2],
        'amount' => $amount,
    ];
    Cache::put(HomeCacheService::key(8, 'balances'), ['my_balances' => [], 'owed_to_me' => [
        $debt(1, 'EUR', '€', 1000),
        $debt(2, 'USD', '$', 300),
        $debt(2, 'USD', '$', 450),
        $debt(3, 'GBP', '£', 20),
    ]], 60);
    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([7]));
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect());
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(0.0);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);

    $payload = makeStatsService($tallyRepo, $statsRepo)->buildStatsPayload($user, StatsRange::MONTH, CarbonImmutable::parse('2025-05-15 10:00:00'));

    expect($payload['owed_to_me'])->toBe([
        'amount_minor' => 1000,
        'open_balances' => 1,
        'other_currencies' => [
            ['amount_minor' => 750, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ['amount_minor' => 20, 'currency_symbol' => '£', 'currency_code' => 'GBP', 'decimals' => 2],
        ],
    ]);
});

test('the shared percentage is the shared share of the total to one decimal', function (): void {
    $user = User::factory()->make(['id' => 9]);
    $tallyRepo = mockStatsTallyRepo();
    $statsRepo = mockStatsStatsRepo();
    $tallyRepo->shouldReceive('accessibleTallyIds')->once()->andReturn(collect([7]));
    $tallyRepo->shouldReceive('filterOptions')->once()->andReturn(collect([['tally_id' => 7, 'uuid' => 'u-7', 'name' => 'Home', 'is_shared' => true]]));
    $statsRepo->shouldReceive('primaryCurrency')->once()->andReturn(['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 1]);
    $statsRepo->shouldReceive('weeklyHistory')->once()->andReturn([]);
    $statsRepo->shouldReceive('expenseTotal')->times(3)->andReturn(3.0, 0.0, 1.0);
    $statsRepo->shouldReceive('categoriesBreakdown')->once()->andReturn([]);
    $statsRepo->shouldReceive('otherCurrencyExpenses')->once()->andReturn([]);
    $statsRepo->shouldReceive('activity')->once()->andReturn([]);

    $payload = makeStatsService($tallyRepo, $statsRepo)->buildStatsPayload($user, StatsRange::MONTH, CarbonImmutable::parse('2025-05-15 10:00:00'));

    expect($payload['shared_percentage'])->toBe(33.3)
        ->and($payload['tallies'])->toBe([['uuid' => 'u-7', 'name' => 'Home', 'is_shared' => true]]);
});
