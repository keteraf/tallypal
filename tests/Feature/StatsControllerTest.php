<?php

declare(strict_types=1);

use App\Http\Controllers\StatsController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

covers(StatsController::class);

test('guests are redirected to login', function (): void {
    $this->get('/stats')->assertRedirect('/login');
});

test('unverified users are redirected to email verification', function (): void {
    $user = User::factory()->unverified()->create();
    actingAs($user)
        ->get('/stats')
        ->assertRedirect(route('verification.notice'));
});

test('verified users without tallies see an empty payload', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/stats')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Stats')
            ->where('range', 'month')
            ->where('series', [])
            ->where('categories', [])
            ->where('total_out', 0)
            ->where('currency_symbol', '€')
            ->where('currency_code', 'EUR')
            ->where('decimals', 2)
            ->where('daily_average', 0)
            ->where('previous_total_out', 0)
            ->where('previous_daily_average', 0)
            ->where('previous_period', null)
            ->where('delta_percentage', null)
            ->where('shared_total_out', 0)
            ->where('shared_percentage', null)
            ->where('owed_to_me', ['amount_minor' => 0, 'open_balances' => 0, 'other_currencies' => []])
            ->where('activity', [])
            ->where('filter', ['tally_uuid' => null])
            ->where('tallies', [])
        );
});

test('filtering to a tally of another user is forbidden', function (): void {
    $user = User::factory()->create();
    $foreign = Tally::factory()->create();

    actingAs($user)
        ->get("/stats?tally={$foreign->uuid}")
        ->assertForbidden();
});

test('a member who does not own the tally can filter to it', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create();
    $tally->users()->attach($user->id, ['default_share_percentage' => 50]);

    actingAs($user)
        ->get("/stats?tally={$tally->uuid}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('filter', ['tally_uuid' => $tally->uuid])
        );
});

test('the tally filter rejects a value that is not a uuid', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->getJson('/stats?tally=not-a-uuid')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tally' => 'The tally field must be a valid UUID.']);
});

test('the tally filter rejects a uuid on a browser visit with a session error', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/stats?tally=not-a-uuid')
        ->assertSessionHasErrors('tally');
});

test('the tally filter rejects a uuid that matches no tally', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->getJson('/stats?tally=0d3e0000-0000-7000-8000-00000000ffff')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tally' => 'The selected tally is invalid.']);
});

test('the filter options list every tally the user can view by name, even when one is filtered', function (string $query): void {
    $user = User::factory()->create();
    $zoo = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Zoo trip']);
    $household = Tally::factory()->create(['name' => 'Household']);
    $household->users()->attach($user->id, ['default_share_percentage' => 50]);
    Tally::factory()->create(['name' => 'Not mine']);

    actingAs($user)
        ->get('/stats?' . str_replace('{tally}', $zoo->uuid, $query))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies', [
                ['uuid' => $household->uuid, 'name' => 'Household', 'is_shared' => true],
                ['uuid' => $zoo->uuid, 'name' => 'Zoo trip', 'is_shared' => false],
            ])
        );
})->with([
    'unfiltered' => ['range=month'],
    'filtered' => ['tally={tally}'],
]);

test('stats page defaults to the month range', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 40.0, 'transaction_date' => now()->toDateString(), 'title' => 'Groceries']);

    actingAs($user)
        ->get('/stats')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Stats')
            ->where('range', 'month')
            ->has('series', 12)
            ->has('categories')
            ->where('total_out', 40)
        );
});

test('stats page accepts a week range', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 12.0, 'transaction_date' => now()->toDateString()]);

    actingAs($user)
        ->get('/stats?range=week')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Stats')
            ->where('range', 'week')
            ->has('series', 7)
            ->where('total_out', 12)
        );
});

test('stats page accepts a year range', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/stats?range=year')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Stats')
            ->where('range', 'year')
        );
});

test('stats page rejects an invalid range', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/stats?range=decade')
        ->assertSessionHasErrors('range');
});

test('stats page scopes data to the authenticated user', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);

    $myTally = Tally::factory()->create(['owner_id' => $user->id]);
    $theirTally = Tally::factory()->create(['owner_id' => $other->id]);

    Transaction::factory()->forTally($myTally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 5.0, 'transaction_date' => now()->toDateString()]);
    Transaction::factory()->forTally($theirTally)->forParty($other)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 500.0, 'transaction_date' => now()->toDateString()]);

    actingAs($user)
        ->get('/stats')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Stats')
            ->where('total_out', 5)
        );
});

/**
 * October 2026: EUR is the most-used currency (2 expenses, 50), USD has 1 expense (12.50).
 *
 * @return array{user: User, dollar: Currency}
 */
