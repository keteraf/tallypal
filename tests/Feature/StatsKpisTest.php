<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PeriodComparison;
use App\Services\StatsService;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

covers(StatsService::class, PeriodComparison::class);

beforeEach(function (): void {
    $this->travelTo('2026-10-14 12:00:00'); // a Wednesday
    $this->euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $this->dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
});

function kpiTx(Tally $tally, User $party, Currency $currency, float $amount, string $date, string $type = 'expense', string $title = 'Expense'): Transaction
{
    return Transaction::factory()->forTally($tally)->forParty($party)->withCurrency($currency)
        ->create(['type' => $type, 'amount' => $amount, 'transaction_date' => $date, 'title' => $title]);
}

/**
 * A tally owned by `$owner` and split evenly with `$member`.
 *
 * @param  array<string, mixed>  $attributes
 */
function kpiSharedTally(User $owner, User $member, array $attributes = []): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $owner->id, ...$attributes]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    return $tally;
}

/**
 * A solo tally whose owner also has their own `tally_user` row (still one member).
 */
function kpiSoloTally(User $owner, string $name = 'Solo'): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'name' => $name]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100]);

    return $tally;
}

/**
 * The user defaults to EUR. Household (shared, EUR), Trip (shared, default USD, EUR + USD) and
 * Solo (EUR). The co-member owes the user € 71.00 in Household, $ 597.50 and € 6.00 in Trip.
 *
 * @return array{user: User, member: User, household: Tally, trip: Tally, solo: Tally}
 */
function kpiFixture(Currency $euro, Currency $dollar): array
{
    $user = User::factory()->create(['default_currency_id' => $euro->currency_id]);
    $member = User::factory()->create();
    $household = kpiSharedTally($user, $member, ['name' => 'Household']);
    $trip = kpiSharedTally($user, $member, ['name' => 'Trip', 'default_currency_id' => $dollar->currency_id]);
    $solo = kpiSoloTally($user);

    kpiTx($household, $user, $euro, 100.00, '2026-10-02', title: 'Groceries');
    kpiTx($household, $user, $euro, 42.00, '2026-09-10');
    kpiTx($trip, $user, $dollar, 70.00, '2026-10-03', title: 'Hotel');
    kpiTx($trip, $user, $euro, 12.00, '2026-10-05', title: 'Museum');
    kpiTx($trip, $user, $dollar, 70.00, '2026-10-10', title: 'Car');
    kpiTx($trip, $user, $dollar, 56.00, '2026-09-05');
    kpiTx($trip, $user, $dollar, 999.00, '2026-09-20');
    kpiTx($solo, $user, $euro, 28.00, '2026-10-08', title: 'Books');

    return ['user' => $user, 'member' => $member, 'household' => $household, 'trip' => $trip, 'solo' => $solo];
}

