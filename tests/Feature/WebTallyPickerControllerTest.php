<?php

declare(strict_types=1);

use App\Http\Controllers\TallyController;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\User;

covers(TallyController::class, TransactionController::class);

// ─── tallies/picker ────────────────────────────────────────────────────────────

it('picker returns only the caller accessible tallies as json', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $ownedTally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'My Tally']);
    $sharedTally = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Shared']);
    $sharedTally->users()->attach($user->id, ['default_share_percentage' => 50]);
    $unrelatedTally = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Other']);

    $response = $this->actingAs($user)->getJson(route('tallies.picker'));

    $response->assertOk();
    $data = $response->json();
    $uuids = array_column($data, 'uuid');

    expect($uuids)->toContain($ownedTally->uuid, $sharedTally->uuid);
    expect($uuids)->not->toContain($unrelatedTally->uuid);
});

it('picker returns expected json shape with color and glyph', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Shaped', 'color' => 'c3', 'glyph' => '✈']);

    $response = $this->actingAs($user)->getJson(route('tallies.picker'));

    $response->assertOk();
    $data = $response->json();
    expect($data)->toHaveCount(1);
    expect($data[0])->toHaveKeys(['tally_id', 'uuid', 'name', 'color', 'glyph']);
    expect($data[0]['color'])->toBe('c3');
    expect($data[0]['glyph'])->toBe('✈');
});

it('picker returns 401 for guests', function () {
    $this->getJson(route('tallies.picker'))->assertUnauthorized();
});

// ─── tallies/{tally}/transactions/form-data ────────────────────────────────────

it('form-data returns categories, currencies and parties for a member', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    Category::query()->create(['name' => 'Transport']);

    $response = $this->actingAs($user)->getJson(route('tallies.transactions.form-data', $tally));

    $response->assertOk();
    $response->assertJsonStructure([
        'categories' => [['category_id', 'name']],
        'currencies' => [['currency_id', 'name', 'code', 'symbol']],
        'parties' => [['id', 'name']],
    ]);
});

it('form-data currencies include symbol', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);

    $response = $this->actingAs($user)->getJson(route('tallies.transactions.form-data', $tally));

    $response->assertOk();
    $data = $response->json();
    expect($data['currencies'][0])->toHaveKey('symbol');
    expect($data['currencies'][0]['symbol'])->toBe('€');
});

it('form-data returns 403 for a non-member', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($stranger)
        ->getJson(route('tallies.transactions.form-data', $tally))
        ->assertForbidden();
});

it('form-data returns 401 for guests', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $this->getJson(route('tallies.transactions.form-data', $tally))->assertUnauthorized();
});
