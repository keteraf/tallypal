<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Http\Controllers\TallyController;
use App\Http\Controllers\TransactionController;
use App\Http\Requests\Tally\StoreTallyRequest;
use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

covers(Currency::class, Tally::class, TallyController::class, TransactionController::class, StoreTallyRequest::class, UpdateTallyRequest::class);

/**
 * B-11: the tally default currency only prefills Add transaction; it never restricts or converts.
 */
function currencyByCode(string $code): Currency
{
    return Currency::query()->where('code', $code)->firstOrFail();
}

function tallyFor(User $user, array $attributes = []): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $user->id, ...$attributes]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    return $tally;
}

function prefilledCurrencyUuid(User $user, Tally $tally): ?string
{
    return test()->actingAs($user)
        ->getJson(route('tallies.transactions.form-data', $tally))
        ->assertOk()
        ->json('default_currency_uuid');
}

// ─── prefill order ─────────────────────────────────────────────────────────────

it('prefills the tally default currency first, over usage and the user default', function (): void {
    $user = User::factory()->create(['default_currency_id' => currencyByCode('GBP')->currency_id]);
    $tally = tallyFor($user, ['default_currency_id' => currencyByCode('USD')->currency_id]);
    Transaction::factory()->count(3)->forTally($tally)->forParty($user)->withCurrency(currencyByCode('EUR'))->create();

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('USD')->uuid);
});

it('prefills the most-used currency of the tally when it has no default', function (): void {
    $user = User::factory()->create(['default_currency_id' => currencyByCode('GBP')->currency_id]);
    $tally = tallyFor($user);
    Transaction::factory()->count(2)->forTally($tally)->forParty($user)->withCurrency(currencyByCode('USD'))->create();
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency(currencyByCode('EUR'))->create();

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('USD')->uuid);
});

it('breaks a most-used tie with the lowest currency id', function (): void {
    $user = User::factory()->create();
    $tally = tallyFor($user);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency(currencyByCode('USD'))->create();
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency(currencyByCode('EUR'))->create();

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('EUR')->uuid);
});

it('ignores the usage of other tallies when picking the most-used currency', function (): void {
    $user = User::factory()->create();
    $tally = tallyFor($user);
    $other = tallyFor($user);
    Transaction::factory()->count(3)->forTally($other)->forParty($user)->withCurrency(currencyByCode('USD'))->create();
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency(currencyByCode('GBP'))->create();

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('GBP')->uuid);
});

it('prefills the user default currency when the tally has no default and no transactions', function (): void {
    $user = User::factory()->create(['default_currency_id' => currencyByCode('GBP')->currency_id]);
    $tally = tallyFor($user);

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('GBP')->uuid);
});

it('prefills the app default currency when no other rung applies', function (): void {
    $user = User::factory()->create();
    $tally = tallyFor($user);

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('EUR')->uuid);
});

it('prefills a new user\'s first transaction on a new tally with EUR', function (): void {
    $this->post('/register', [
        'name' => 'Newcomer',
        'email' => 'newcomer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();
    $user = User::query()->where('email', 'newcomer@example.com')->firstOrFail();
    $user->markEmailAsVerified();

    $this->actingAs($user->refresh())->post(route('tallies.store'), ['name' => 'First'])->assertSessionHasNoErrors();
    $tally = Tally::query()->where('name', 'First')->firstOrFail();

    expect(prefilledCurrencyUuid($user, $tally))->toBe(currencyByCode('EUR')->uuid);
});

it('prefills nothing when no rung applies and the app default is not a known currency', function (): void {
    config(['app.default_currency' => 'ZZZ']);
    $user = User::factory()->create();
    $tally = tallyFor($user);

    expect(prefilledCurrencyUuid($user, $tally))->toBeNull();
});

it('applies the same order on the full-page create route', function (): void {
    $user = User::factory()->create(['default_currency_id' => currencyByCode('GBP')->currency_id]);
    $tally = tallyFor($user, ['default_currency_id' => currencyByCode('USD')->currency_id]);

    $this->actingAs($user)
        ->get(route('tallies.transactions.create', $tally))
        ->assertInertia(fn (Assert $page) => $page->where('default_currency_uuid', currencyByCode('USD')->uuid));
});

// ─── Household: never restricts or converts ───────────────────────────────────

it('keeps accepting both currencies on a tally with a default, without converting', function (): void {
    $user = Sanctum::actingAs(User::factory()->create());
    $tally = tallyFor($user, ['default_currency_id' => currencyByCode('EUR')->currency_id]);
    $category = Category::query()->firstOrFail();

    foreach (['EUR', 'USD'] as $code) {
        $this->postJson(route('api.tallies.transactions.store', $tally), [
            'party_id' => $user->id,
            'currency_id' => currencyByCode($code)->currency_id,
            'category_id' => $category->getKey(),
            'type' => TransactionType::EXPENSE->value,
            'title' => "Household {$code}",
            'amount' => 100,
            'transaction_date' => now()->toDateString(),
        ])->assertSuccessful();
    }

    $stored = Transaction::query()->where('tally_id', $tally->getKey())->orderBy('transaction_id')->get();

    expect($stored->pluck('currency_id')->all())->toBe([currencyByCode('EUR')->currency_id, currencyByCode('USD')->currency_id])
        ->and($stored->map(fn (Transaction $t): float => (float) $t->amount)->all())->toBe([100.0, 100.0])
        ->and($tally->refresh()->default_currency_id)->toBe(currencyByCode('EUR')->currency_id);
});

// ─── New / Edit tally ──────────────────────────────────────────────────────────

it('stores the default currency from the new tally request', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tallies.store'), ['name' => 'Trip', 'default_currency_uuid' => currencyByCode('USD')->uuid])
        ->assertSessionHasNoErrors();

    expect(Tally::query()->where('name', 'Trip')->firstOrFail()->default_currency_id)->toBe(currencyByCode('USD')->currency_id);
});

it('leaves the default currency null when the new tally request omits or clears it', function (?array $extra): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('tallies.store'), ['name' => 'Plain', ...$extra])->assertSessionHasNoErrors();

    expect(Tally::query()->where('name', 'Plain')->firstOrFail()->default_currency_id)->toBeNull();
})->with([
    'omitted' => [[]],
    'cleared' => [['default_currency_uuid' => null]],
]);