test('filtering to a USD tally gives its USD KPIs and lists its EUR apart', function (): void {
    ['user' => $user, 'trip' => $trip] = kpiFixture($this->euro, $this->dollar);

    actingAs($user)
        ->get("/stats?range=month&tally={$trip->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('filter', ['tally_uuid' => $trip->uuid])
            ->where('currency_code', 'USD')
            ->where('currency_symbol', '$')
            ->where('total_out', 140)
            ->where('daily_average', 10)
            ->where('previous_total_out', 56)
            ->where('previous_daily_average', 4)
            ->where('previous_period', ['start' => '2026-09-01', 'end' => '2026-09-14'])
            ->where('delta_percentage', 150)
            ->where('shared_total_out', 140)
            ->where('shared_percentage', 100)
            ->where('owed_to_me', [
                'amount_minor' => 59750,
                'open_balances' => 1,
                'other_currencies' => [['amount_minor' => 600, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2]],
            ])
            ->where('other_currencies', [
                ['amount' => 12, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
            ])
        );
});

test('without a filter every KPI is in the primary currency while activity keeps each row in its own', function (): void {
    ['user' => $user, 'solo' => $solo] = kpiFixture($this->euro, $this->dollar);

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('filter', ['tally_uuid' => null])
            ->where('currency_code', 'EUR')
            ->where('total_out', 140)
            ->where('daily_average', 10)
            ->where('previous_total_out', 42)
            ->where('previous_daily_average', 3)
            ->where('delta_percentage', 233.3)
            ->where('shared_total_out', 112)
            ->where('shared_percentage', 80)
            ->where('owed_to_me', [
                'amount_minor' => 7700,
                'open_balances' => 2,
                'other_currencies' => [['amount_minor' => 59750, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2]],
            ])
            ->where('other_currencies', [
                ['amount' => 140, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
            ->where('activity', fn (Collection $activity): bool => $activity->map(fn (array $row): array => [
                $row['title'], $row['transaction_date'], $row['amount'], $row['currency_symbol'], $row['is_shared'],
            ])->all() === [
                ['Car', '2026-10-10', 70, '$', true],
                ['Books', '2026-10-08', 28, '€', false],
                ['Museum', '2026-10-05', 12, '€', true],
                ['Hotel', '2026-10-03', 70, '$', true],
                ['Groceries', '2026-10-02', 100, '€', true],
            ])
            ->where('activity.1.tally_uuid', $solo->uuid)
            ->where('activity.1.tally_name', 'Solo')
        );
});

test('a filtered tally uses its default currency over its most-used one', function (): void {
    $user = User::factory()->create(['default_currency_id' => $this->dollar->currency_id]);
    $tally = kpiSoloTally($user);
    $tally->update(['default_currency_id' => $this->euro->currency_id]);
    kpiTx($tally, $user, $this->dollar, 5.00, '2026-10-02');
    kpiTx($tally, $user, $this->dollar, 5.00, '2026-10-03');
    kpiTx($tally, $user, $this->euro, 14.00, '2026-10-04');

    actingAs($user)
        ->get("/stats?tally={$tally->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('currency_code', 'EUR')
            ->where('total_out', 14)
            ->where('other_currencies', [
                ['amount' => 10, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
});

test('a filtered tally without a default uses its most-used currency, not the user default', function (): void {
    $user = User::factory()->create(['default_currency_id' => $this->euro->currency_id]);
    $tally = kpiSoloTally($user);
    kpiTx($tally, $user, $this->dollar, 5.00, '2026-10-02');
    kpiTx($tally, $user, $this->dollar, 9.00, '2026-10-03');
    kpiTx($tally, $user, $this->euro, 14.00, '2026-10-04');

    actingAs($user)
        ->get("/stats?tally={$tally->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('currency_code', 'USD')
            ->where('total_out', 14)
        );
});

test('a filtered tally with no currency to prefill falls back to the user primary currency', function (): void {
    config(['app.default_currency' => 'XXX']);
    $user = User::factory()->create();
    $empty = kpiSoloTally($user, 'Empty');
    kpiTx(kpiSoloTally($user, 'Trip'), $user, $this->dollar, 20.00, '2026-10-02');

    actingAs($user)
        ->get("/stats?tally={$empty->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('currency_code', 'USD')
            ->where('total_out', 0)
            ->where('shared_percentage', null)
            ->where('delta_percentage', null)
        );
});

test('each range compares its period to date with the same span of the previous period', function (string $range, array $expenses, array $expected): void {
    $user = User::factory()->create();
    $tally = kpiSoloTally($user);

    foreach ($expenses as [$date, $amount]) {
        kpiTx($tally, $user, $this->euro, $amount, $date);
    }

    actingAs($user)
        ->get("/stats?range={$range}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('total_out', $expected['total_out'])
            ->where('daily_average', $expected['daily_average'])
            ->where('previous_total_out', $expected['previous_total_out'])
            ->where('previous_daily_average', $expected['previous_daily_average'])
            ->where('previous_period', $expected['previous_period'])
            ->where('delta_percentage', $expected['delta_percentage'])
        );
})->with([
    'week: Mon–Wed vs last Mon–Wed, last Thursday excluded, averages over 3 days' => [
        'week',
        [['2026-10-13', 100.00], ['2026-10-06', 50.00], ['2026-10-08', 500.00]],
        ['total_out' => 100, 'daily_average' => 33.33, 'previous_total_out' => 50, 'previous_daily_average' => 16.67,
            'previous_period' => ['start' => '2026-10-05', 'end' => '2026-10-07'], 'delta_percentage' => 100],
    ],
    'month: Oct 1–14 vs Sep 1–14, Sep 15 excluded' => [
        'month',
        [['2026-10-02', 140.00], ['2026-09-14', 70.00], ['2026-09-15', 500.00]],
        ['total_out' => 140, 'daily_average' => 10, 'previous_total_out' => 70, 'previous_daily_average' => 5,
            'previous_period' => ['start' => '2026-09-01', 'end' => '2026-09-14'], 'delta_percentage' => 100],
    ],
    'year: Jan 1–Oct 14 vs the same dates last year, averages over 287 days' => [
        'year',
        [['2026-03-01', 2870.00], ['2025-10-14', 1435.00], ['2025-10-15', 500.00]],
        ['total_out' => 2870, 'daily_average' => 10, 'previous_total_out' => 1435, 'previous_daily_average' => 5,
            'previous_period' => ['start' => '2025-01-01', 'end' => '2025-10-14'], 'delta_percentage' => 100],
    ],
]);

test('on March 31 the month compares daily averages over 31 days and February 1–28', function (): void {
    $this->travelTo('2026-03-31 12:00:00');
    $user = User::factory()->create();
    $tally = kpiSoloTally($user);
    kpiTx($tally, $user, $this->euro, 310.00, '2026-03-15');
    kpiTx($tally, $user, $this->euro, 280.00, '2026-02-10');

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('previous_period', ['start' => '2026-02-01', 'end' => '2026-02-28'])
            ->where('total_out', 310)
            ->where('previous_total_out', 280)
            ->where('daily_average', 10)
            ->where('previous_daily_average', 10)
            ->where('delta_percentage', 0) // the totals would give +10.7%
        );
});

test('on February 29 the year compares with January 1 – February 28 of the previous year', function (): void {
    $this->travelTo('2028-02-29 12:00:00');
    $user = User::factory()->create();
    $tally = kpiSoloTally($user);
    kpiTx($tally, $user, $this->euro, 600.00, '2028-02-29');
    kpiTx($tally, $user, $this->euro, 295.00, '2027-02-28');
    kpiTx($tally, $user, $this->euro, 500.00, '2027-03-01');

    actingAs($user)
        ->get('/stats?range=year')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('previous_period', ['start' => '2027-01-01', 'end' => '2027-02-28'])
            ->where('daily_average', 10)
            ->where('previous_total_out', 295)
            ->where('previous_daily_average', 5)
            ->where('delta_percentage', 100)
        );
});

test('on the 1st of the month the daily average is the day total', function (): void {
    $this->travelTo('2026-10-01 09:00:00');
    $user = User::factory()->create();
    kpiTx(kpiSoloTally($user), $user, $this->euro, 25.00, '2026-10-01');

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('daily_average', 25)
            ->where('previous_period', ['start' => '2026-09-01', 'end' => '2026-09-01'])
        );
});

test('the delta is null when the previous period has no expenses', function (): void {
    $user = User::factory()->create();
    kpiTx(kpiSoloTally($user), $user, $this->euro, 30.00, '2026-10-02');

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('previous_total_out', 0)
            ->where('previous_daily_average', 0)
            ->where('delta_percentage', null)
        );
});

test('the shared portion counts only multi-member tallies, in the KPI currency', function (): void {
    $user = User::factory()->create();
    $member = User::factory()->create();
    $shared = kpiSharedTally($user, $member);
    kpiTx($shared, $user, $this->euro, 30.00, '2026-10-02');
    kpiTx($shared, $user, $this->euro, 30.00, '2026-10-03');
    kpiTx($shared, $user, $this->dollar, 500.00, '2026-10-04');
    kpiTx(kpiSoloTally($user), $user, $this->euro, 140.00, '2026-10-05');

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('currency_code', 'EUR')
            ->where('total_out', 200)
            ->where('shared_total_out', 60)
            ->where('shared_percentage', 30)
        );
});

test('the shared portion is null when nothing was spent in the period', function (): void {
    $user = User::factory()->create();
    $shared = kpiSharedTally($user, User::factory()->create());
    kpiTx($shared, $user, $this->euro, 30.00, '2026-09-02');
    kpiTx($shared, $user, $this->euro, 80.00, '2026-10-02', 'income');

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('total_out', 0)
            ->where('shared_total_out', 0)
            ->where('shared_percentage', null)
            ->where('delta_percentage', -100)
        );
});

test('owed to you counts only debts owed to the user in the KPI currency, and only the filtered tally', function (): void {
    $user = User::factory()->create();
    $member = User::factory()->create();
    $owedToMe = kpiSharedTally($user, $member, ['name' => 'Owed to me']);
    $iOwe = kpiSharedTally($user, $member, ['name' => 'I owe']);
    $inDollars = kpiSharedTally($user, $member, ['name' => 'In dollars']);
    kpiTx($owedToMe, $user, $this->euro, 100.00, '2026-10-02');
    kpiTx($owedToMe, $user, $this->euro, 40.00, '2026-10-03');
    kpiTx($iOwe, $member, $this->euro, 60.00, '2026-10-02');
    kpiTx($inDollars, $user, $this->dollar, 20.00, '2026-10-02');

    actingAs($user)->get('/stats')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me', [
                'amount_minor' => 7000,
                'open_balances' => 1,
                'other_currencies' => [['amount_minor' => 1000, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2]],
            ]));
    actingAs($user)->get("/stats?tally={$iOwe->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me', ['amount_minor' => 0, 'open_balances' => 0, 'other_currencies' => []]));
    actingAs($user)->get("/stats?tally={$inDollars->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me', ['amount_minor' => 1000, 'open_balances' => 1, 'other_currencies' => []]));
});

