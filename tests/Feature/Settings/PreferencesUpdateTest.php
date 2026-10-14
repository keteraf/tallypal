<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\User;
use App\Services\HomeCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

test('guests cannot update preferences', function () {
    $this->patch(route('preferences.update'), ['default_currency_uuid' => null])
        ->assertRedirect(route('login'));
});

test('the default currency can be set', function () {
    $user = User::factory()->create();
    $currency = Currency::factory()->create();

    $this->actingAs($user)
        ->from(route('you'))
        ->patch(route('preferences.update'), ['default_currency_uuid' => $currency->uuid])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('you'));

    expect($user->refresh()->default_currency_id)->toBe($currency->currency_id);
});

test('the default currency can be cleared', function () {
    $currency = Currency::factory()->create();
    $user = User::factory()->create(['default_currency_id' => $currency->currency_id]);

    $this->actingAs($user)
        ->patch(route('preferences.update'), ['default_currency_uuid' => null])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->default_currency_id)->toBeNull();
});

test('an unknown currency is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('preferences.update'), ['default_currency_uuid' => (string) Str::uuid()])
        ->assertSessionHasErrors('default_currency_uuid');

    expect($user->refresh()->default_currency_id)->toBeNull();
});

test('a non-integer currency is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('preferences.update'), ['default_currency_uuid' => 'abc'])
        ->assertSessionHasErrors('default_currency_uuid');
});

test('the shared auth user carries the default currency', function () {
    $currency = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€']);
    $user = User::factory()->create(['default_currency_id' => $currency->currency_id]);

    $this->actingAs($user)
        ->get(route('you'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.default_currency.uuid', $currency->uuid)
            ->missing('auth.user.default_currency.currency_id')
            ->where('auth.user.default_currency.code', 'EUR')
            ->where('auth.user.default_currency.symbol', '€'));
});

test('the shared default currency is null when unset', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('you'))
        ->assertInertia(fn ($page) => $page->where('auth.user.default_currency', null));
});

test('deleting the currency nulls the default', function () {
    $currency = Currency::factory()->create();
    $user = User::factory()->create(['default_currency_id' => $currency->currency_id]);

    $currency->delete();

    expect($user->refresh()->default_currency_id)->toBeNull();
});

test('transaction form data returns the user default currency id', function () {
    $currency = Currency::factory()->create();
    $user = User::factory()->create(['default_currency_id' => $currency->currency_id]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    $this->actingAs($user)
        ->getJson(route('tallies.transactions.form-data', $tally))
        ->assertOk()
        ->assertJsonPath('default_currency_uuid', $currency->uuid);
});

test('transaction form data falls back to the app default currency when nothing else is set', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    $this->actingAs($user)
        ->getJson(route('tallies.transactions.form-data', $tally))
        ->assertOk()
        ->assertJsonPath('default_currency_uuid', Currency::query()->where('code', 'EUR')->value('uuid'));
});

test('changing the default currency drops the cached home primary currency and balance', function () {
    $user = User::factory()->create();
    $currency = Currency::factory()->create();
    $primaryKey = HomeCacheService::key((int) $user->id, 'primary_currency');
    $balanceKey = HomeCacheService::key((int) $user->id, 'balance:' . now()->format('Y-m-d'));
    Cache::put($primaryKey, ['code' => 'EUR'], 60);
    Cache::put($balanceKey, ['net' => 1.0], 60);

    $this->actingAs($user)
        ->patch(route('preferences.update'), ['default_currency_uuid' => $currency->uuid])
        ->assertSessionHasNoErrors();

    expect(Cache::has($primaryKey))->toBeFalse()
        ->and(Cache::has($balanceKey))->toBeFalse();
});
