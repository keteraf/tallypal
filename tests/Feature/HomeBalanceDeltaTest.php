<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Services\HomeService;
use Carbon\CarbonImmutable;

use function Pest\Laravel\actingAs;

covers(HomeService::class);

/**
 * Seed one EUR income/expense pair in the month of the given date, so that the month's net is
 * `$income - $expense`.
 */
function seedNet(Tally $tally, User $user, Currency $currency, string $date, float $income, float $expense): void
{
    foreach (['income' => $income, 'expense' => $expense] as $type => $amount) {
        if ($amount === 0.0) {
            continue;
        }

        Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
            ->create(['type' => $type, 'amount' => $amount, 'transaction_date' => $date]);
    }
}

/**
 * @return array{user: User, euro: Currency, dollar: Currency, tally: Tally}
 */
function deltaFixture(): array
{
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    return ['user' => $user, 'euro' => $euro, 'dollar' => $dollar, 'tally' => $tally];
}

function homeBalance(User $user, string $now): array
{
    return resolve(HomeService::class)->buildHomePayload($user, CarbonImmutable::parse($now))['balance'];
}

test('a better month than the previous one gives a positive delta rounded to one decimal', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 200.0, 100.0); // previous net 100
    seedNet($tally, $user, $euro, '2026-10-05', 212.34, 100.0); // current net 112.34

    $balance = homeBalance($user, '2026-10-14 12:00:00');

    expect($balance['previous_net'])->toBe(100.0)
        ->and($balance['previous_period'])->toBe(['start' => '2026-09-01', 'end' => '2026-09-14'])
        ->and($balance['delta_percentage'])->toBe(12.3);
});

test('a worse month than the previous one gives a negative delta', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 300.0, 100.0); // previous net 200
    seedNet($tally, $user, $euro, '2026-10-05', 150.0, 50.0); // current net 100

    expect(homeBalance($user, '2026-10-14 12:00:00')['delta_percentage'])->toBe(-50.0);
});

test('improving from a negative balance to a positive one gives a positive delta', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 0.0, 200.0); // previous net -200
    seedNet($tally, $user, $euro, '2026-10-05', 100.0, 0.0); // current net 100

    $balance = homeBalance($user, '2026-10-14 12:00:00');

    expect($balance['previous_net'])->toBe(-200.0)
        ->and($balance['delta_percentage'])->toBe(150.0);
});

test('a smaller loss than the previous month is an improvement', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 0.0, 200.0); // previous net -200
    seedNet($tally, $user, $euro, '2026-10-05', 0.0, 100.0); // current net -100

    expect(homeBalance($user, '2026-10-14 12:00:00')['delta_percentage'])->toBe(50.0);
});

test('the delta is null when the previous month nets to zero', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 100.0, 100.0);
    seedNet($tally, $user, $euro, '2026-10-05', 80.0, 0.0);

    $balance = homeBalance($user, '2026-10-14 12:00:00');

    expect($balance['previous_net'])->toBe(0.0)
        ->and($balance['delta_percentage'])->toBeNull();
});

test('the delta is null when there was no activity in the previous month', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-10-05', 80.0, 0.0);

    expect(homeBalance($user, '2026-10-14 12:00:00')['delta_percentage'])->toBeNull();
});

test('mid-month, the previous period is the same span of days of the previous month', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-08-31', 999.0, 0.0); // two months back: ignored
    seedNet($tally, $user, $euro, '2026-09-01', 60.0, 0.0); // first day of the span
    seedNet($tally, $user, $euro, '2026-09-13', 40.0, 0.0); // same day-of-month as today
    seedNet($tally, $user, $euro, '2026-10-01', 150.0, 0.0);

    $balance = homeBalance($user, '2026-10-13 12:00:00');

    expect($balance['previous_net'])->toBe(100.0)
        ->and($balance['net'])->toBe(150.0)
        ->and($balance['delta_percentage'])->toBe(50.0);
});

test('previous-month transactions dated after today\'s day of the month are excluded', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-13', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-09-14', 900.0, 0.0); // after the 13th: excluded
    seedNet($tally, $user, $euro, '2026-09-30', 900.0, 0.0); // after the 13th: excluded
    seedNet($tally, $user, $euro, '2026-10-05', 110.0, 0.0);

    $balance = homeBalance($user, '2026-10-13 12:00:00');

    expect($balance['previous_net'])->toBe(100.0)
        ->and($balance['delta_percentage'])->toBe(10.0);
});

test('on the first of the month the previous period is only the first of the previous month', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-01', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-09-02', 900.0, 0.0);
    seedNet($tally, $user, $euro, '2026-10-01', 150.0, 0.0);

    $balance = homeBalance($user, '2026-10-01 00:00:00');

    expect($balance['previous_net'])->toBe(100.0)
        ->and($balance['delta_percentage'])->toBe(50.0);
});