test('owed to you lists the other currencies owed apart in minor units, never added to the KPI currency', function (): void {
    $yen = Currency::factory()->create(['decimals' => 0, 'symbol' => '¥', 'code' => 'JPY']);
    $user = User::factory()->create(['default_currency_id' => $this->euro->currency_id]);
    $member = User::factory()->create();
    $household = kpiSharedTally($user, $member, ['name' => 'Household']);
    $trip = kpiSharedTally($user, $member, ['name' => 'Weekend Trip']);
    kpiTx($household, $user, $this->euro, 50.00, '2026-10-02');
    kpiTx($trip, $user, $this->dollar, 211.70, '2026-10-03');
    kpiTx($trip, $user, $yen, 3000.00, '2026-10-04');
    kpiTx($trip, $user, $this->dollar, 10.00, '2026-10-05');

    actingAs($user)->get('/stats')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me.amount_minor', 2500)
            ->where('owed_to_me.open_balances', 1)
            ->where('owed_to_me.other_currencies', [
                ['amount_minor' => 11085, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
                ['amount_minor' => 1500, 'currency_symbol' => '¥', 'currency_code' => 'JPY', 'decimals' => 0],
            ]));
    actingAs($user)->get("/stats?tally={$household->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me.other_currencies', []));
});

test('owed to you shows a zero KPI amount with the other currency when only that one is owed', function (): void {
    $user = User::factory()->create(['default_currency_id' => $this->euro->currency_id]);
    $member = User::factory()->create();
    $trip = kpiSharedTally($user, $member, ['name' => 'Weekend Trip']);
    kpiTx($trip, $user, $this->dollar, 211.70, '2026-10-03');

    actingAs($user)->get('/stats')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me', [
                'amount_minor' => 0,
                'open_balances' => 0,
                'other_currencies' => [['amount_minor' => 10585, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2]],
            ]));
});