function seedMixedCurrencyStats(): array
{
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $food = Category::factory()->create(['name' => 'Food']);
    $travel = Category::factory()->create(['name' => 'Travel']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 30.0, 'transaction_date' => '2026-10-13', 'category_id' => $food->getKey()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 20.0, 'transaction_date' => '2026-10-02', 'category_id' => $food->getKey()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 12.5, 'transaction_date' => '2026-10-13', 'category_id' => $travel->getKey()]);

    return ['user' => $user, 'dollar' => $dollar];
}

test('stats of a mixed EUR/USD month use only the most-used currency and list the others apart', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    ['user' => $user] = seedMixedCurrencyStats();

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('currency_code', 'EUR')
            ->where('total_out', 50)
            ->where('series.11.amount', 30)
            ->where('categories', [
                ['name' => 'Food', 'amount' => 50, 'percentage' => 100, 'bar_fill' => 100, 'is_other' => false],
            ])
            ->where('other_currencies', [
                ['amount' => 12.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
});

test('stats week and year ranges keep currencies apart too', function (string $range, int $tuesdayBucket, int $totalOut): void {
    $this->travelTo('2026-10-14 12:00:00');
    ['user' => $user] = seedMixedCurrencyStats();

    actingAs($user)
        ->get("/stats?range={$range}")
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('total_out', $totalOut)
            ->where("series.{$tuesdayBucket}.amount", $totalOut)
            ->where('other_currencies', [
                ['amount' => 12.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
})->with([
    'week' => ['week', 1, 30],
    'year' => ['year', 9, 50],
]);

test('stats use the user default currency over the most-used one', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    ['user' => $user, 'dollar' => $dollar] = seedMixedCurrencyStats();
    $user->update(['default_currency_id' => $dollar->currency_id]);

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('currency_code', 'USD')
            ->where('currency_symbol', '$')
            ->where('total_out', 12.5)
            ->where('series.11.amount', 12.5)
            ->where('categories', [
                ['name' => 'Travel', 'amount' => 12.5, 'percentage' => 100, 'bar_fill' => 100, 'is_other' => false],
            ])
            ->where('other_currencies', [
                ['amount' => 50, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
            ])
        );
});

/**
 * Seeds October 2026 expenses, one category per amount, in a single EUR tally.
 *
 * @param  array<string, float>  $amountsByCategory
 */
function seedCategoryExpenses(User $user, array $amountsByCategory): void
{
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    foreach ($amountsByCategory as $name => $amount) {
        Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)->create([
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => '2026-10-13',
            'category_id' => Category::factory()->create(['name' => $name])->getKey(),
        ]);
    }
}

test('stats category bars are relative to the largest category and the rest is one Other row', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    seedCategoryExpenses($user, ['Rent' => 400.0, 'Food' => 200.0, 'Travel' => 100.0, 'Fun' => 50.0, 'Gym' => 25.0, 'Cafe' => 15.0, 'Bus' => 10.0]);

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('categories', [
                ['name' => 'Rent', 'amount' => 400, 'percentage' => 50, 'bar_fill' => 100, 'is_other' => false],
                ['name' => 'Food', 'amount' => 200, 'percentage' => 25, 'bar_fill' => 50, 'is_other' => false],
                ['name' => 'Travel', 'amount' => 100, 'percentage' => 12.5, 'bar_fill' => 25, 'is_other' => false],
                ['name' => 'Fun', 'amount' => 50, 'percentage' => 6.3, 'bar_fill' => 12.5, 'is_other' => false],
                ['name' => 'Gym', 'amount' => 25, 'percentage' => 3.1, 'bar_fill' => 6.25, 'is_other' => false],
                ['name' => 'Other', 'amount' => 25, 'percentage' => 3.1, 'bar_fill' => 6.25, 'is_other' => true],
            ])
        );
});

test('stats Other bar never sets the scale and is capped at 100 when it is the largest amount', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    seedCategoryExpenses($user, [
        'Rent' => 100.0, 'Food' => 90.0, 'Travel' => 80.0, 'Fun' => 70.0, 'Gym' => 60.0,
        'Cafe' => 30.0, 'Bus' => 30.0, 'Tea' => 30.0, 'Pub' => 30.0, 'Zoo' => 30.0, 'Spa' => 30.0,
    ]);

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('categories', 6)
            ->where('categories.0', ['name' => 'Rent', 'amount' => 100, 'percentage' => 17.2, 'bar_fill' => 100, 'is_other' => false])
            ->where('categories.4.bar_fill', 60)
            ->where('categories.5', ['name' => 'Other', 'amount' => 180, 'percentage' => 31, 'bar_fill' => 100, 'is_other' => true])
        );
});

test('stats without a rest of categories has no Other row', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    seedCategoryExpenses($user, ['Rent' => 100.0, 'Food' => 50.0, 'Travel' => 40.0, 'Fun' => 30.0, 'Gym' => 20.0]);

    actingAs($user)
        ->get('/stats?range=month')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('categories', 5)
            ->where('categories.4.is_other', false)
        );
});