test('on March 31 the span is clamped to the end of February', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-01-31', 999.0, 0.0); // January: ignored
    seedNet($tally, $user, $euro, '2026-02-01', 60.0, 0.0);
    seedNet($tally, $user, $euro, '2026-02-28', 40.0, 0.0); // last day of February 2026
    seedNet($tally, $user, $euro, '2026-03-01', 150.0, 0.0);

    $balance = homeBalance($user, '2026-03-31 12:00:00');

    expect($balance['previous_period'])->toBe(['start' => '2026-02-01', 'end' => '2026-02-28']);

    expect($balance['previous_net'])->toBe(100.0)
        ->and($balance['delta_percentage'])->toBe(50.0);
});

test('on March 31 of a leap year the span runs to February 29', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2028-02-28', 60.0, 0.0);
    seedNet($tally, $user, $euro, '2028-02-29', 40.0, 0.0); // leap day, last day of the span
    seedNet($tally, $user, $euro, '2028-03-01', 150.0, 0.0);

    $balance = homeBalance($user, '2028-03-31 12:00:00');

    expect($balance['previous_net'])->toBe(100.0)
        ->and($balance['delta_percentage'])->toBe(50.0);
});

test('on a day the previous month also has, the span is not clamped', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-02-28', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-03-01', 100.0, 0.0);

    expect(homeBalance($user, '2026-03-28 12:00:00')['previous_net'])->toBe(100.0);
});

test('the previous net is cached per day, since the span grows daily', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-01', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-09-02', 50.0, 0.0);
    seedNet($tally, $user, $euro, '2026-10-01', 100.0, 0.0);

    expect(homeBalance($user, '2026-10-01 12:00:00')['previous_net'])->toBe(100.0)
        ->and(homeBalance($user, '2026-10-02 12:00:00')['previous_net'])->toBe(150.0);
});

test('january compares with december of the previous year', function (): void {
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2025-12-10', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-01-03', 50.0, 0.0);

    expect(homeBalance($user, '2026-01-15 09:00:00')['delta_percentage'])->toBe(-50.0);
});

test('other currencies never enter the delta', function (): void {
    ['user' => $user, 'euro' => $euro, 'dollar' => $dollar, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-09-11', 50.0, 0.0);
    seedNet($tally, $user, $euro, '2026-10-05', 150.0, 0.0);
    seedNet($tally, $user, $dollar, '2026-09-12', 9000.0, 0.0);
    seedNet($tally, $user, $dollar, '2026-10-06', 1.0, 0.0);

    $balance = homeBalance($user, '2026-10-14 12:00:00');

    expect($balance['currency_code'])->toBe('EUR')
        ->and($balance['previous_net'])->toBe(150.0)
        ->and($balance['delta_percentage'])->toBe(0.0);
});

test('a previous month with activity only in another currency gives no delta', function (): void {
    ['user' => $user, 'euro' => $euro, 'dollar' => $dollar, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-10-05', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-10-06', 10.0, 0.0);
    seedNet($tally, $user, $dollar, '2026-09-12', 500.0, 0.0);

    $balance = homeBalance($user, '2026-10-14 12:00:00');

    expect($balance['currency_code'])->toBe('EUR')
        ->and($balance['previous_net'])->toBe(0.0)
        ->and($balance['delta_percentage'])->toBeNull();
});

test('the delta follows the user default currency', function (): void {
    ['user' => $user, 'euro' => $euro, 'dollar' => $dollar, 'tally' => $tally] = deltaFixture();
    $user->update(['default_currency_id' => $dollar->getKey()]);
    seedNet($tally, $user, $euro, '2026-09-10', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-09-11', 100.0, 0.0);
    seedNet($tally, $user, $dollar, '2026-09-12', 100.0, 0.0);
    seedNet($tally, $user, $dollar, '2026-10-05', 125.0, 0.0);

    $balance = homeBalance($user->refresh(), '2026-10-14 12:00:00');

    expect($balance['currency_code'])->toBe('USD')
        ->and($balance['delta_percentage'])->toBe(25.0);
});

test('the home page sends the delta props', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    ['user' => $user, 'euro' => $euro, 'tally' => $tally] = deltaFixture();
    seedNet($tally, $user, $euro, '2026-09-10', 100.0, 0.0);
    seedNet($tally, $user, $euro, '2026-10-05', 110.0, 0.0);

    actingAs($user)->get('/home')->assertInertia(fn ($page) => $page
        ->where('balance.previous_net', 100)
        ->where('balance.delta_percentage', 10)
    );
});

test('a user without tallies gets a null delta', function (): void {
    $balance = homeBalance(User::factory()->create(), '2026-10-14 12:00:00');

    expect($balance['previous_net'])->toBe(0.0)
        ->and($balance['delta_percentage'])->toBeNull();
});