test('activity lists the latest 20 transactions of the range, newest first', function (): void {
    $user = User::factory()->create();
    $tally = kpiSoloTally($user);
    $days = collect(CarbonPeriod::create('2026-09-20', '2026-10-14'))->map(fn ($day): string => $day->toDateString());

    foreach ($days as $day) {
        kpiTx($tally, $user, $this->euro, 1.00, $day, title: $day);
    }

    kpiTx($tally, $user, $this->euro, 1.00, '2026-10-20', title: 'future');
    kpiTx($tally, $user, $this->euro, 1.00, '2025-12-31', title: 'last year');
    $stranger = User::factory()->create();
    kpiTx(kpiSoloTally($stranger), $stranger, $this->euro, 1.00, '2026-10-14', title: 'foreign');

    actingAs($user)
        ->get('/stats?range=year')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('activity', fn (Collection $activity): bool => $activity->pluck('title')->all() === $days->reverse()->take(20)->values()->all())
        );
});

test('activity breaks a same-day tie by the most recently entered transaction', function (): void {
    $user = User::factory()->create();
    $tally = kpiSoloTally($user);
    kpiTx($tally, $user, $this->euro, 1.00, '2026-10-14', title: 'Earlier')->forceFill(['created_at' => '2026-10-14 09:00:00'])->save();
    kpiTx($tally, $user, $this->euro, 1.00, '2026-10-14', title: 'Later')->forceFill(['created_at' => '2026-10-14 11:00:00'])->save();

    actingAs($user)
        ->get('/stats?range=week')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('activity.0.title', 'Later')
            ->where('activity.1.title', 'Earlier')
        );
});