it('sets, changes and clears the default currency from the edit tally request', function (): void {
    $user = User::factory()->create();
    $tally = tallyFor($user);

    $this->actingAs($user)
        ->patch(route('tallies.update', $tally), ['name' => $tally->name, 'default_currency_uuid' => currencyByCode('GBP')->uuid])
        ->assertSessionHasNoErrors();
    expect($tally->refresh()->default_currency_id)->toBe(currencyByCode('GBP')->currency_id);

    $this->patch(route('tallies.update', $tally), ['name' => $tally->name, 'default_currency_uuid' => currencyByCode('EUR')->uuid]);
    expect($tally->refresh()->default_currency_id)->toBe(currencyByCode('EUR')->currency_id);

    $this->patch(route('tallies.update', $tally), ['name' => $tally->name, 'default_currency_uuid' => null]);
    expect($tally->refresh()->default_currency_id)->toBeNull();
});

it('keeps the stored default currency when the edit request does not send it', function (): void {
    $user = User::factory()->create();
    $tally = tallyFor($user, ['default_currency_id' => currencyByCode('USD')->currency_id]);

    $this->actingAs($user)->patch(route('tallies.update', $tally), ['name' => 'Renamed'])->assertSessionHasNoErrors();

    expect($tally->refresh()->default_currency_id)->toBe(currencyByCode('USD')->currency_id);
});

it('rejects a currency that is not in the supported list', function (mixed $value): void {
    $user = User::factory()->create();
    $tally = tallyFor($user);

    $this->actingAs($user)
        ->post(route('tallies.store'), ['name' => 'Bad', 'default_currency_uuid' => $value])
        ->assertSessionHasErrors('default_currency_uuid');
    $this->patch(route('tallies.update', $tally), ['name' => 'Bad', 'default_currency_uuid' => $value])
        ->assertSessionHasErrors('default_currency_uuid');

    expect(Tally::query()->where('name', 'Bad')->exists())->toBeFalse()
        ->and($tally->refresh()->default_currency_id)->toBeNull();
})->with([
    'unknown uuid' => fn () => (string) Str::uuid(),
    'not a uuid' => 'EUR',
]);

it('stores, updates and returns the default currency through the API', function (): void {
    $user = Sanctum::actingAs(User::factory()->create());

    $created = $this->postJson(route('api.tallies.store'), ['name' => 'Api', 'default_currency_uuid' => currencyByCode('USD')->uuid])
        ->assertSuccessful();
    $tally = Tally::query()->findOrFail($created->json('data.id'));
    expect($tally->default_currency_id)->toBe(currencyByCode('USD')->currency_id)
        ->and($tally->owner_id)->toBe($user->id);

    $this->putJson(route('api.tallies.update', $tally), ['name' => 'Api', 'default_currency_uuid' => currencyByCode('GBP')->uuid])
        ->assertSuccessful();
    expect($tally->refresh()->default_currency_id)->toBe(currencyByCode('GBP')->currency_id);

    $this->postJson(route('api.tallies.store'), ['name' => 'Api2', 'default_currency_uuid' => (string) Str::uuid()])
        ->assertUnprocessable();
});

// ─── props handed to the existing pages ────────────────────────────────────────

it('hands the supported currencies and the user default to the create tally page', function (): void {
    $user = User::factory()->create(['default_currency_id' => currencyByCode('GBP')->currency_id]);

    $this->actingAs($user)
        ->get(route('tallies.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('tallies/Create')
            ->where('default_currency_uuid', currencyByCode('GBP')->uuid)
            ->has('currencies', Currency::query()->count()));
});

it('hands the tally default currency to the edit tally page', function (): void {
    $user = User::factory()->create();
    $tally = tallyFor($user, ['default_currency_id' => currencyByCode('USD')->currency_id]);
    $plain = tallyFor($user);

    $this->actingAs($user)
        ->get(route('tallies.edit', $tally))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tally.default_currency_uuid', currencyByCode('USD')->uuid)
            ->has('currencies', Currency::query()->count()));

    $this->get(route('tallies.edit', $plain))
        ->assertInertia(fn (Assert $page) => $page->where('tally.default_currency_uuid', null));
});

it('falls back to null when the default currency is deleted', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create();
    $tally = tallyFor($user, ['default_currency_id' => $currency->currency_id]);

    $currency->delete();

    expect($tally->refresh()->default_currency_id)->toBeNull();
});