test('activity rows carry the transaction, its tally and its own currency', function (): void {
    $user = User::factory()->create();
    $trip = kpiSharedTally($user, User::factory()->create(), ['name' => 'Trip']);
    $food = Category::factory()->create(['name' => 'Food', 'slug' => 'food', 'color' => 'c4']);
    $transaction = Transaction::factory()->forTally($trip)->forParty($user)->withCurrency($this->dollar)->withCategory($food)
        ->create(['type' => 'expense', 'amount' => 12.50, 'transaction_date' => '2026-10-13', 'title' => 'Lunch']);

    actingAs($user)
        ->get('/stats')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('activity', [[
                'uuid' => $transaction->uuid,
                'title' => 'Lunch',
                'type' => 'expense',
                'amount' => 12.5,
                'transaction_date' => '2026-10-13',
                'category_id' => $food->getKey(),
                'category_name' => 'Food',
                'category_slug' => 'food',
                'category_color' => 'c4',
                'tally_uuid' => $trip->uuid,
                'tally_name' => 'Trip',
                'is_shared' => true,
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'decimals' => 2,
            ]])
        );
});

test('Stats runs a constant number of queries however many tallies and transactions there are', function (string $query): void {
    $user = User::factory()->create();
    $filtered = kpiSharedTally($user, User::factory()->create());
    $build = function (int $tallies, int $transactions) use ($user, $filtered): void {
        foreach (range(1, $transactions) as $day) {
            kpiTx($filtered, $user, $day % 2 === 0 ? $this->euro : $this->dollar, 10.00, sprintf('2026-10-%02d', ($day % 14) + 1));
        }

        foreach (range(1, $tallies) as $ignored) {
            $tally = kpiSharedTally($user, User::factory()->create());
            kpiTx($tally, $user, $this->euro, 10.00, '2026-10-02');
            kpiTx($tally, $user, $this->dollar, 10.00, '2026-10-03');
            kpiTx($tally, $user, $this->euro, 10.00, '2026-09-03');
        }
    };
    $url = '/stats?' . str_replace('{tally}', $filtered->uuid, $query);
    $countQueries = function () use ($user, $url): int {
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($user)->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $build(1, 3);
    actingAs($user)->get($url);
    $few = $countQueries();
    $build(6, 25);
    $many = $countQueries();

    expect($many)->toBe($few);
})->with([
    'all tallies' => ['range=month'],
    'one tally' => ['range=month&tally={tally}'],
]);

test('a new transaction shows in the next Stats request', function (): void {
    $user = User::factory()->create();
    $member = User::factory()->create();
    $tally = kpiSharedTally($user, $member);
    kpiTx($tally, $user, $this->euro, 20.00, '2026-10-02');
    actingAs($user)->get('/stats')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('owed_to_me', ['amount_minor' => 1000, 'open_balances' => 1, 'other_currencies' => []]));

    kpiTx($tally, $user, $this->euro, 30.00, '2026-10-03');

    actingAs($user)->get('/stats')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('total_out', 50)
            ->where('owed_to_me', ['amount_minor' => 2500, 'open_balances' => 1, 'other_currencies' => []]));
});

test('a filtered Stats visit on a cold cache leaves Home with the balances of every tally', function (): void {
    ['user' => $user, 'trip' => $trip] = kpiFixture($this->euro, $this->dollar);
    Cache::flush();

    actingAs($user)->get("/stats?tally={$trip->uuid}")->assertOk();

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('tallies', 3)
            ->where('tallies', fn (Collection $tallies): bool => $tallies->every(fn (array $tally): bool => $tally['my_balance'] !== []))
        );
});
